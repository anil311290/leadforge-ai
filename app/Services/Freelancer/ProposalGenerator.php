<?php

namespace App\Services\Freelancer;

use App\Models\FreelancerAccount;
use App\Services\Ai\AiClient;

/**
 * Builds a bid proposal (cover letter) for a project. Uses the configured AI
 * provider when available, otherwise falls back to a simple template so bids
 * can still be prepared for manual review.
 */
class ProposalGenerator
{
    public function __construct(protected AiClient $ai)
    {
    }

    public function generate(array $project, FreelancerAccount $account): string
    {
        $title = (string) ($project['title'] ?? 'your project');
        $description = (string) ($project['preview_description'] ?? $project['description'] ?? '');
        $useAi = $account->proposal_use_ai ?? FreelancerSettings::get('proposal_use_ai');

        if ($useAi && $this->ai->isConfigured()) {
            try {
                [$profileTitle, $profileSummary] = $this->profile($account);
                $signOff = $this->signOffName($account);
                $wantsPortfolio = $this->wantsPortfolioLink($description);
                $wantsAdminDemo = $this->wantsAdminDemoAccess($description);
                $links = $wantsPortfolio ? $this->relevantLinks($account, $title, $description) : [];

                $prompt = "Write a professional, easy-to-understand freelance bid proposal (130-200 words) for this project.\n"
                    ."Project title: {$title}\n"
                    ."Project description: {$description}\n"
                    ."My profile: {$profileTitle} — {$profileSummary}\n"
                    ."Formatting rules:\n"
                    ."- Tailor the content specifically to what THIS project's description actually asks for — mention the real technologies/features requested, don't write generic filler.\n"
                    ."- Use short paragraphs separated by a blank line (\\n\\n).\n"
                    ."- If listing multiple distinct skills or deliverables, use a bullet list with lines starting with \"- \"; otherwise write in plain paragraphs. Don't force bullets when they aren't needed.\n"
                    ."- The client's name is unknown — do not greet them by name or use any placeholder such as [Client's Name]; start with a plain greeting like \"Hi,\" instead.\n"
                    ."- Never use bracketed placeholders anywhere in the text.\n"
                    .($wantsPortfolio
                        ? "- The project description asks for a portfolio/past work/samples. Mention ONLY the following relevant past project(s), using their exact URLs, and briefly say why they're relevant: ".$this->formatLinksForPrompt($links, $wantsAdminDemo)."\n"
                        : "- Do not mention any portfolio link or website URL — the project description does not ask for one.\n")
                    ."- End with a real sign-off on its own line: \"Best regards,\" followed by \"{$signOff}\" (no placeholder).\n"
                    .'Respond as JSON: {"proposal": "..."}';

                $response = $this->ai->complete('You write professional, clear, well-structured freelance bid proposals.', $prompt);
                $decoded = json_decode($response, true);
                if (is_array($decoded) && ! empty($decoded['proposal'])) {
                    return $this->sanitize((string) $decoded['proposal'], $account, $wantsPortfolio, $wantsAdminDemo);
                }
            } catch (\Throwable $e) {
                // fall through to template below
            }
        }

        return $this->template($title, $account, $description);
    }

    /**
     * Removes any placeholder brackets the AI might still slip in, strips an
     * unwanted portfolio/demo-admin link, and makes sure the sign-off uses the
     * account's real name.
     */
    protected function sanitize(string $text, FreelancerAccount $account, bool $wantsPortfolio, bool $wantsAdminDemo): string
    {
        $signOff = $this->signOffName($account);

        // Drop a client-name greeting placeholder entirely (e.g. "Dear [Client's Name],").
        $text = preg_replace('/^(Dear|Hi|Hello)\s*\[[^\]]*\],?\s*/im', 'Hi,'."\n\n", $text, 1) ?? $text;

        // Replace any remaining "[Your Name]" / "[Name]" style placeholder with the real sign-off.
        $text = preg_replace('/\[(your name|name|client\'?s? name)\]/i', $signOff, $text) ?? $text;

        // Safety net: strip any portfolio/website link that wasn't explicitly offered.
        if (! $wantsPortfolio) {
            foreach ($this->allAccountUrls($account) as $url) {
                $text = preg_replace('/\s*'.preg_quote($url, '/').'\S*/i', '', $text) ?? $text;
            }
        }

        // Safety net: never leak demo admin URLs/credentials unless explicitly asked for.
        if (! $wantsAdminDemo) {
            foreach ($account->portfolio_projects ?? [] as $project) {
                if (! empty($project['demo_admin_url'])) {
                    $text = preg_replace('/\s*'.preg_quote($project['demo_admin_url'], '/').'\S*/i', '', $text) ?? $text;
                }
                if (! empty($project['demo_credentials'])) {
                    $text = str_ireplace($project['demo_credentials'], '', $text);
                }
            }
        }

        return trim($text);
    }

    protected function signOffName(FreelancerAccount $account): string
    {
        return $account->name;
    }

    protected function portfolioUrl(FreelancerAccount $account): string
    {
        return $account->portfolio_url ?: FreelancerSettings::get('portfolio_url');
    }

    /**
     * All URLs an account could ever reference (used to scrub unrequested links).
     */
    protected function allAccountUrls(FreelancerAccount $account): array
    {
        $projects = $account->portfolio_projects ?? [];
        $urls = array_merge(array_column($projects, 'url'), array_column($projects, 'demo_admin_url'));
        $urls[] = $this->portfolioUrl($account);

        return array_values(array_filter($urls));
    }

    /**
     * Finds the account's past-work project(s) that best match this specific
     * project's title/description (by tag/description keyword overlap).
     * Falls back to the account's generic portfolio URL when nothing matches.
     */
    protected function relevantLinks(FreelancerAccount $account, string $title, string $description): array
    {
        $haystack = mb_strtolower($title.' '.$description);
        $scored = [];

        foreach ($account->portfolio_projects ?? [] as $project) {
            $keywords = array_merge($project['tags'] ?? [], preg_split('/\s+/', mb_strtolower($project['title'] ?? '')));
            $score = 0;
            foreach (array_unique(array_filter($keywords)) as $keyword) {
                $keyword = trim((string) $keyword);
                if ($keyword !== '' && mb_strlen($keyword) > 2 && str_contains($haystack, mb_strtolower($keyword))) {
                    $score++;
                }
            }
            if ($score > 0) {
                $scored[] = [
                    'score' => $score,
                    'title' => $project['title'] ?? '',
                    'url' => $project['url'] ?? '',
                    'demo_admin_url' => $project['demo_admin_url'] ?? '',
                    'demo_credentials' => $project['demo_credentials'] ?? '',
                ];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);
        $top = array_slice($scored, 0, 2);

        if (empty($top)) {
            return [['title' => 'Similar work', 'url' => $this->portfolioUrl($account), 'demo_admin_url' => '', 'demo_credentials' => '']];
        }

        return array_map(fn ($m) => [
            'title' => $m['title'],
            'url' => $m['url'],
            'demo_admin_url' => $m['demo_admin_url'],
            'demo_credentials' => $m['demo_credentials'],
        ], $top);
    }

    protected function formatLinksForPrompt(array $links, bool $includeCredentials = false): string
    {
        return collect($links)->map(function ($l) use ($includeCredentials) {
            $entry = "{$l['title']} ({$l['url']})";
            if ($includeCredentials && ! empty($l['demo_admin_url'])) {
                $entry .= " — demo admin: {$l['demo_admin_url']}";
                if (! empty($l['demo_credentials'])) {
                    $entry .= " (login: {$l['demo_credentials']})";
                }
            }

            return $entry;
        })->implode('; ');
    }

    /**
     * Only mention the portfolio when the project explicitly asks for one.
     */
    protected function wantsPortfolioLink(string $description): bool
    {
        return (bool) preg_match('/\b(portfolio|past work|previous work|sample work|work samples|showcase|github|examples of your work|link to your work)\b/i', $description);
    }

    /**
     * Demo admin credentials are only ever offered when the client explicitly
     * asks to test/access an admin panel or demo login — never automatically.
     */
    protected function wantsAdminDemoAccess(string $description): bool
    {
        return (bool) preg_match('/\b(admin (panel |access )?demo|demo (login|credentials|access)|test (login|credentials|the admin)|access to (the )?admin)\b/i', $description);
    }

    /**
     * Per-account profile overrides the global default when set.
     */
    protected function profile(FreelancerAccount $account): array
    {
        return [
            $account->profile_title ?: FreelancerSettings::get('profile_title'),
            $account->profile_summary ?: FreelancerSettings::get('profile_summary'),
        ];
    }

    protected function template(string $title, FreelancerAccount $account, string $description = ''): string
    {
        [$profileTitle, $profileSummary] = $this->profile($account);
        $portfolioLine = '';

        if ($this->wantsPortfolioLink($description)) {
            $links = $this->relevantLinks($account, $title, $description);
            $portfolioLine = "\n\nRelevant past work: ".$this->formatLinksForPrompt($links, $this->wantsAdminDemoAccess($description));
        }

        return "Hi, I reviewed your project \"{$title}\" and I'm confident I can deliver it well.\n\n"
            ."I'm a {$profileTitle}. {$profileSummary}{$portfolioLine}\n\n"
            ."I'd like to discuss your requirements in detail and start promptly. Looking forward to your response.\n\n"
            ."Best regards,\n{$this->signOffName($account)}";
    }
}
