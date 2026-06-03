# Codex Handoff

Use this file to keep Codex context synchronized between machines. Commit and push updates to this file when switching between home and office.

## Current Status

- Branch: `main`
- Last checked: 2026-06-03
- Git status at setup: clean
- Purpose: keep work context, decisions, verification steps, and known environment differences visible across machines.

## Working Notes

- Conversation history is not synchronized by git automatically; this file is the shared project memory.
- If something behaves differently between machines, compare `.env`, PHP/Composer/Node/npm versions, database state, cache, `vendor/`, and `node_modules/`.
- Location logic rule: use `locality_id` for create/edit/filter logic, `localities.slug` for URLs, and `localities.name` only for display text.
- Do not reseed `localities` on an existing database unless you are intentionally rebuilding location data; existing ads depend on stable locality IDs.

## Latest Changes

- 2026-06-03: Added project handoff workflow via `AGENTS.md` and this file.
- 2026-06-03: Added Romanian display names with diacritics for localities, updated `CountiesSeeder` to use them, added migrations to update existing `localities.name`, and synchronized legacy `services.city` from `localities.name`.
- 2026-06-03: Hid browser-native clear controls on combobox search inputs so only the custom `ia-combobox__clear` button is shown.

## Verification

- Ran `git status --short --branch`; repository was clean on `main`.
- Ran `php -l` on the new support class, both new migrations, and `CountiesSeeder.php`; no syntax errors.
- Ran `php artisan migrate`; both location migrations completed.
- Verified examples in DB: `Brașov` keeps slug `brasov`, `Iași` keeps slug `iasi`, `Târgu Mureș` keeps slug `targu-mures`.
- Verified all services with `locality_id` have `services.city` matching `localities.name`.
- Verified `Str::slug(localities.name)` matches existing `localities.slug` for all localities.
- Ran `php artisan optimize:clear`.
- Ran `php artisan test`; existing suite still has unrelated failures in auth/profile tests and SQLite test setup (`services` table missing for `/`). Location-related checks passed manually.
- Verified combobox clear source exactly: each combobox has one `data-combobox-clear` button in DOM, while inputs are `type="search"`, so the duplicate X came from the browser-native search cancel control.
- Ran Vite build through the local Node runtime: `node node_modules/vite/bin/vite.js build`; build completed.
- Verified the CSS served by Vite contains the new `::-webkit-search-cancel-button` and `::-ms-clear` rules.
- Opened local listing at `http://127.0.0.1:8010/anunturi-auto-de-vanzare?brand_id=11`; selected brand combobox had one visible custom clear button.

## Open Items

- Add project-specific setup commands once they are confirmed.
- Test environment needs cleanup: SQLite test database does not have the full app schema for the homepage test, `/profile` tests expect routes that currently return 404/405, and registration test does not authenticate the created user.

## Machine Handoff Checklist

Before stopping work:

1. Run `git status --short --branch`.
2. Note any unfinished work in this file.
3. Run relevant tests or manual checks and write the result here.
4. Commit and push code plus this handoff file.

After starting work on another machine:

1. Pull latest changes.
2. Read this file before asking Codex to modify code.
3. Confirm environment-sensitive settings if behavior differs.
