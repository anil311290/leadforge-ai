@php($account = $account ?? null)
<div class="row g-3">
    <div class="col-md-6"><label class="form-label fw-semibold">Account label <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required value="{{ old('name', $account->name ?? '') }}" placeholder="e.g. Main Account"></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Freelancer username</label><input type="text" name="freelancer_username" class="form-control" value="{{ old('freelancer_username', $account->freelancer_username ?? '') }}"></div>

    <div class="col-md-8">
        <label class="form-label fw-semibold">OAuth token (API key) {{ $account ? '' : '*' }}</label>
        <input type="password" name="oauth_token" class="form-control" autocomplete="new-password" {{ $account ? '' : 'required' }} placeholder="{{ $account ? 'Leave blank to keep current key' : 'Paste the account OAuth token' }}">
        <div class="form-text">Stored encrypted. Generate this from your Freelancer.com Developer app settings.</div>
    </div>
    <div class="col-md-4"><label class="form-label fw-semibold">API base URL</label><input type="url" name="api_url" class="form-control" value="{{ old('api_url', $account->api_url ?? 'https://www.freelancer.com') }}"></div>

    <div class="col-md-4"><label class="form-label fw-semibold">Max bids / day</label><input type="number" min="1" max="200" name="max_bids_per_day" class="form-control" value="{{ old('max_bids_per_day', $account->max_bids_per_day ?? 10) }}"></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Min budget</label><input type="number" min="0" name="budget_min" class="form-control" value="{{ old('budget_min', $account->budget_min ?? 0) }}"></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Budget currency</label><input type="text" name="budget_min_currency" class="form-control" value="{{ old('budget_min_currency', $account->budget_min_currency ?? 'USD') }}"></div>

    <div class="col-md-6"><label class="form-label fw-semibold">Fallback bid amount</label><input type="number" min="0" step="0.01" name="bid_amount_default" class="form-control" value="{{ old('bid_amount_default', $account->bid_amount_default ?? 0) }}"><div class="form-text">Used only when Freelancer does not return a usable budget. Normal bids use 80% of the budget ceiling and round up to a multiple of 5.</div></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Fallback bid period (days)</label><input type="number" min="1" max="90" name="bid_period_days_default" class="form-control" value="{{ old('bid_period_days_default', $account->bid_period_days_default ?? 5) }}"><div class="form-text">Normal timelines are estimated from project value and complexity in 5-day increments.</div></div>

    <div class="col-12"><label class="form-label fw-semibold">Include keywords (comma separated)</label><input type="text" name="include_keywords" class="form-control" value="{{ old('include_keywords', $account && $account->include_keywords ? implode(', ', $account->include_keywords) : '') }}" placeholder="php, laravel, node.js, react"></div>
    <div class="col-12"><label class="form-label fw-semibold">Exclude keywords (comma separated)</label><input type="text" name="exclude_keywords" class="form-control" value="{{ old('exclude_keywords', $account && $account->exclude_keywords ? implode(', ', $account->exclude_keywords) : '') }}" placeholder="wordpress, wix"></div>
    <div class="col-12"><label class="form-label fw-semibold">Exclude client countries (ISO codes, comma separated)</label><input type="text" name="exclude_countries" class="form-control" value="{{ old('exclude_countries', $account && $account->exclude_countries ? implode(', ', $account->exclude_countries) : '') }}" placeholder="IN"></div>

    <div class="col-12"><hr><div class="fw-semibold small text-muted text-uppercase mb-1">Proposal profile (overrides the global default in Freelancer Settings)</div></div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Generate with AI</label>
        <select name="proposal_use_ai" class="form-select">
            <option value="" @selected(old('proposal_use_ai', $account->proposal_use_ai ?? null) === null)>Use global default</option>
            <option value="1" @selected(old('proposal_use_ai', $account->proposal_use_ai ?? null) === true || old('proposal_use_ai') === '1')>Yes</option>
            <option value="0" @selected(old('proposal_use_ai', $account->proposal_use_ai ?? null) === false || old('proposal_use_ai') === '0')>No, template only</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Experience (Years)</label>
        <input type="number" min="0" max="50" name="experience_years" class="form-control" value="{{ old('experience_years', $account->experience_years ?? 5) }}" placeholder="e.g. 5">
        <div class="form-text">Years of experience to highlight in proposals.</div>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Proposal style / tone</label>
        <select name="proposal_style" class="form-select">
            <option value="direct" @selected(old('proposal_style', $account->proposal_style ?? 'direct') === 'direct')>Direct & Results-focused</option>
            <option value="technical" @selected(old('proposal_style', $account->proposal_style ?? '') === 'technical')>Technical & Architecture-first</option>
            <option value="consultative" @selected(old('proposal_style', $account->proposal_style ?? '') === 'consultative')>Consultative & Solution-oriented</option>
            <option value="conversational" @selected(old('proposal_style', $account->proposal_style ?? '') === 'conversational')>Conversational & Friendly</option>
            <option value="agile" @selected(old('proposal_style', $account->proposal_style ?? '') === 'agile')>Agile & Fast Delivery</option>
        </select>
        <div class="form-text">Ensures proposals for different accounts are never identical.</div>
    </div>
    <div class="col-md-12"><label class="form-label fw-semibold">Profile title</label><input type="text" name="profile_title" class="form-control" value="{{ old('profile_title', $account->profile_title ?? '') }}" placeholder="Leave blank to use the global default"></div>
    <div class="col-12"><label class="form-label fw-semibold">Profile summary</label><textarea name="profile_summary" class="form-control" rows="3" placeholder="Leave blank to use the global default">{{ old('profile_summary', $account->profile_summary ?? '') }}</textarea></div>
    <div class="col-12"><label class="form-label fw-semibold">Portfolio URL</label><input type="url" name="portfolio_url" class="form-control" value="{{ old('portfolio_url', $account->portfolio_url ?? '') }}" placeholder="Leave blank to use the global default"><div class="form-text">Generic fallback link, used only when no specific past project below matches the client's request.</div></div>
    <div class="col-12">
        <label class="form-label fw-semibold">Past work projects (one per line)</label>
        <textarea name="portfolio_projects" class="form-control" rows="6" placeholder="Title | https://example.com | tag1,tag2,tag3 | One-line description | https://example.com/admin | demo@mail.com / demo123">{{ old('portfolio_projects', \App\Models\FreelancerAccount::formatPortfolioProjects($account->portfolio_projects ?? null)) }}</textarea>
        <div class="form-text">Format: <code>Title | Public URL | tags,comma,separated | Description | Demo admin URL (optional) | Demo credentials (optional)</code>. The proposal links the project(s) whose tags/description best match the client's requirement. The demo admin URL/credentials (last 2 columns, optional) are shared <strong>only when the client explicitly asks to test/access the admin panel</strong> — use non-production demo logins only, never real client data.</div>
    </div>

    <div class="col-md-6">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $account ? $account->is_active : true) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="is_active">Active (included in scheduled scans)</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="auto_submit_bids" id="auto_submit_bids" value="1" {{ old('auto_submit_bids', $account->auto_submit_bids ?? false) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="auto_submit_bids">Auto-submit bids (off = draft for review)</label>
        </div>
    </div>
</div>
