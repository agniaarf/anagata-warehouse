# Anagata Warehouse — agent entry point

Full project instructions live in **[`CLAUDE.md`](CLAUDE.md)** — read that file
first, it's the single source of truth for this project (setup, deployment, stack,
project structure, section order, design tokens, customization, and known
limitations).

This file used to be a full duplicate of `CLAUDE.md`, hand-copied on every edit.
It drifted out of sync in practice (missing whole sections, describing an older
front-end layout) — see the pre-production audit note in `CLAUDE.md`'s "Riwayat
keputusan desain". To stop that from happening again, this file now just points
to the one real doc instead of maintaining a second copy.

Other docs worth knowing about, all linked from `CLAUDE.md` too:
- [`docs/CODEBASE.md`](docs/CODEBASE.md) — architecture map (routing, request flow, directory structure).
- [`docs/DATABASE.md`](docs/DATABASE.md) — full schema reference, table by table.
- [`app/Http/Controllers/Admin/AGENTS.md`](app/Http/Controllers/Admin/AGENTS.md) — CMS/admin panel guide: how the schema-driven field system works, seeder safety rules, image handling.
