# Website Builder Architecture Plan

## 1) Actual app findings
- Laravel: 12.x (`laravel/framework: ^12.0`)
- PHP: ^8.2
- Auth: standard Laravel `Auth` + `auth` middleware, login route in `routes/web.php`
- Users table: default Laravel `users` with `role`, `is_active`, `last_login_at` added by migration `2024_01_01_000001_add_user_roles_to_users_table.php`
- Role model: `User::role` is a string; current role checks are `admin` only via `EnsureUserHasRole` middleware
- Permissions: no full policy package; custom middleware alias `role` registered in `bootstrap/app.php`
- Lead model: `App\Models\Lead` uses existing `leads` table and stores business data like `company`, `website`, `city`, `state`, `country`, `address`, `phone`, `email`, `industry`, `status`, etc.
- Frontend: Blade + Bootstrap 5 assets in `public/assets/vendor/...`, plus Vite for JS asset pipeline (`resources/js/app.js`)
- AI: `App\Services\Ai\AiClient` is the reusable OpenAI-compatible provider layer; config lives in `config/leadforge.php`
- Storage: Laravel filesystem default is `local` + `public` disk; file uploads should use `storage/app/public` and `Storage::disk('public')`
- Settings: `App\Models\Setting` + `Setting::get()/set()` stores app config in DB; good fit for Website Builder base URL and defaults
- Route structure: all app routes are in `routes/web.php`; no existing `/website-builder` module

## 2) Route conflict risk
- Do not add a catch-all route before `/login`, `/dashboard`, `/leads`, `/campaigns`, `/admin`, `/api` etc.
- A public website route like `/{slug}` is only safe if placed at the very end and after all known app routes.
- `website-builder` path itself is low-risk if grouped under `auth` and protected by a strict role check.

## 3) Recommended DB changes
Keep the current app structure and add only the missing tables:
- `website_templates` (template metadata)
- `business_websites` (main website record)
- `website_services`
- `website_products`
- `website_media`
- `website_sections`
- `website_seo`
- `website_contact_submissions`
- `website_generation_logs` (optional)

Use existing `leads` table as the source for lead data; add `business_websites.lead_id` nullable and foreign key to `leads.id`.

## 4) Recommended role approach
Use existing `users.role` instead of new auth tables.
- Existing values: `user`, `admin`
- New value: `website_builder`
- Add a custom middleware like `website_builder` or extend the existing `role` middleware to support multiple roles: `admin|website_builder`
- Admin stays normal and can access both modules.

## 5) Recommended folder structure
- `app/Http/Controllers/WebsiteBuilder/`
- `app/Http/Requests/WebsiteBuilder/`
- `app/Policies/WebsiteBuilderPolicy.php`
- `app/Services/WebsiteBuilder/`
- `app/Services/WebsiteBuilder/Seo/`
- `app/Services/WebsiteBuilder/Content/`
- `app/Models/BusinessWebsite.php`
- `app/Models/WebsiteTemplate.php`
- `resources/views/website-builder/`
- `resources/views/website-builder/templates/`
- `resources/views/website-builder/partials/`

## 6) Recommended architecture
- Route group: `/website-builder` under `auth`
- Middleware: `website_builder` or `role:website_builder` plus admin passthrough
- Use a single `BusinessWebsite` model with status values: `draft`, `published`, `unpublished`, `archived`
- Template rendering via Blade components and reusable sections, not duplicated full pages per website
- Public renderer: slug lookup -> business website -> template -> section data -> SEO metadata -> render
- SEO layer: dynamic title/meta/canonical/OG/Twitter/JSON-LD/robots; support sitemap for published entries
- Media: Laravel `Storage` with file validation and blocked executable mime types

## 7) Lead -> Website Builder flow
1. Lead discovered / analyzed
2. If no website or poor score, show `Create Website`
3. Pre-fill business fields from `Lead`
4. Create `BusinessWebsite` with `lead_id`
5. Choose template
6. Generate AI content + branding
7. Preview on desktop/tablet/mobile
8. Publish -> unique slug + public URL
9. Show website status, URL, preview, edit, and copy-link actions on the Lead screen

## 8) Phase 1 implementation plan
Phase 1 should be limited to:
- Add `website_builder` role support
- Add `/website-builder/login` and redirect logic after login
- Add dashboard + CRUD screens for websites
- Add business info form and slug generation
- Add public rendering for one modern template
- Add publish/unpublish flow

## 9) Phase 2 onward
- Phase 2: multiple templates, sections, services, products, media, branding
- Phase 3: AI content, SEO generation, recommendation engine
- Phase 4: AI logo generation, lead integration, website status on lead detail, share demo links
- Phase 5: sitemap, schema, performance, analytics-ready structure

## 10) Recommended implementation posture
- No large refactor
- No new auth system
- No duplicate lead/business tables
- No catch-all public route before protected app routes
- Reuse existing Laravel config and storage patterns

## Approval gate
This is the base architecture plan derived from the current codebase. Proceed with Phase 1 only after approval.
