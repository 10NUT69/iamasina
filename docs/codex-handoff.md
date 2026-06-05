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
- 2026-06-03: Removed mobile sticky listing action-bar text truncation by replacing `truncate` labels and disabling ellipsis inside `#listing-actions-bar`.
- 2026-06-03: Adjusted the mobile listing filters panel offset to use the actual rendered bottom edge of `#main-nav`, so the opened filters panel starts immediately after the visible header even when the mobile header is partially hidden.
- 2026-06-03: Updated the shared combobox component to generate visible search input ids from field names, e.g. `tractiune_search`, keeping the real submitted value only on the hidden input.
- 2026-06-03: Added a combobox autofill guard: visible searchable inputs render as readonly initially and JS releases readonly shortly after real focus/click, preventing Chrome password manager from attaching to fields like Tracțiune.

- 2026-06-03: Removed the early-stage promotional listing banner/card from the listing controller data and listing Blade DOM, so listing pages start directly with real service cards.
- 2026-06-05: Added minimal BreadcrumbList JSON-LD to the car detail and listing Blade views, using existing view data only and leaving routes, controllers, URLs, filters, canonical links, and visual breadcrumbs unchanged.
- 2026-06-05: Preserved diacritics in listing meta and BreadcrumbList names by removing ASCII transliteration from `cleanMetaLabel`; URL slugs still use `Str::slug`.
- 2026-06-05: Updated the shared listing date label accessor to display `Astăzi` with diacritics across show, homepage, and listing cards.

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
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors.
- Ran Vite build through the local Node runtime again after the mobile sticky bar change; build completed.
- Verified the listing page at 390px mobile width in Edge headless: `#listing-actions-bar` remained sticky, had `oldTruncateCount = 0`, and action labels used `textOverflow: clip`, `whiteSpace: normal`, and `overflow: visible`.
- Ran `php -l resources/views/services/listing.blade.php` and `git diff --check`; both passed after the mobile filters offset change.
- Ran Vite build through the local Node runtime after the mobile filters offset change; build completed.
- Verified the listing filters panel at 390px mobile width in Edge headless for three states: top of page, partial header hide after `scrollY=24`, and fully hidden header after `scrollY=700`. In all cases `#filters-panel` and `#filters-overlay` started exactly at the measured bottom of `#main-nav` (`gapPanelToHeader = 0`, `gapOverlayToHeader = 0`).
- Ran `php -l resources/views/components/combobox.blade.php` and `git diff --check`; both passed after the combobox visible input id and autofill guard changes.
- Ran Vite build through the local Node runtime after the combobox JS guard change; build completed.
- Verified the rendered create listing page in Edge headless at `http://127.0.0.1:8010/anunturi-auto-de-vanzare/adauga-anunt`: the visible Tracțiune combobox input has `id="tractiune_search"`, `type="search"`, `autocomplete="new-password"`, no `name`, and no duplicate ids; the hidden input has `id="inputTractiune"` and `name="tractiune_id"`.
- Verified real click behavior in Edge headless: before click the visible Tracțiune input is readonly, immediately after click it remains readonly while the dropdown opens, and after 400ms JS releases readonly for normal combobox search.

- Ran `php -l app/Http/Controllers/ServiceController.php`, `php -l resources/views/services/listing.blade.php`, and `git diff --check`; all passed after removing the early-stage listing banner.
- Verified the rendered local listing page at `http://127.0.0.1:8010/anunturi-auto-de-vanzare`: no `Nou pe pia`, `Start gratuit`, or `homepage-hero-car` content remains, `data-service-card` appears 20 times, and the first rendered `<article>` is a real service card.
- Ran `git diff --check`; passed after adding BreadcrumbList JSON-LD.
- Ran `php artisan view:cache`; Blade templates compiled successfully after adding BreadcrumbList JSON-LD.
- Ran `php artisan view:clear`; compiled view cache was cleared after verification.
- Ran `git diff --check`; passed after preserving diacritics in listing labels.
- Ran `php artisan view:cache`; Blade templates compiled successfully after preserving diacritics in listing labels.
- Ran `php artisan view:clear`; compiled view cache was cleared after verification.
- Ran `php -l app/Models/Service.php`; no syntax errors after updating the listing date label.
- Ran `git diff --check`; passed after updating the listing date label.

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
