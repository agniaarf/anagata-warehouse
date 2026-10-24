# Database

MySQL in every environment (`DB_CONNECTION=mysql` in both `.env` and
`.env.production`). Five tables total, created by five migrations under
`database/migrations/`, in this order:

1. `0001_01_01_000000_create_users_table.php` → `users`, `password_reset_tokens`
2. `2026_09_16_063104_create_cms_contents_table.php` → `cms_contents`
3. `2026_09_17_000000_create_analytics_tables.php` → `analytics_sessions`, `analytics_events`

(Two default Laravel scaffold migrations — `cache`/`cache_locks` and
`jobs`/`job_batches`/`failed_jobs` — plus the `sessions` table were removed during
the pre-production cleanup: this app uses `SESSION_DRIVER=file`, `CACHE_STORE=file`,
`QUEUE_CONNECTION=sync` everywhere, so none of those tables were ever written to.
If you ever switch any of those three to a `database` driver, you'll need to
re-add the corresponding migration.)

## `users`

Admin login accounts only — no public registration, no roles/permissions (every
row has identical access to `/bojong/*`).

| Column | Type | Notes |
|---|---|---|
| `id` | bigint, PK | |
| `name` | string | display name, not used for login |
| `email` | string, unique | login identifier |
| `email_verified_at` | timestamp, nullable | unused — no verification flow is wired up |
| `password` | string | bcrypt hash |
| `remember_token` | string, nullable | set when "Tetap masuk" is checked at login |
| `created_at`/`updated_at` | timestamp | |

Populated by `database/seeders/AdminUserSeeder.php`, run explicitly
(`php artisan db:seed --class=AdminUserSeeder`) — **not** part of the automatic
`db:seed` chain, specifically so re-seeding content never resets a changed
password. Reads `ADMIN_SEED_EMAIL`/`ADMIN_SEED_PASSWORD` (required) and
`ADMIN_SEED_EMAIL_2`/`ADMIN_SEED_PASSWORD_2` (optional second account) from
`.env`; safe to re-run any time (`updateOrCreate` on email).

## `password_reset_tokens`

Standard Laravel password-broker table (`email` PK, `token`, `created_at`) — used
by the "Lupa kata sandi?" flow regardless of session/cache driver config. Note:
`MAIL_MAILER=log` in both `.env` files means reset emails are currently only
written to the log file, not delivered — this table works, but the email that's
supposed to carry the reset link won't reach an inbox until real SMTP is
configured.

## `cms_contents`

The single table backing the entire CMS — every piece of editable landing-page
copy, from hero headline to SEO meta tags, is a row here. See
`app/Http/Controllers/Admin/AGENTS.md` for the full editing model; this is the
storage shape.

| Column | Type | Notes |
|---|---|---|
| `id` | bigint, PK | |
| `section` | string(40) | e.g. `hero`, `about`, `location`, `site_settings`, `seo` — matches a top-level key in `config/cms_schema.php` |
| `item_key` | string(60) | for `type=field` rows, the field name (e.g. `headline`); for `type=item` rows, `<repeater_group>_<n>` (e.g. `slide_1`, `card_3`) |
| `type` | string(10) | `field` or `item` (see `Content::TYPE_FIELD`/`TYPE_ITEM`) |
| `value` | longtext | scalar string for `field` rows; JSON-encoded array of that repeater item's fields for `item` rows |
| `order` | unsigned int, default 0 | display order — used both for homepage section order (`section='page_layout'`) and repeater item order within a group |
| `created_at`/`updated_at` | timestamp | |

Constraints: **unique** on `(section, item_key)` — this is the natural key the
whole CMS keys off (`Content::putField`/`putItem` are `updateOrCreate` on this
pair). Indexed on `section`.

Reads go through `ContentRepository::section($section)`, which is
`Cache::rememberForever()`'d per section and invalidated by `ContentObserver` on
every save/delete — never query `cms_contents` directly in a new code path, go
through `Content`/`ContentRepository` or the cache will drift.

**⚠️ Production content-safety rule**: `database/seeders/CmsContentSeeder.php`
seeds every section's placeholder/starter content via the same `putField`/`putItem`
`updateOrCreate` calls admin edits use — it is the ONLY seeder in the automatic
`db:seed` chain (`DatabaseSeeder.php`). Running `php artisan db:seed` (or
`migrate:fresh --seed`) again after go-live will silently revert any admin's real
edits back to the seeded placeholder content. After the initial production
deploy's one-time seed, never run it again — see root `AGENTS.md` deployment
section.

## `analytics_sessions`

One row per visitor session on the public landing page. Written only by
`App\Services\AnalyticsCollector` (via the public `POST /analytics/collect`
endpoint), read by `App\Http\Controllers\Admin\AnalyticsController` for the
`/bojong/analytics` dashboard.

| Column | Type | Notes |
|---|---|---|
| `id` | bigint, PK | |
| `session_key` | uuid, unique | client-generated, one per browser session |
| `visitor_key` | uuid, indexed | client-generated, persisted across sessions (localStorage) to distinguish repeat vs new visitors |
| `started_at` | timestamp, indexed, `useCurrent()` | set explicitly by `AnalyticsCollector`, not relied on for the DB default |
| `last_activity_at` | timestamp, indexed, `useCurrent()` | bumped on every subsequent event from the same session; a session older than `config('analytics.session_timeout_minutes')` (30) is rejected |
| `landing_path` | string(255), default `/` | first page path of the session — written but **not currently read anywhere** (single-page site, value is always `/`) |
| `referrer_domain` | string(253), nullable | host only, not full URL |
| `utm_source`/`utm_medium`/`utm_campaign` | string(100), nullable | |
| `device_type` | string(12), default `unknown` | `mobile`/`tablet`/`desktop`/`unknown`, derived from user-agent |
| `created_at`/`updated_at` | timestamp | |

Also indexed on `(visitor_key, started_at)`. Deliberately does **not** store raw
IP addresses — see root `CLAUDE.md`'s analytics section on privacy.

Purged after `config('analytics.retention_days')` (90 days, `.env`'s
`ANALYTICS_TIMEZONE` doesn't affect this cutoff — it's UTC-based) by the
`analytics:prune` Artisan command (`app/Console/Commands/PruneAnalytics.php`),
scheduled daily at 02:30 Asia/Jakarta in `app/Console/Kernel.php`. **This only
actually runs if the Laravel scheduler cron entry is installed on the server**
(`* * * * * php artisan schedule:run`) — see deployment notes in root `AGENTS.md`.

## `analytics_events`

One row per tracked interaction within a session (section scrolled into view, a
contact channel clicked).

| Column | Type | Notes |
|---|---|---|
| `id` | bigint, PK | |
| `analytics_session_id` | foreign key → `analytics_sessions.id`, cascade delete | pruning a session cascades its events |
| `event_key` | uuid, unique | client-generated, makes retries idempotent |
| `event_type` | string(20) | `session_start`, `section_view`, or `contact_click` (allowlisted via `Rule::in`) |
| `target` | string(60) | which section (`config('analytics.sections')`) or contact channel (`config('analytics.contact_channels')`) |
| `created_at` | timestamp, indexed | no `updated_at` — events are write-once |

Also indexed on `(event_type, created_at)` and `(analytics_session_id, event_type)`.

## Entity relationships

```
users            (standalone — auth only, no FK to anything else)

cms_contents     (standalone — keyed by section+item_key, not by any other table)

analytics_sessions ──1:N── analytics_events   (cascade delete)
```

There is no relationship between the CMS tables and the analytics tables — the CMS
describes *what the site shows*, analytics describes *how visitors used it*. They
share nothing but the same database.

## Local dev vs. production

Both environments use the same schema and the same MySQL driver — no SQLite
fallback (a stray `database/database.sqlite` file from the original
`composer create-project` scaffold was removed during cleanup since it was never
the active connection). The only differences are connection credentials
(`.env` vs `.env.production`) and which rows exist: local dev's `cms_contents`
holds whatever the last `db:seed` put there; production's should hold real,
admin-edited content after the initial bootstrap (see root `AGENTS.md`).
