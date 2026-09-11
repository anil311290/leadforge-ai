<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateEmail;
use App\Jobs\SendEmail;
use App\Models\EmailMessage;
use App\Models\Lead;
use App\Services\AuditService;
use App\Services\Ai\AiClient;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index()
    {
        $messages = EmailMessage::with('lead')
            ->orderByRaw("case when status='pending_approval' then 0 when status='draft' then 1 else 2 end")
            ->orderByDesc('updated_at')
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'approved_pending' => EmailMessage::where('status', 'approved')->count(),
            'pending_approval' => EmailMessage::where('status', 'pending_approval')->count(),
            'sent' => EmailMessage::where('status', 'sent')->count(),
            'replied' => EmailMessage::where('direction', 'inbound')->count(),
        ];

        return view('emails.index', compact('messages', 'summary'));
    }

    public function pending()
    {
        $messages = EmailMessage::whereIn('status', ['pending_approval', 'approved'])
            ->with('lead')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('emails.pending', compact('messages'));
    }

    public function generate(Request $request, Lead $lead)
    {
        if (! $lead->email) {
            return back()->with('error', 'Cannot generate email — no email address known for '.$lead->company.'.');
        }

        dispatch(new GenerateEmail($lead))->onQueue('emails');

        return back()->with('success', 'Email draft generation queued for '.$lead->company.'.');
    }

    public function generateQuotation(Request $request, Lead $lead)
    {
        $this->authorize('update', $lead);

        $data = $this->validate($request, [
            'quotation_prompt' => ['nullable', 'string', 'max:2000'],
            'generation_mode' => ['nullable', 'in:template,ai'],
        ]);

        $draft = ($data['generation_mode'] ?? 'template') === 'ai'
            ? $this->generateAiQuotation($lead, $data['quotation_prompt'] ?? null)
            : [
                'subject' => 'Quotation for '.$lead->company,
                'body' => $lead->quotationMessage($data['quotation_prompt'] ?? null),
            ];

        $quotation = EmailMessage::create([
            'lead_id' => $lead->id,
            'campaign_id' => $lead->campaign_id,
            'direction' => 'outbound',
            'subject' => $draft['subject'],
            'body' => $draft['body'],
            'to_email' => $lead->email,
            'from_email' => config('mail.from.address', config('leadforge.email.from_email')),
            'status' => $lead->email ? 'pending_approval' : 'draft',
        ]);

        $lead->update([
            'status' => Lead::STATUS_PROPOSAL,
            'next_action' => $lead->email ? 'Review and send quotation' : 'Send quotation on WhatsApp or call',
        ]);

        AuditService::record(auth()->user(), 'quotation_generated', 'EmailMessage', $quotation->id, null, ['lead_id' => $lead->id]);

        return back()->with('success', 'Quotation draft generated for '.$lead->company.'.');
    }

    protected function generateAiQuotation(Lead $lead, ?string $customPrompt): array
    {
        $ai = app(AiClient::class);

        if (! $ai->isConfigured()) {
            return [
                'subject' => 'Quotation for '.$lead->company,
                'body' => $lead->quotationMessage($customPrompt)."\n\nNote: AI quotation generation is unavailable because AI API is not configured.",
            ];
        }

        $company = $lead->company;
        $industry = $lead->industry ?? '';
        $location = trim(($lead->city ?? '').' '.($lead->location ?? ''));
        $recommendedService = $lead->recommended_service ?? '';
        $estimatedMin = $lead->estimated_min ?? '';
        $estimatedMax = $lead->estimated_max ?? '';
        $analysisSummary = $lead->analysis['summary'] ?? '';
        $customRequirement = $customPrompt ?? '';

        $prompt = <<<PROMPT
Create a professional quotation message for this lead.

    Company: {$company}
    Industry: {$industry}
    Location: {$location}
    Recommended service: {$recommendedService}
    Estimated minimum budget: {$estimatedMin}
    Estimated maximum budget: {$estimatedMax}
    Lead analysis summary: {$analysisSummary}
    Custom requirement from user: {$customRequirement}

Rules:
- Write from APARK IT SOLUTIONS.
- Keep it clear, practical, and client-ready.
- Include scope of work, deliverables, estimated budget in INR, timeline, assumptions, and next step.
- Do not overpromise fixed pricing; say final quote depends on exact requirements.
- Use plain text only. No markdown tables.
- End with:
Best regards,
APARK IT SOLUTIONS

Return JSON with keys: subject, body
PROMPT;

        try {
            $raw = $ai->complete(
                'You are a senior software sales consultant creating practical client quotations.',
                $prompt,
                ['max_tokens' => 1200, 'temperature' => 0.35]
            );

            $parsed = json_decode($raw, true) ?: [];
            $body = trim((string) ($parsed['body'] ?? ''));

            if ($body === '') {
                throw new \RuntimeException('AI returned an empty quotation.');
            }

            return [
                'subject' => trim((string) ($parsed['subject'] ?? 'Quotation for '.$lead->company)),
                'body' => $body,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'subject' => 'Quotation for '.$lead->company,
                'body' => $lead->quotationMessage($customPrompt)."\n\nNote: AI quotation generation failed, so a template quotation was created instead.",
            ];
        }
    }

    public function updateDraft(Request $request, EmailMessage $email)
    {
        $this->authorize('update', $email->lead);

        if ($email->status === 'sent') {
            return back()->with('error', 'Sent messages cannot be edited.');
        }

        $data = $this->validate($request, [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $email->update($data);
        AuditService::record(auth()->user(), 'email_draft_updated', 'EmailMessage', $email->id);

        return back()->with('success', 'Draft updated.');
    }

    public function destroyDraft(EmailMessage $email)
    {
        $this->authorize('update', $email->lead);

        if ($email->status === 'sent') {
            return back()->with('error', 'Sent messages cannot be deleted.');
        }

        AuditService::record(auth()->user(), 'email_draft_deleted', 'EmailMessage', $email->id, null, [
            'subject' => $email->subject,
            'lead_id' => $email->lead_id,
        ]);

        $email->delete();

        return back()->with('success', 'Draft deleted.');
    }

    public function approve(Request $request, EmailMessage $email)
    {
        $email->update(['status' => 'approved']);
        AuditService::record(auth()->user(), 'email_approved', 'EmailMessage', $email->id);

        return back()->with('success', 'Email approved. It will be sent in the next send window.');
    }

    public function send(Request $request, EmailMessage $email)
    {
        if (config('queue.default') !== 'sync') {
            dispatch(new SendEmail($email))->onQueue('emails');
        } else {
            app(\App\Services\Email\MailService::class)->send($email);
        }

        return back()->with('success', 'Email send queued.');
    }
}