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
        $rawTitle = (string) ($project['title'] ?? 'your project');
        $rawDescription = (string) ($project['preview_description'] ?? $project['description'] ?? '');

        $title = trim(html_entity_decode($rawTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $description = trim(html_entity_decode($rawDescription, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        $useAi = $account->proposal_use_ai ?? FreelancerSettings::get('proposal_use_ai');

        if ($useAi && $this->ai->isConfigured()) {
            try {
                [$profileTitle, $profileSummary] = $this->profile($account);
                $signOff = $this->signOffName($account);
                $experienceYears = (int) ($account->experience_years ?: 5);
                $style = $account->proposal_style ?: 'direct';

                $wantsPortfolio = $this->wantsPortfolioLink($description);
                $wantsAdminDemo = $this->wantsAdminDemoAccess($description);
                $links = $wantsPortfolio ? $this->relevantLinks($account, $title, $description) : [];

                $styleGuidelines = match ($style) {
                    'technical' => 'Adopt an architecture-first, highly technical tone. Focus on clean code, database design, API design, and system scalability.',
                    'consultative' => 'Adopt a consultative, business-value tone. Focus on project goals, ROI, user experience, and long-term maintainability.',
                    'conversational' => 'Adopt a warm, conversational, approachable tone. Emphasize open communication, active listening, and collaboration.',
                    'agile' => 'Adopt an agile, high-momentum tone. Focus on rapid delivery, iterative milestones, and immediate execution.',
                    default => 'Adopt a direct, results-focused tone. Highlight immediate fit, key technical deliverables, and value.',
                };

                $prompt = "Write a professional, easy-to-understand freelance bid proposal (130-200 words) for this project.\n\n"
                    ."Project Title: {$title}\n"
                    ."Project Description: {$description}\n\n"
                    ."Freelancer Profile Reference (FOR BACKGROUND CONTEXT ONLY):\n"
                    ."- Developer Name: {$signOff}\n"
                    ."- Experience Level: {$experienceYears}+ years of hands-on professional development\n"
                    ."- Title: {$profileTitle}\n"
                    ."- Background Context: {$profileSummary}\n"
                    ."- Preferred Proposal Writing Style: {$styleGuidelines}\n\n"
                    ."CRITICAL PROPOSAL REQUIREMENTS:\n"
                    ."- DO NOT COPY OR PASTE THE BACKGROUND CONTEXT VERBATIM INTO THE PROPOSAL. Use it ONLY to inform your understanding of the developer's background.\n"
                    ."- The proposal MUST be written 100% specifically about the client's project description and requirements.\n"
                    ."- Explain how you will solve their specific requirements, build requested features, and deliver the project.\n"
                    ."- Every proposal must be 100% UNIQUE in phrasing, sentence structure, opening hook, and technical framing for account '{$signOff}'.\n"
                    ."- Explicitly reflect {$signOff}'s {$experienceYears}+ years of experience and specialized perspective.\n"
                    ."- Use short paragraphs separated by a blank line (\\n\\n).\n"
                    ."- If listing distinct deliverables, use a bullet list with lines starting with \"- \".\n"
                    ."- Do not use placeholders like [Client's Name] or [Your Name]. Start with a clean greeting like \"Hi,\".\n"
                    .($wantsPortfolio
                        ? "- Mention ONLY these relevant past project link(s) for the requested portfolio: ".$this->formatLinksForPrompt($links, $wantsAdminDemo)."\n"
                        : "- Do not mention any portfolio URL as it was not explicitly requested.\n")
                    ."- End with a sign-off on its own line: \"Best regards,\" followed by \"{$signOff}\".\n"
                    .'Respond in valid JSON format: {"proposal": "..."}';

                $systemPrompt = "You write distinct, high-converting, professional freelance bid proposals tailored for developer '{$signOff}' ({$experienceYears}+ years experience).";

                $response = $this->ai->complete($systemPrompt, $prompt);
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

        return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
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
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        [$profileTitle, ] = $this->profile($account);
        $exp = (int) ($account->experience_years ?: 5);
        $style = $account->proposal_style ?: 'direct';
        $portfolioLine = '';

        if ($this->wantsPortfolioLink($description)) {
            $links = $this->relevantLinks($account, $title, $description);
            $portfolioLine = "\n\nRelevant past work: ".$this->formatLinksForPrompt($links, $this->wantsAdminDemoAccess($description));
        }

        $intros = [
            'direct' => "Hi, I'm {$this->signOffName($account)}. With {$exp}+ years of professional experience as a {$profileTitle}, I reviewed \"{$title}\" and am ready to deliver exact results.",
            'technical' => "Hello, I read through your requirements for \"{$title}\". As a {$profileTitle} with {$exp}+ years of software architecture experience, I will build a clean, reliable, and well-structured solution.",
            'consultative' => "Hi there, I analyzed \"{$title}\" and see a great fit. Bringing {$exp}+ years of experience as a {$profileTitle}, I focus on scalable software that drives real business value.",
            'conversational' => "Hi! I saw your post for \"{$title}\" and would love to help you build this. I have {$exp}+ years of hands-on experience as a {$profileTitle}.",
            'agile' => "Greetings! I specialize in rapid, high-quality execution for projects like \"{$title}\". I bring {$exp}+ years of specialized {$profileTitle} expertise.",
        ];

        $intro = $intros[$style] ?? $intros['direct'];
        $solution = $this->buildTailoredSolution($title, $description, $profileTitle, $exp);

        $text = "{$intro}\n\n"
            ."{$solution}{$portfolioLine}\n\n"
            ."I'd like to discuss your project requirements in detail and get started promptly. Looking forward to connecting.\n\n"
            ."Best regards,\n{$this->signOffName($account)}";

        return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    protected function buildTailoredSolution(string $title, string $description, string $profileTitle, int $exp): string
    {
        $combined = strtolower($title.' '.$description);
        $highlights = [];

        if (str_contains($combined, 'api') || str_contains($combined, 'rest') || str_contains($combined, 'integration') || str_contains($combined, 'webhook')) {
            $highlights[] = 'clean, secure API integrations and robust backend logic';
        }
        if (str_contains($combined, 'database') || str_contains($combined, 'mysql') || str_contains($combined, 'sql') || str_contains($combined, 'data')) {
            $highlights[] = 'optimized database schema and query performance';
        }
        if (str_contains($combined, 'app') || str_contains($combined, 'mobile') || str_contains($combined, 'flutter') || str_contains($combined, 'react native')) {
            $highlights[] = 'responsive, high-performance mobile application workflows';
        }
        if (str_contains($combined, 'shop') || str_contains($combined, 'ecommerce') || str_contains($combined, 'e-commerce') || str_contains($combined, 'store') || str_contains($combined, 'payment')) {
            $highlights[] = 'seamless payment processing and secure transaction management';
        }
        if (str_contains($combined, 'crm') || str_contains($combined, 'erp') || str_contains($combined, 'dashboard') || str_contains($combined, 'admin')) {
            $highlights[] = 'intuitive administrative management tools and business reporting dashboards';
        }
        if (str_contains($combined, 'bug') || str_contains($combined, 'fix') || str_contains($combined, 'issue') || str_contains($combined, 'refactor')) {
            $highlights[] = 'thorough code auditing, quick bug resolution, and system stabilization';
        }

        if (! empty($highlights)) {
            $focus = implode(', ', $highlights);

            return "For your requirements, my technical focus will be on {$focus}. I ensure all deliverables are well-tested, documented, and easy to maintain.";
        }

        return "My technical approach focuses on clean architecture, efficient execution, and delivering production-ready software aligned with your specific project scope.";
    }
}
