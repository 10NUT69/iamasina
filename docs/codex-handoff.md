# Codex Handoff

Use this file to keep Codex context synchronized between machines. Commit and push updates to this file when switching between home and office.

## Current Status

- Branch: `main`
- Last checked: 2026-06-12
- Git status at setup: clean
- Purpose: keep work context, decisions, verification steps, and known environment differences visible across machines.

## Working Notes

- Conversation history is not synchronized by git automatically; this file is the shared project memory.
- If something behaves differently between machines, compare `.env`, PHP/Composer/Node/npm versions, database state, cache, `vendor/`, and `node_modules/`.
- Location logic rule: use `locality_id` for create/edit/filter logic, `localities.slug` for URLs, and `localities.name` only for display text.
- Do not reseed `localities` on an existing database unless you are intentionally rebuilding location data; existing ads depend on stable locality IDs.

## Latest Changes

- 2026-06-13: Updated the mobile listing action bar so `Sus` is a passive scroll-only button that stays neutral, while `Salveaza` no longer shows the bell icon and uses saved-search toast lifecycle to stay red only while feedback is visible.
- 2026-06-13: Added an optional `onHide` callback to the shared toast helper and wired saved-search feedback events to the listing save button state; no routes, controllers, listing queries, or filter behavior were changed.

- 2026-06-13: Changed the Home seller tab label from `Toate` to `Toți` while keeping the submitted `seller_type=all` value unchanged.
- 2026-06-13: Refined the Home and listing seller tabs into a flatter segmented-control palette: active `#30323A` with white text and no shadow, inactive `#F7F8FA`/`#687080`, divider/border `#E6E8EC`, and inactive hover `#EEF1F4`/`#30323A`.
- 2026-06-13: Matched the Home page seller source tabs to the listing filter styling by using the anthracite active state (`#2F3137`), white text, and neutral shadow for `Toate` / `Proprietari` / `Parcuri Auto`.
- 2026-06-13: Changed the active `Tip Vânzător` listing filter tab from primary red to an anthracite selected state (`#2F3137`), keeping conversion actions visually owned by the main red buttons.
- 2026-06-13: Updated the active `Tip Vânzător` listing filter tab to use the darker primary red (`#BA1C23`), white text, and a subtle red shadow instead of the pale red selected state.
- 2026-06-13: Updated the mobile listing filters popup to portal `#filters-overlay` and `#filters-panel` into `document.body` while open, then restore them to the filters sidebar on close, so the popup can cover the fixed header without raising the whole listing `<main>`.
- 2026-06-13: Removed the mobile filters-open rule that raised the whole listing `<main>` to `z-index: 20000`; only the filters overlay and panel keep high z-index now, preventing listing cards from sitting above the header while the popup is open.
- 2026-06-13: Simplified the mobile listing filters popup so the overlay and sheet always start at viewport top (`top: 0`) instead of using the dynamic header offset; the sticky listing action bar can still use `--mobile-filters-top`.
- 2026-06-12: Changed the mobile listing filters sheet to auto-height with a viewport max-height, so the white container ends shortly after the reset/search buttons instead of continuing to the bottom of the screen.
- 2026-06-12: Compact the mobile listing filters panel header and form spacing so more filters fit in the first viewport; the hidden filter inputs are now explicitly marked `hidden` so Tailwind `space-y` does not add dead space before the first visible control.
- 2026-06-12: Fixed the mobile listing filters panel offset so opening filters locks the panel top to the header visibility at tap time: hidden header opens from viewport top, visible header opens below the header.
- 2026-06-12: Updated listing filter combobox icon behavior so, inside `#filters-panel`, the clear `x` replaces the dropdown arrow while a value is selected; clearing the value hides `x` and shows the arrow again.
- 2026-06-12: Tightened the source combobox CSS for all listing filter comboboxes inside `#filters-panel` so two-column filter fields have more usable text space before the clear/toggle controls.
- 2026-06-12: Widened the desktop listing filters sidebar from 300px to 340px by changing the existing `lg:w-[300px]` definition in `resources/views/services/listing.blade.php`; mobile remains fluid.
- 2026-06-12: Adjusted the listing filters layout only in `resources/views/services/listing.blade.php`: seller type now uses three buttons backed by the existing `seller_type` hidden input, brand/model, body/fuel, and transmission/county are paired on rows, and locality remains full-width.
- 2026-06-12: Optimized listing/show image gallery loading without route/controller changes: listing cards now keep only the first image in initial markup and progressively preload secondary images, while the show page defers hidden lightbox/mobile images and warms nearby gallery images.
- 2026-06-12: Reworked the admin manual media export so `admin.backups.media.export` now creates a `backup_exports` record and dispatches `GenerateMediaBackup` on `database_backups/backups` instead of building the ZIP in the HTTP request.
- 2026-06-12: Added private manual media export archives under `storage/app/private/backups/media-exports`, atomic `.zip.part` generation, completed/download/delete states in the admin Backup page, and a scheduled `backups:cleanup-media-exports` cleanup command.
- 2026-06-06: Nudged the mobile price-type badges down by 1px on both listing cards and the show page so they align better with the price text without changing size, shape, or colors.
- 2026-06-06: Tightened the mobile listing price-type badge shape further to `rounded` so it reads as a compact rectangle instead of a pill, while keeping `rounded-md` on desktop.
- 2026-06-06: Added the price-type badge to the listing show page on mobile and desktop so fixed-price ads show `PREȚ FIX` in red with the same dimensions as the existing negotiable badge.
- 2026-06-06: Tuned mobile listing price-type badge sizing by reducing badge padding and line height while keeping the rectangular shape, green/red colors, and larger desktop treatment.
- 2026-06-06: Restyled listing price-type badges to be slightly larger rectangular badges with lightly rounded corners, green for `NEGOCIABIL` and red for `PREȚ FIX`, across the responsive horizontal listing cards.
- 2026-06-06: Updated listing horizontal service cards so priced fixed ads show the `PREȚ FIX` badge while negotiable ads continue to show `NEGOCIABIL`.
- 2026-06-06: Added helper text under the optional account password field on the create listing form, suggesting a stronger password and restating the 6-character minimum.
- 2026-06-06: Updated the shared combobox autofill guard so touch/pen/coarse-pointer interactions release `readonly` immediately, restoring the mobile keyboard path while keeping the delayed desktop release used for password-manager suppression.
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
- 2026-06-05: Changed the deleted listing disabled contact button label from `Contact dezactivat` to `Anunt indisponibil` on desktop and mobile show views.

## Verification

- Ran `git diff --check`; passed after the mobile listing action-bar feedback update.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after the mobile listing action-bar feedback update.
- Ran Vite build through the bundled Codex Node runtime after changing the saved-search/toast JavaScript; build completed.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the mobile listing action-bar feedback update.
- Verified `http://auto.test/anunturi-auto-de-vanzare` in the in-app browser at 390x844 mobile viewport: `Salveaza` has no SVG icon, `Sus` scrolls from `window.scrollY=1100` back to `0` while staying white/neutral, and `Salveaza` turns red while the saved-search toast is visible then returns to white when the toast hides.

- Ran `php -l resources/views/services/index.blade.php`; no syntax errors after changing the Home seller tab label to `Toți`.
- Ran `git diff --check`; passed after changing the Home seller tab label to `Toți`.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after changing the Home seller tab label.
- Ran `php -l resources/views/services/index.blade.php` and `php -l resources/views/services/listing.blade.php`; no syntax errors after refining the seller tabs segmented-control palette.
- Ran `git diff --check`; passed after refining the seller tabs segmented-control palette.
- Ran Vite build through the bundled Codex Node runtime after adding the new seller tab arbitrary color classes; build completed.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after refining the seller tabs segmented-control palette.
- Verified `http://auto.test/` and `http://auto.test/anunturi-auto-de-vanzare` in the in-app browser: seller tab containers render with `rgb(247, 248, 250)` and `rgb(230, 232, 236)`, active tabs settle to `rgb(48, 50, 58)`/white/no shadow, inactive tabs settle to `rgb(247, 248, 250)`/`rgb(104, 112, 128)`, and clicking dealer/parcuri keeps the hidden `seller_type` value as `dealer`.
- Ran `php -l resources/views/services/index.blade.php`; no syntax errors after matching the Home seller tabs to the anthracite active state.
- Ran `git diff --check`; passed after matching the Home seller tabs to the anthracite active state.
- Ran Vite build through the bundled Codex Node runtime after updating the Home seller tab classes; build completed.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after matching the Home seller tabs to the anthracite active state.
- Verified `http://auto.test/` in the in-app browser: the default `Toate` tab renders with `rgb(47, 49, 55)`, white text, and neutral shadow; clicking `Parcuri Auto` moves the same anthracite active styling and keeps the hidden `seller_type` value as `dealer`.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after switching the seller filter active state to anthracite.
- Ran `git diff --check`; passed after switching the seller filter active state to anthracite.
- Ran Vite build through the bundled Codex Node runtime after adding the anthracite Tailwind arbitrary color/shadow classes; build completed.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after switching the seller filter active state to anthracite.
- Verified `http://auto.test/anunturi-auto-de-vanzare` in the in-app browser at mobile viewport: the default `Toți` tab renders with `rgb(47, 49, 55)`, white text, and neutral shadow; clicking `Parcuri` moves the same anthracite active styling and keeps the hidden `seller_type` value as `dealer`.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after the seller filter active-state color update.
- Ran `git diff --check`; passed after the seller filter active-state color update.
- Ran Vite build through the bundled Codex Node runtime after adding the new Tailwind arbitrary color/shadow classes; build completed.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the seller filter active-state color update.
- Verified `http://auto.test/anunturi-auto-de-vanzare` in the in-app browser at mobile viewport: the default `Toți` tab renders with `rgb(186, 28, 35)`/white text/shadow, and clicking `Parcuri` moves the same active styling while keeping the hidden `seller_type` value as `dealer`.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after adding the mobile filters body portal.
- Ran `git diff --check`; passed after adding the mobile filters body portal.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after adding the mobile filters body portal.
- Verified `http://auto.test/anunturi-auto-de-vanzare` in the in-app browser at a mobile viewport: with the header visible and hidden after scroll, opening filters moved `#filters-overlay` and `#filters-panel` under `BODY`, both started at viewport top `0`, the popup layered above the header/card content, and closing filters restored both elements to the original `ASIDE` while `<main>` stayed at computed `z-index: 0`.
- Ran `git diff --check`; passed after removing the filters-open `<main>` z-index lift.
- Ran Vite build through the bundled Codex Node runtime after removing the filters-open `<main>` z-index lift; build completed.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after removing the filters-open `<main>` z-index lift.
- Verified `http://auto.test/anunturi-auto-de-vanzare` in the in-app browser at 390x844 mobile viewport with filters open after scroll: `<main>` computed z-index stayed `0`, listing cards stayed `auto`, header stayed `50`, overlay/panel stayed `20001/20002`, and only the filter popup/overlay layered over the page.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after making the mobile filters popup start at top.
- Ran `git diff --check`; passed after making the mobile filters popup start at top.
- Ran Vite build through the bundled Codex Node runtime after making the mobile filters popup start at top; build completed.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after making the mobile filters popup start at top.
- Verified `http://auto.test/anunturi-auto-de-vanzare` in the in-app browser at 390x844 mobile viewport using coordinate clicks: with the header visible and with it hidden after scroll, `#filters-panel`, `#filters-overlay`, and `.filters-panel-sheet` all opened at top `0`; the sheet remained auto-height and ended about 13px below the submit button.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after changing the mobile filters sheet to auto-height.
- Ran `git diff --check`; passed after changing the mobile filters sheet to auto-height.
- Ran Vite build through the bundled Codex Node runtime after changing the mobile filters sheet to auto-height; build completed.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after changing the mobile filters sheet to auto-height.
- Verified `http://auto.test/anunturi-auto-de-vanzare` in the in-app browser at 390x844 mobile viewport: the overlay still covers the screen, while `.filters-panel-sheet` ended about 13px below the submit button instead of extending to the viewport bottom.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after compacting the mobile filters panel.
- Ran `git diff --check`; passed after compacting the mobile filters panel.
- Ran Vite build through the bundled Codex Node runtime after compacting the mobile filters panel; build completed.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after compacting the mobile filters panel.
- Verified `http://auto.test/anunturi-auto-de-vanzare` in the in-app browser at 390x844 mobile viewport: the filters panel header measured 57px, the gap from the header to `Tip Vânzător` measured 12px, and the submit button fit within the viewport.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after the mobile filters offset lock.
- Ran `git diff --check`; passed after the mobile filters offset lock.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the mobile filters offset lock.
- Verified `http://auto.test/anunturi-auto-de-vanzare` in the in-app browser at 390x844 mobile viewport using coordinate clicks: after scrolling until the header is hidden, opening filters keeps `#filters-panel` and `#filters-overlay` at top `0`; from page top with the header visible, opening filters keeps the panel at top `56px` under the header.
- Ran `git diff --check`; passed after making the listing filter clear icon replace the dropdown arrow for selected values.
- Ran Vite build through the bundled Codex Node runtime after the listing filter icon behavior change; build completed.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the listing filter icon behavior change.
- Verified `http://auto.test/anunturi-auto-de-vanzare?caroserie_id=4` in the in-app browser on desktop and at 390x844 mobile viewport with the filters panel open: with `Hatchback` selected, the clear `x` sits in the arrow position and the dropdown arrow is hidden; after clicking `x` on desktop, the value clears and the arrow reappears.
- Ran `git diff --check`; passed after the listing filter combobox text-spacing change.
- Ran Vite build through the bundled Codex Node runtime after the listing filter combobox text-spacing change; build completed.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the listing filter combobox text-spacing change.
- Verified `http://auto.test/anunturi-auto-de-vanzare?caroserie_id=4` in the in-app browser on desktop and at 390x844 mobile viewport with the filters panel open: all listing filter comboboxes (`Marca`, `Model`, `Tip caroserie`, `Combustibil`, `Transmisie`, `Județ`, `Localitate`) used the updated spacing, and `Hatchback` rendered fully.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after widening the desktop filters sidebar.
- Ran `git diff --check`; passed after widening the desktop filters sidebar.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after widening the desktop filters sidebar.
- Verified `http://auto.test/anunturi-auto-de-vanzare` in the in-app browser at 1280px desktop viewport: the listing filters `<aside>` and `.filters-panel-sheet` both measured 340px wide.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after the listing filters layout change.
- Ran `git diff --check`; passed after the listing filters layout change.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the listing filters layout change.
- Verified `http://auto.test/anunturi-auto-de-vanzare` in the in-app browser on desktop and at 390x844 mobile viewport: seller buttons render with `Toți` active by default, clicking `Parcuri` updates the existing hidden `seller_type` value to `dealer`, brand/model share a row, body/fuel share a row, transmission/county share a row, and locality stays full-width.
- Ran `php artisan view:clear` and `php artisan view:cache`; Blade templates compiled after the progressive image-loading change.
- Ran `php -l resources/views/services/partials/service_cards_horizontal.blade.php` and `php -l resources/views/services/show.blade.php`; no syntax errors after the progressive image-loading change.
- Ran `git diff --check`; passed after the progressive image-loading change.
- Verified `git diff --name-only` only listed `resources/views/services/partials/service_cards_horizontal.blade.php` and `resources/views/services/show.blade.php` before updating this handoff; no routes/controllers were changed for the image-loading optimization.
- Ran `php artisan test --filter=AdminMediaBackupExportTest`; 11 tests / 47 assertions passed for the async manual media export flow.
- Ran `php -l` on `BackupExport.php`, `GenerateMediaBackup.php`, `ManualMediaBackupArchiver.php`, `CleanupMediaBackupExports.php`, `AdminBackupController.php`, the new `backup_exports` migration, and `AdminMediaBackupExportTest.php`; no syntax errors.
- Ran `php artisan route:list --name=backups`; confirmed the admin backup routes include media export start, media download, and media delete routes.
- Ran `php artisan list backups`; confirmed `backups:cleanup-media-exports` is registered.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the Backup page change.
- Ran `git diff --check`; passed after the async manual media export change.
- Ran `php -l resources/views/services/partials/service_cards_horizontal.blade.php` and `php -l resources/views/services/show.blade.php`; no syntax errors after the mobile badge alignment tweak.
- Ran `git diff --check`; passed after the mobile badge alignment tweak.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the mobile badge alignment tweak.
- Ran `php -l resources/views/services/partials/service_cards_horizontal.blade.php`; no syntax errors after reducing the mobile listing badge corner radius.
- Ran `git diff --check`; passed after reducing the mobile listing badge corner radius.
- Ran `php artisan view:clear`; cleared compiled Blade views after the mobile listing badge shape adjustment.
- Ran `php -l resources/views/services/show.blade.php`; no syntax errors after adding fixed-price badges to the show page.
- Ran `git diff --check`; passed after adding fixed-price badges to the show page.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the show-page price-type badge update.
- Ran `php -l resources/views/services/partials/service_cards_horizontal.blade.php`; no syntax errors after tightening mobile listing price-type badge sizing.
- Ran `git diff --check`; passed after tightening mobile listing price-type badge sizing.
- Rendered `services.partials.service_cards_horizontal` in CLI with fake fixed and negotiable services; confirmed the generated badge classes keep `rounded-md`, use tighter mobile `px-2 py-0.5 leading-none`, and preserve desktop `md:px-2.5 md:py-1`.
- Ran `php -l resources/views/services/partials/service_cards_horizontal.blade.php`; no syntax errors after restyling the listing price-type badges.
- Ran `git diff --check`; passed after restyling the listing price-type badges.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after restyling the listing price-type badges.
- Rendered `services.partials.service_cards_horizontal` in CLI with fake fixed and negotiable services; confirmed the generated HTML includes `PREȚ FIX`, `NEGOCIABIL`, red fixed styling, green negotiable styling, larger badge classes, and rectangular `rounded-md` shape rather than `rounded-full`.
- Ran `php -l resources/views/services/partials/service_cards_horizontal.blade.php`; no syntax errors after the listing price badge change.
- Ran `git diff --check`; passed after the listing price badge change.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the listing price badge change.
- Rendered `services.partials.service_cards_horizontal` in CLI with fake fixed and negotiable services; confirmed one `PREȚ FIX` badge and one `NEGOCIABIL` badge in the generated HTML.
- Started a temporary local Laravel server at `http://127.0.0.1:8010` for browser verification, but the listing route could not be verified live because local MySQL refused the configured connection; stopped the temporary server afterward.
- Ran `php -l resources/views/services/create.blade.php`; no syntax errors after the password helper text change.
- Ran `git diff --check`; passed after the password helper text change.
- Ran `git fetch --prune` and `git status --short --branch`; local `main` was aligned with `origin/main` before the combobox mobile-keyboard fix.
- Ran `git diff --check`; passed after the combobox touch-release change.
- Ran Vite build through the bundled Codex Node runtime after the combobox touch-release change; build completed.
- Started the local Laravel server at `http://127.0.0.1:8010`, confirmed the create listing page returned HTTP 200, then stopped the temporary PHP server.
- Further mobile/browser automation was stopped at user request; real-device mobile testing is still expected.
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
- Ran `git diff --check`; passed after changing the deleted listing button label.
- Ran `php artisan view:cache`; Blade templates compiled successfully after changing the deleted listing button label.
- Ran `php artisan view:clear`; compiled view cache was cleared after verification.

## Environment Assumptions

- Manual media exports are stored on the local/private disk path `storage/app/private/backups/media-exports`; database rows store only relative paths like `backups/media-exports/<file>.zip`.
- Production must run the migration with OpenLiteSpeed PHP from `/home/iaAuto.ro/public_html`: `sudo -u iaauto /usr/local/lsws/lsphp83/bin/php artisan migrate --force`.
- Because `config/queue.php` changed, production must refresh Laravel config cache before starting the backup worker: `sudo -u iaauto /usr/local/lsws/lsphp83/bin/php artisan optimize:clear`, then `sudo -u iaauto /usr/local/lsws/lsphp83/bin/php artisan config:cache`.
- Production needs a separate Supervisor worker for `queue:work database_backups --queue=backups --sleep=3 --tries=1 --timeout=7200`; use Linux user `iaauto`, not the `iaAuto.ro` folder/domain name, and do not reuse the image-processing worker for this queue.
- Supervisor config to add manually on production:
  ```ini
  [program:iaauto-backup-worker]
  process_name=%(program_name)s_%(process_num)02d
  command=/usr/local/lsws/lsphp83/bin/php /home/iaAuto.ro/public_html/artisan queue:work database_backups --queue=backups --sleep=3 --tries=1 --timeout=7200
  directory=/home/iaAuto.ro/public_html
  user=iaauto
  numprocs=1
  autostart=true
  autorestart=true
  stopasgroup=true
  killasgroup=true
  redirect_stderr=true
  stdout_logfile=/home/iaAuto.ro/public_html/storage/logs/backup-worker.log
  stdout_logfile_maxbytes=20MB
  stdout_logfile_backups=5
  stopwaitsecs=7500
  ```
- Optional `.env` keys are `DB_BACKUP_QUEUE` and `DB_BACKUP_QUEUE_RETRY_AFTER`; defaults are `backups` and `7500`.
- Local Windows environment used PHP 8.3.26 from Laragon.
- The Vite dev server was already serving assets from `http://[::1]:5173`.
- The bundled Codex Node runtime was used for Vite build because `node.exe` from PATH returned `Access denied`.

## Open Items

- Production deployment still needs the new migration and manual Supervisor worker setup for the backup queue; no production migration or Supervisor changes were executed locally. Correct order after deploy: `cd /home/iaAuto.ro/public_html`, `sudo -u iaauto git pull`, `sudo -u iaauto /usr/local/lsws/lsphp83/bin/php artisan optimize:clear`, `sudo -u iaauto /usr/local/lsws/lsphp83/bin/php artisan migrate --force`, `sudo -u iaauto /usr/local/lsws/lsphp83/bin/php artisan config:cache`, then `sudo supervisorctl reread`, `sudo supervisorctl update`, `sudo supervisorctl status`.
- Add project-specific setup commands once they are confirmed.
- Test environment needs cleanup: SQLite test database does not have the full app schema for the homepage test, `/profile` tests expect routes that currently return 404/405, and registration test does not authenticate the created user.
- User should test the shared combobox behavior on a real mobile device, especially create/edit/listing filters and the original Tractiune/password-manager case.

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
