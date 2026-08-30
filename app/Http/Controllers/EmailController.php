<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateEmail;
use App\Jobs\SendEmail;
use App\Models\EmailMessage;
use App\Models\Lead;
use App\Services\AuditService;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index()
    {
        $messages = EmailMessage::with('lead')
            ->orderByRaw("case when status='pending_approval' then 0 when status='draft' then 1 else 2 end")
            ->orderByDesc('updated_at')
            ->paginate(25);

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
            ->paginate(20);

        return view('emails.pending', compact('messages'));
    }

    public function generate(Request $request, Lead $lead)
    {
        dispatch(new GenerateEmail($lead))->onQueue('emails');

        return back()->with('success', 'Email draft generation queued for '.$lead->company.'.');
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