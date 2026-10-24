# CMS / Admin panel — agent guide

Scope: everything under `/bojong` — the schema-driven CMS that lets a non-developer
admin edit every piece of landing-page copy, plus the separate analytics dashboard.
Covers this directory (`app/Http/Controllers/Admin/`), its `Concerns/HandlesCmsFields.php`
trait, `resources/views/admin/**`, `config/cms_schema.php`, `config/page_sections.php`,
and the two admin-related seeders. For the rest of the app (public landing page,
analytics collection endpoint, deployment) see the root `CLAUDE.md`/`AGENTS.md` and
`docs/CODEBASE.md` / `docs/DATABASE.md`.

## Mental model: it's all one schema-driven system

There is no per-section admin form hand-written in PHP. Every editable field on the
site — hero headline, service card steps, warehouse coordinates, SEO meta tags,
brand colors — is declared **once** in `config/cms_schema.php` as `{type, label, hint?,
required?, options?}`, and everything downstream (validation rules, the edit form's
input widget, encoding/decoding) is generated from that declaration by
`HandlesCmsFields` (this directory's `Concerns/HandlesCmsFields.php`).

**To add a new editable field to an existing section:** add one entry to that
section's `fields` array in `config/cms_schema.php`. That's it — `validateCmsFields()`
picks up the type-appropriate validation automatically, `admin/partials/field.blade.php`
renders the right input, and `encodeFieldValue()`/`cms_display_value()` handle
read/write. You do NOT hand-write a validation rule or a form input for a new field
unless its `type` doesn't exist yet (see field types below).

**To add a new repeatable item type** (like a new gallery, a new stat list): add a
`repeaters.<group>` block to the section in `cms_schema.php` with its own `fields`.
`ContentController` handles create/update/delete/reorder generically for any
repeater group — no controller changes needed.

**To add a whole new homepage section:** you need three things in sync —
1. a `resources/views/partials/<name>.blade.php` view,
2. an entry in `config/page_sections.php` pointing `view` at it and giving it
   `default_order`/`default_show_in_nav`/`schema_section`,
3. a matching top-level key in `config/cms_schema.php` (same name as `schema_section`)
   with that section's `fields`/`repeaters`.

Then add `'<name>'` to `PageController::SECTIONS` (`app/Http/Controllers/PageController.php`)
so the public page actually loads its content, and to `content_collections()`'s
traversal if it has repeaters editable from the "Content" admin page.

## Field types (`cms_schema.php` → validation → widget)

| `type` | Validation (`HandlesCmsFields::validateCmsFields`) | Notes |
|---|---|---|
| `text` | string, max 255 | keys ending `_href` get anchor/relative/HTTPS-only validation (see below); `email`/`whatsapp_number`/`instagram_handle` keys get format-specific regex |
| `textarea` | string, max 10000 | rendered as `<textarea>`; may contain the tiny formatting subset (see Rich text) |
| `checkbox` | boolean | stored as `'1'`/`'0'` string |
| `select` | must be one of `options` keys | `options` is `[value => label]` |
| `number` | numeric; `lat`→`between:-90,90`, `lng`→`between:-180,180`, else `between:0,1000000` | |
| `color` | `regex:/\A#[0-9a-fA-F]{6}\z/` | strict 6-digit hex only, blocks CSS/script injection via the color field |
| `image` | HTTPS URL or a stored relative path matching `jpe?g|png|webp|gif|svg` extension | see Image handling below — never a `data:` URI |
| `list` | string, max 20000, ≤100 lines, ≤500 chars/line | one item per line, encoded to/from a JSON array via `FieldCodec::listToArray()`/`arrayToList()` |
| `delimited_list` | string, max 20000, ≤100 lines, ≤1500 chars/line | one item per line, `Judul \| Penjelasan` format, via `FieldCodec::delimitedToArray()`/`arrayToDelimited()` |

**Adding a new field `type`** means adding a branch to `HandlesCmsFields::validateCmsFields()`
(validation) and to `admin/partials/field.blade.php` (rendering) — the one place that
doesn't come for free from the schema.

## Content storage model

Everything lives in one table, `cms_contents` (see `docs/DATABASE.md` for the full
schema) via the `Content` model (`app/Models/Content.php`), keyed by
`(section, item_key)` unique pair:

- **`type = 'field'`** rows are scalar values (`Content::putField()`), read with the
  `cms_field($sectionCollection, $key, $default)` helper.
- **`type = 'item'`** rows are repeater entries — `value` is a JSON-encoded array of
  that repeater's fields (`Content::putItem()`), read with `cms_items($sectionCollection)`.
  Item keys follow `<group>_<n>` (e.g. `slide_1`, `card_3`) — `ContentController`
  derives the next number from existing keys (`nextItemKey()`), don't hand-assign them.

`ContentRepository::section($section)` is the one read path every controller and the
public page go through — it's `Cache::rememberForever()`'d and only invalidated by
`ContentObserver` on `Content::saved`/`deleted`. If you ever write to `cms_contents`
outside the `Content` model's `putField`/`putItem` (e.g. a raw query or a seeder), the
cache will go stale — always go through the model.

## Rich text / XSS — read this before touching a `textarea` field's output

Admin-entered `textarea` content that reaches a public blade view MUST go through
**`cms_rich_text()`** (`app/Support/helpers.php`), never raw `{!! !!}`. It HTML-escapes
everything and then selectively re-allows only `<br>`, `<em>`, `<strong>` — this is
what stops an admin-entered `<img onerror=...>` from executing. If you add a new
`textarea` field that needs to render with line breaks or emphasis on the public
page, route it through this helper; don't invent a second sanitizer.

Any field key ending in **`_href`** automatically gets URL-safety validation for
free (anchor `#id`, relative path, or HTTPS URL only — `javascript:` is rejected).
Follow this naming convention for any new CTA-link-shaped field instead of writing
custom validation.

## Image handling

Two separate things named "image":

1. **Uploads** (`UploadController::store`, `POST /bojong/uploads`, called from
   `public/js/admin.js`'s dropzone) — accepts jpg/png ≤2MB, and
   `ImageUploadService` re-encodes every upload through GD into a fresh WebP file
   (caps at 12 megapixels before decoding, strips anything hidden in the original
   bytes). Never trust an uploaded file's extension/mime alone — this is why
   re-encoding happens unconditionally.
   Uploads larger than 720px also get a `<name>.thumb.webp` sibling
   (`Cms::thumbnailPath()`); public pages use `Cms::thumbUrl()` for grid-sized
   images (falls back to the original if no thumb exists, and resizes seeded
   Unsplash URLs via their `w=` param) and `Cms::largeUrl()` for full-size views.
   `php artisan cms:thumbnails` backfills thumbs for older uploads (safe to re-run).
2. **`type: 'image'` schema fields** — store either the HTTPS URL an upload
   returned, or an already-seeded absolute URL (some seeded content still points at
   `images.unsplash.com` or `asset('images/...')` directly — see `docs/DATABASE.md`
   for which). `HandlesCmsFields`'s image validator accepts HTTPS URLs or a stored
   relative path with an image extension; it rejects `data:` URIs specifically.

## Auth — no roles, two equal admin accounts

`/bojong/*` sits behind Laravel's plain `auth` middleware (`routes/web.php` →
`routes/admin.php`). There is **no roles/permissions system** — any authenticated
`User` row has full access to every admin route. Two accounts currently exist
(seeded via `AdminUserSeeder` — see below), both equal; if you're asked to
restrict what one admin can do vs another, that's new work (a role column +
authorization checks), not something already half-built.

## Seeders — the production landmine to know about

`database/seeders/DatabaseSeeder.php` calls **only** `CmsContentSeeder` — that's
what `php artisan db:seed` (no `--class`) runs. `CmsContentSeeder` seeds **every**
CMS section via `Content::putField()`/`putItem()`, which is `updateOrCreate` keyed
on `(section, item_key)`.

**This means re-running `php artisan db:seed` (or `migrate:fresh --seed`) after an
admin has edited real content through the UI will silently overwrite that content
back to the seeder's placeholder/demo values.** There is no confirmation, no diff —
it just wins the `updateOrCreate` race.

- **Local dev**: fine to re-run whenever you want to reset to a known state.
- **Production, after go-live**: never run `db:seed` again. Schema changes only
  ever go through `php artisan migrate --force`. Content changes only ever go
  through the admin UI.

`AdminUserSeeder` (creates/updates admin login accounts) is the opposite case —
it's deliberately **kept out of** the automatic `DatabaseSeeder` chain specifically
so it's always safe to re-run (`php artisan db:seed --class=AdminUserSeeder`)
without resetting a changed password; it reads `ADMIN_SEED_EMAIL`/`ADMIN_SEED_PASSWORD`
(+ optional `_2` pair for a second account) from `.env` and `updateOrCreate`s only
those specific user rows — it never touches `cms_contents`.

## Analytics dashboard (`/bojong/analytics`) — separate subsystem

Not CMS content — reads `analytics_sessions`/`analytics_events` (see
`docs/DATABASE.md`), populated by the **public, unauthenticated**
`POST /analytics/collect` endpoint (`AnalyticsCollectionController` +
`AnalyticsCollector` service, both outside this `Admin/` directory). If you're
asked to add a new metric to the dashboard, look at `AnalyticsController::index()`
(this directory) for the existing pattern (current-vs-previous-period comparison,
daily series for the chart) before adding a new query shape. Session data is
purged after `config('analytics.retention_days')` (90) days by the
`analytics:prune` Artisan command, scheduled daily at 02:30 `Asia/Jakarta` in
`app/Console/Kernel.php` — this requires the Laravel scheduler cron entry to
actually be running in production (see root `AGENTS.md`'s deployment section).

## Testing

- `tests/Feature/Security/SecurityHardeningTest.php` is the authoritative check for
  admin access control (every admin route requires auth), XSS escaping, and unsafe
  input rejection (bad CTA URLs, bad colors, `data:` image URIs). Any CMS/admin
  change that touches validation or auth should keep this green.
- `tests/Feature/Admin/CmsNavigationTest.php` — homepage editor + content page
  navigation.
- `tests/Feature/Admin/ImageUploadTest.php` — upload validation/conversion.
- `tests/Feature/Admin/AnalyticsDashboardTest.php` — dashboard access + period metrics.

Run `php artisan test --filter=Admin` (or the `Security` suite) after any
CMS/admin change, not just the full suite — these are the tests that actually
exercise this subsystem.
