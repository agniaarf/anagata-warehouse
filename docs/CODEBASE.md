# Codebase overview

Laravel 9 app serving one thing: the Anagata Warehouse landing page, plus a
database-backed CMS/admin panel (`/bojong`) so non-developers can edit every piece
of that page's copy without touching code, plus a small first-party visitor
analytics system feeding a dashboard in that same admin panel.

See also: [`docs/DATABASE.md`](DATABASE.md) for the full schema, and
[`app/Http/Controllers/Admin/AGENTS.md`](../app/Http/Controllers/Admin/AGENTS.md)
for how the CMS's schema-driven editing actually works if you're changing
anything under `/bojong`. This file is the wider map.

## Three route groups (`routes/`)

- **`web.php`** — `GET /` (the public landing page, `PageController::home`) and
  `POST /analytics/collect` (public, unauthenticated, throttled 60/min — where the
  visitor-tracking beacon in `public/js/app.js` posts to). Also wraps everything
  under `/bojong` in a 20 req/min throttle and pulls in the next two files.
- **`auth.php`** — login, forgot-password, reset-password. Two hand-seeded admin
  accounts, no public registration (see `docs/DATABASE.md`'s `users` table).
- **`admin.php`** — everything under `/bojong` behind the `auth` middleware:
  homepage section editor, repeatable-content editor, settings tabs, image
  uploads, analytics dashboard. All routed through `Admin\*Controller` classes —
  see the CMS `AGENTS.md` linked above for how these work.

## Request flow: public landing page

`PageController::home()` → `ContentRepository::section()` (cached) for each of 13
sections → `view('landing', ['cms' => $sections])` → `resources/views/landing.blade.php`
`@include`s one partial per visible section from `resources/views/partials/`, each
reading its own content via the `cms_field()`/`cms_items()` helpers
(`app/Support/helpers.php`). Which sections exist, their order, and their
Blade partial path are defined in `config/page_sections.php`; their editable
fields are defined in `config/cms_schema.php`. No controller logic decides what
content to show beyond "loop over configured sections in order" — it's entirely
config + database driven.

## Request flow: admin CMS

Covered in depth in `app/Http/Controllers/Admin/AGENTS.md`. Short version:
`config/cms_schema.php` declares every field; `HandlesCmsFields` trait
(`app/Http/Controllers/Admin/Concerns/`) turns that declaration into validation
rules and encode/decode logic generically, so none of the four admin controllers
(`WebsiteController`, `ContentController`, `SettingsController`, `UploadController`)
hand-write per-field validation.

## Request flow: analytics

Public beacon (`public/js/app.js`, IIFE using `sendBeacon`/`fetch` with
`keepalive`) → `POST /analytics/collect` → `AnalyticsCollectionController` (JSON
validation, allowlisted event types) → `AnalyticsCollector` service (session
lookup/creation, 30-min idle timeout, bot/admin exclusion) → `analytics_sessions`/
`analytics_events` tables. Read side: `Admin\AnalyticsController` →
`/bojong/analytics` dashboard (period comparison, daily visitor chart, session
list with their events). Purged after 90 days by `analytics:prune`
(`app/Console/Commands/PruneAnalytics.php`), scheduled in `app/Console/Kernel.php`.
Full column reference in `docs/DATABASE.md`.

## Directory map

```
app/
  Console/
    Commands/PruneAnalytics.php    <- analytics:prune, scheduled daily 02:30 Asia/Jakarta
    Kernel.php                      <- schedule() definition
  Http/
    Controllers/
      PageController.php            <- public landing page (single action: home())
      AnalyticsCollectionController.php  <- public POST /analytics/collect
      Admin/                        <- see its own AGENTS.md
        AGENTS.md
        WebsiteController.php       <- homepage section order/visibility/nav + section edit forms
        ContentController.php       <- generic repeater item CRUD (any section's repeaters)
        SettingsController.php      <- branding/contact/seo tabs (subset of site_settings + seo fields)
        UploadController.php        <- POST /bojong/uploads, image upload endpoint
        AnalyticsController.php     <- GET /bojong/analytics
        Concerns/HandlesCmsFields.php  <- schema-driven validation + encode/decode, shared by the 3 controllers above
      Auth/                         <- login, forgot/reset password (Laravel Breeze scaffold, trimmed — see below)
    Middleware/
      SecurityHeaders.php           <- CSP/X-Frame-Options/etc on every response, no-store on /bojong/*
      TrustHosts.php                <- rejects unrecognized Host headers
      (rest are stock Laravel: EncryptCookies, TrimStrings, TrustProxies, VerifyCsrfToken, ...)
    Requests/Auth/LoginRequest.php  <- rate-limited login (5 attempts per email+IP)
  Models/
    User.php, Content.php, AnalyticsSession.php, AnalyticsEvent.php
  Observers/ContentObserver.php     <- busts the per-section content cache on save/delete
  Repositories/ContentRepository.php <- the one read path for cms_contents (cached)
  Services/
    AnalyticsCollector.php          <- validates + persists analytics events
    ImageUploadService.php          <- re-encodes uploads to WebP via GD, dimension caps
  Support/
    Cms.php                         <- Cms::imageUrl() — resolves a stored image reference to a URL
    FieldCodec.php                  <- list/delimited_list ⇄ array encoding for the 'list'/'delimited_list' field types
    helpers.php                     <- cms_field(), cms_items(), cms_rich_text(), content_collections(), cms_display_value()

config/
  cms_schema.php      <- every CMS field's type/label/validation-relevant metadata
  page_sections.php   <- homepage section registry (view path, default order, nav visibility)
  analytics.php       <- retention/timeout/allowlisted sections+channels

database/
  migrations/          <- see docs/DATABASE.md
  seeders/
    DatabaseSeeder.php    <- runs CmsContentSeeder only (the automatic php artisan db:seed chain)
    CmsContentSeeder.php  <- seeds all placeholder/starter CMS content — DANGEROUS to re-run after go-live, see docs/DATABASE.md
    AdminUserSeeder.php   <- creates/updates admin login(s) from .env, NOT in the automatic chain, always safe to re-run

resources/views/
  landing.blade.php         <- public page shell, @includes visible partials in order
  partials/                 <- one Blade file per homepage section (hero, about, services, ...)
  admin/                    <- CMS/admin views (website editor, content editor, settings, analytics dashboard)
  auth/                     <- login, forgot/reset-password
  layouts/                  <- app.blade.php (admin shell), guest.blade.php (auth pages), navigation.blade.php
  components/               <- shared Blade components (buttons, inputs, logo) — trimmed to only what's used, see below
  errors/404.blade.php

public/
  css/app.css     <- public landing page styles (no build step — edit directly)
  css/admin.css   <- admin panel styles (also loaded by auth pages, which live under /bojong)
  js/app.js       <- public page behavior: nav, hero carousel, services flip cards, testimonials, Leaflet map, analytics beacon
  js/admin.js     <- admin panel behavior: upload dropzone, sidebar toggle, auto-submit toggles
  images/, partner-logos/  <- static assets referenced by seeded CMS content

docs/
  CODEBASE.md   <- this file
  DATABASE.md   <- full schema reference
```

## No frontend build step

`public/css/*.css` and `public/js/*.js` are plain static files loaded via
`asset()` — there is no Vite/npm build. Edit them directly, refresh the browser.
(The default `laravel new` Vite scaffold files — `vite.config.js`, `package.json`,
`resources/css/app.css`, `resources/js/app.js` — were never wired into any Blade
view and are safe to ignore or remove if you find them; they don't affect
anything.)

## Auth scaffold — trimmed from the Laravel Breeze default

This started from Laravel Breeze's default auth scaffolding. What's actually
wired up: login (with rate-limiting + "remember me"), forgot-password,
reset-password, logout. What was **removed** during the pre-production cleanup
because nothing referenced it: the registration flow's email-verification event
listener (`EventServiceProvider`), the password-confirmation route/controller/view
(it existed but nothing required re-confirming a password before any action), and
a few unused Breeze Blade components (`dashboard.blade.php`,
`danger-button.blade.php`, `secondary-button.blade.php`). If you're looking for
"confirm password before doing X" as a security feature for some future sensitive
admin action, that's new work, not something disabled.

## Testing

`tests/Feature/`:
- `Auth/` — login, password reset.
- `Admin/` — CMS navigation, image upload, analytics dashboard.
- `Analytics/` — the collection endpoint's validation, idempotency, rate limiting,
  bot/admin exclusion, and the `analytics:prune` command.
- `Security/SecurityHardeningTest.php` — the broadest one: every admin route
  requires auth, security headers are present, Host-header spoofing is rejected,
  CMS rich text escapes XSS attempts, unsafe CTA URLs/colors/image references are
  rejected. Run this after any change touching auth, validation, or headers.

`php artisan test` runs everything against `phpunit.xml`'s testing environment
(in-memory SQLite, array cache/session, sync queue — independent of whatever
`.env`/`.env.production` say).

## Dependency/security note

`composer.json` pins `laravel/framework: ^9.19` — Laravel 9's security-support
window ended 2024-02-06. `composer audit` currently reports 12 known advisories
across `symfony/mailer`, `symfony/mime`, `symfony/process`, `symfony/routing`, and
others, all frozen at `v6.0.19` (Jan 2023) despite Laravel 9.52 permitting
`symfony/* : ^6.0` (i.e. the 6.4 LTS line is a compatible, non-breaking upgrade
path — no Laravel major bump needed for this specific fix). Run
`composer update symfony/mailer symfony/mime symfony/process symfony/routing
symfony/http-kernel symfony/http-foundation --with-all-dependencies` somewhere
with real internet access before/soon after the first production deploy, then
`php artisan test` to confirm nothing broke. A full Laravel 9→12 upgrade is a
separate, much larger project — not attempted here.
