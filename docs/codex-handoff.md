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

- 2026-07-03: Added dealer logo support for car-park accounts. A new nullable `users.dealer_logo` column stores one logo path, `ProfileController` now exposes authenticated upload/delete endpoints for dealer logos with image validation and server-side/client-side optimization, and `User::dealer_logo_url` provides a reusable display URL. The account profile tab now shows the dealer logo in the profile sidebar/header plus a logo upload form above the dealer gallery, with immediate preview/delete behavior in both places. Dealer logos now display on the public dealer portfolio identity block and in the dealer card on listing detail pages, using centered cover-crop fill inside the logo square/card and falling back to the existing initial block when no logo is uploaded. Switching a saved dealer account to individual now requires explicit confirmation, then clears dealer data, removes the public dealer page URL, and deletes both the logo file and dealer gallery image files.

- 2026-07-03: Updated the account profile dealer media uploads so a user who selects `Parc auto` and fills dealer details can upload the dealer logo/gallery without first pressing the bottom save button. The logo and gallery upload actions now auto-save the dealer profile through the existing AJAX profile update when the saved account is still `individual`, skip password changes during that implicit save, and show dealer-field validation messages in the media upload area if required data is missing.

- 2026-07-03: Added dealer tier support on `users.dealer_tier` with allowed application values `standard`, `founding`, and `premium`. The new migration adds the column with default `standard` and backfills existing users to `standard`; the `User` model exposes tier constants/labels and normalizes invalid/missing values to `standard` on save. Admin users can now set dealer tiers inline from the users table using only the tier dropdown, or through bulk actions for selected dealer accounts. Public dealer portfolio pages show a discrete badge only for special tiers (`Fondator` / `Premium`); `standard` stays visually neutral.

- 2026-07-03: Made the admin users table sortable by `Utilizator`, `Tip dealer`, and `Inregistrat`, using the same query-string sort pattern already used by the `Anunturi` column. The first click on `Utilizator` sorts A-Z; `Tip dealer` starts with the highest/special tier first; `Inregistrat` starts with the newest users first. Dealer-tier sorting keeps dealer accounts grouped ahead of non-dealer accounts, orders `standard` / `founding` / `premium` according to the active direction, and uses user name as a stable tie-breaker.

- 2026-07-03: Refined the dealer seller card on listing detail pages so dealer info is split into clear rows: dealer name, an optional gold `Membru fondator` banner only for `founding` dealers, then `Înregistrat în <luna>, <an>`. Dealers without the founding tier show only the name and registration rows. The dealer logo/avatar in this card is now a fixed 72px square so it visually matches the two- or three-row text block.

- 2026-07-03: Investigated service listing image jobs after a local worker/queue mismatch. The listing publish flow in `ServiceController` was not changed: listings still save first with `images = []`, uploaded files are stored temporarily, and `ProcessServiceImages` fills `services.images` asynchronously. Locally, `SERVICE_IMAGES_QUEUE` was unset while a manually started worker listened only to `services`, so local `ProcessServiceImages` jobs landed on `default` and were not consumed until they were moved to `services`. Live was later checked and is different: the iaAuto Supervisor worker runs `queue:work --sleep=1 --tries=3` with no `--queue`, so it consumes the configured default queue (`default`); do not force service image jobs to `services` on live unless the iaAuto worker is also changed to listen to that queue.

- 2026-07-03: Improved mobile listing filter dropdown positioning for the lower `Localitate` combobox. Mobile filter sheets now get temporary bottom scroll space only while a listing-filter dropdown is open, allowing the last field to scroll high enough for its downward dropdown without the dropdown pushing form fields. Async option updates now re-sync open dropdown positioning after options are populated. Also set the mobile saved-search button label back to bold while using the same gray tone as the `Sortare` combobox label.

- 2026-07-03: Tightened the mobile listing sort combobox so `Recomandata` has enough room to render fully in the sticky action bar. The sort input now uses smaller mobile-only right padding, a slightly smaller font on very narrow screens, and a raised chevron kept at its original visual size.

- 2026-07-03: Adjusted the mobile listing sticky action bar so the saved-search button label wraps as `Salvează` / `căutarea`, the saved-search column is narrower, and the sort column is wider. The change is scoped to the listing action bar markup/CSS in `resources/views/services/listing.blade.php`.

- 2026-07-03: Allowed desktop listing filter combobox dropdowns to overflow past the filters card/form instead of being constrained by the card. Desktop filter panel/sheet overflow is now explicitly visible, while dropdown height is bounded by the viewport rather than the filters card.

- 2026-07-03: Adjusted mobile listing filter combobox dropdown behavior after iPhone testing: filter dropdowns now always open downward as overlays, while mobile fields inside `.filters-panel-sheet` auto-scroll upward when needed so lower fields have enough room below. The code schedules delayed re-syncs for iOS visual viewport/keyboard timing and keeps dropdown max-height constrained to the visible sheet/viewport space.

- 2026-07-03: Updated the shared combobox behavior so opening a dropdown no longer visually highlights the first option automatically. `activeIndex` now starts unset after filtering/opening, `aria-activedescendant` is cleared until keyboard navigation actually selects an active option, and real selected values still use the existing `is-selected` state.

- 2026-07-03: Changed listing filter combobox dropdowns to open as overlays instead of lengthening the filters form. Removed the listing-filter dropdown space reservation and mobile `position: static` dropdown behavior, added smart up/down positioning with a calculated max-height for filter dropdowns inside `#filters-panel`, and kept recalculation active on sheet/window/visual viewport scroll/resize so lower mobile fields can open upward.

- 2026-07-03: Removed the desktop listing header count text (`<count> anunturi disponibile`) from `resources/views/services/listing.blade.php`, leaving the page title/description and lower pagination summary untouched.

- 2026-07-03: Refined the public listing sort combobox so `Recomandata` is selected by default when no `sort` query is present, the sort value remains non-clearable, and the listing-specific sort input uses the normal arrow cursor instead of the text I-beam cursor on desktop.

- 2026-07-03: Updated the public listing sort control in `resources/views/services/listing.blade.php` so changing `Sortare` applies the selected order immediately on desktop and mobile through the existing AJAX listing refresh, without waiting for the `Afiseaza rezultatele` filter submit button.

- 2026-06-26: Updated the public dealer portfolio SEO/share metadata in `resources/views/services/dealer-portfolio.blade.php`. The dealer page now builds dynamic title/meta title as `<Nume dealer> - anunțuri auto din <Oraș>, <Județ>` when location data exists, and dynamic meta description with the active listing count, dealer name, city/county location, and the requested iaAuto.ro callout. Social share image now uses the first dealer profile/gallery image when present, then falls back to the first visible dealer listing image, then the existing site default. The existing dealer page route/controller behavior was left unchanged.

- 2026-06-26: Updated the public dealer portfolio location display to follow the same source of truth used by listings: `localities.name` and `counties.name` from `locality_id` / `county_id` are preferred over legacy `users.city` / `users.county` strings. This fixes uppercase/non-diacritic dealer locations such as `PITESTI, ARGES` rendering on the portfolio page, metadata, breadcrumb, address, map query, and map label; fallback to legacy fields remains for incomplete dealer profiles.

- 2026-06-23: Added the GA4 `listing_published` event for successful public listing publication. The exact publication flow is the create form at `GET /anunturi-auto-de-vanzare/adauga-anunt`, posting to `POST /anunturi-auto-de-vanzare/adauga-anunt` (`services.store`), handled by `ServiceController::store()`. The controller now flashes `ga4_listing_published_event` only after the service is saved and marked active/published, then redirects through the existing success flow. The shared app layout consumes that flash once after redirect and calls `gtag('event', 'listing_published')` only when Analytics consent is active and `gtag` is available. The existing Meta Pixel `ListingPublished` event and payload were left unchanged.

- 2026-06-21: Added the Meta Pixel `ListingPublished` custom event for successful public listing publication. The only server-side trigger is `ServiceController::store()` after the service has been saved and marked active/published; it flashes `meta_listing_published_event.meta_event_id` with a fresh UUID and redirects through the existing success flow. The shared app layout consumes that flash once after redirect and calls `window.iaAutoMetaPixel.trackCustom('ListingPublished', { content_category: 'vehicle_listing' }, { eventID: metaEventId })` only when the consent-gated Pixel helper is ready. Pending events stay in page memory only, are canceled on explicit Marketing refusal or page leave, and are not added to edit, renew/reactivation, delete, admin, import, or promotion flows.

- 2026-06-21: Added a consent-gated Meta Pixel integration to the existing cookie consent component. The Pixel ID is read from `META_PIXEL_ID` through `config('services.facebook.pixel_id')`; no Pixel ID is hardcoded in Blade/JS. Meta Pixel is loaded only when the saved/live Marketing consent is active, sends one PageView per page load, calls `fbq('consent', 'revoke')` when Marketing is withdrawn after load, disables Meta auto configuration before `init`, and exposes `window.iaAutoMetaPixel.track(...)` / `trackCustom(...)` for future events. No `noscript` tracking image, conversion events, Conversions API, or advanced matching data were added.

- 2026-06-19: Applied the same supported Facebook Share Dialog behavior to the public service show page: the Facebook share control now builds a Dialog URL with `FACEBOOK_APP_ID`, opens desktop as `display=popup`, opens mobile as `display=touch`, and always uses a new tab so the listing page is not replaced. No routes/controllers were changed.

- 2026-06-19: Updated the account listing Facebook share action so desktop keeps the existing Share Dialog popup behavior, while mobile opens the supported Facebook Share Dialog with `display=touch` in a new tab instead of trying unsupported `fb://` / Android intent routes. The share data still comes from the existing ad URL/title and `services.facebook.app_id` / `FACEBOOK_APP_ID` config path; no secrets were added.

- 2026-06-14: Moved the desktop listing breadcrumb into a full-width row directly under the public header so long breadcrumb fragments no longer share the row with `Salveaza` / `Sortare`; the right-side listing controls now start underneath the breadcrumb. Also made the `no-scrollbar` utility global and changed the service show visual breadcrumb to stop at the last navigable listing segment instead of showing the current ad title.

- 2026-06-14: Tightened public breadcrumb placement so listing, service show, and dealer portfolio breadcrumbs sit about 10px below the fixed header. On the service show page, the breadcrumb now renders above the `Înapoi` button, with the back button on its own row below.
- 2026-06-14: Added a shared segmented breadcrumb component and replaced the visual breadcrumbs on the listing page, service show page, and dealer portfolio page. Breadcrumb segments now match the supplied chevron-chip style on desktop and mobile, every visible segment is rendered as a link, and the dealer county breadcrumb falls back to the county slug URL when the dealer account has no `county_id`.
- 2026-06-14: Updated the dealer portfolio hero CTAs so `Vezi anunțurile` is the secondary outlined action matching the Home/index secondary CTA style, while `Sună acum` is the red primary action. Replaced the stock section title/subtitle pair (`Stocul parcului auto` + `3 rezultate`) with a single heading like `3 anunțuri auto oferite de <nume parc>`. Confirmed the dealer page still builds phone display from all three dealer phone fields (`phone`, `phone_2`, `phone_3`) when present.
- 2026-06-14: Simplified the dealer portfolio `Despre` section so it renders only real dealer description text, capped to the same 3,000-character limit used in the account form, removes the previous trust cards (`Stoc actualizat`, `Anunțuri active`, `Date publice`, `Contact direct`), changes the heading to `Despre <nume parc>`, and hides the `Despre parc` stock-nav tab when no description exists. Added a `Pagina parcului` link to the `Contul meu` tabs for dealer accounts with a public dealer URL.
- 2026-06-14: Restyled the public service show page secondary message actions, dealer portfolio links, and safety recommendation card with the requested neutral/premium and amber palettes; the primary `Sună` phone CTA was left unchanged, and the safety copy now uses the safer "Recomandare de siguranță" wording.
- 2026-06-14: Updated the dealer portfolio page so the main gallery image opens the gallery when clicked, the dealer stock filters use the shared combobox component while still showing only brands/models available in that dealer's stock, and public car card/gallery images on Home, AJAX Home cards, Listing, Show, generic card partials, and dealer stock cards explicitly crop from the visual center with `object-center`.
- 2026-06-14: Centered the dealer portfolio gallery crop by adding explicit `object-center` positioning to the main dealer gallery image and gallery thumbnails, so cover-cropped images keep the visual middle instead of favoring the top edge.
- 2026-06-14: Compact the dealer portfolio hero so the desktop dealer info card and gallery occupy roughly half the previous vertical space: the gallery/card row now measures about 320px tall on desktop, with internal thumbnail rows constrained to the same height; mobile remains stacked and unchanged in overall behavior.
- 2026-06-14: Restyled the public dealer portfolio page (`services.dealer-portfolio`) to follow the supplied dealer-page mockup using only existing account/dealer data: breadcrumb, dealer info card, gallery with main image plus thumbnails, stock tabs/filter/card section, about block, location/contact block, and mobile actions; no routes or controllers were changed.
- 2026-06-14: Made the mobile header auto-hide behavior global for all pages using the shared public app layout by setting the existing `main-nav` scroll logic to always participate on mobile; removed the unused legacy `mainHeader` / `header-scrolled` / `logo-main` scroll-shrink code from `resources/js/app.js` and `resources/css/app.css`.
- 2026-06-14: Updated the service show page mobile layout: important-detail cards now stay two per row on phone widths, dealer seller cards no longer show the green `Verificat` badge, and dealer listings now show a mobile `Vezi portofoliu dealer` button immediately before the safety warning; the existing desktop portfolio button text was changed to the same label.
- 2026-06-14: Added dark-mode active-state classes to the Home seller source tabs and listing seller type tabs, and kept the JavaScript class toggles in sync so `Toți` / `Proprietari` / `Parcuri` remain dark-theme aware after interaction.
- 2026-06-13: Removed the global `html, body { overscroll-behavior-y: none; }` rule from the public app layout so iOS Safari can use native page overscroll/pull-to-refresh again; mobile listing filter popup scroll-lock rules were left unchanged.
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

- Ran `php -l app/Http/Controllers/ProfileController.php`, `php -l app/Models/User.php`, `php -l routes/web.php`, and `php -l database/migrations/2026_07_03_120000_add_dealer_logo_to_users_table.php`; no syntax errors after adding dealer logo support.
- Ran `php -l resources/views/account/index.blade.php`; no syntax errors after also showing the dealer logo in the profile sidebar/header.
- Ran `php -l resources/views/account/index.blade.php`, `php -l resources/views/services/dealer-portfolio.blade.php`, and `php -l resources/views/services/show.blade.php`; no syntax errors after changing dealer logo images from contained/padded display to centered cover-fill display.
- Ran `git diff --check`; passed after the dealer logo changes.
- Ran `php artisan migrate`; `2026_07_03_120000_add_dealer_logo_to_users_table` completed locally.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the dealer logo UI/rendering updates.
- Ran `php -l resources/views/account/index.blade.php`; no syntax errors after adding the pre-upload dealer profile auto-save flow.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the pre-upload dealer profile auto-save flow.
- Ran `git diff --check`; passed after the pre-upload dealer profile auto-save flow.
- Ran `php -l` on `app/Models/User.php`, `app/Http/Controllers/Admin/AdminUserController.php`, `app/Http/Controllers/Auth/RegisteredUserController.php`, `app/Http/Controllers/ServiceController.php`, `routes/web.php`, `resources/views/admin/users/index.blade.php`, `resources/views/services/dealer-portfolio.blade.php`, `resources/views/services/show.blade.php`, and `database/migrations/2026_07_03_130000_add_dealer_tier_to_users_table.php`; no syntax errors after adding dealer tiers.
- Ran `php artisan migrate`; `2026_07_03_130000_add_dealer_tier_to_users_table` completed locally and all 69 local users reported `dealer_tier = standard`.
- Ran a rollback-wrapped `User::create()` tinker check for a dealer account; the new user received `dealer_tier = standard` before rollback.
- Ran `php artisan route:list --name=admin.users.dealer-tier`; confirmed `PATCH panou-secret/users/{user}/dealer-tier` points to `AdminUserController@updateDealerTier`.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the dealer tier admin/public UI updates.
- Ran `php -l resources/views/admin/users/index.blade.php`; no syntax errors after removing the duplicated dealer-tier badge above the admin dropdown.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after leaving only the admin dealer-tier dropdown.
- Ran `php -l app/Http/Controllers/Admin/AdminUserController.php` and `php -l resources/views/admin/users/index.blade.php`; no syntax errors after adding admin user/dealer-tier sorting.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled after the admin sorting update.
- Rendered the admin users page through `AdminUserController@index()` with `sort=dealer_tier&direction=desc` and `sort=user&direction=asc`; both rendered successfully.
- Ran `php -l app/Http/Controllers/Admin/AdminUserController.php` and `php -l resources/views/admin/users/index.blade.php`; no syntax errors after adding admin registration-date sorting.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled after adding the `Inregistrat` sortable column.
- Rendered the admin users page through `AdminUserController@index()` with `sort=registered&direction=desc` and `sort=registered&direction=asc`; both rendered successfully.
- Ran `git diff --check`; passed after the dealer tier changes.
- Ran `php -l resources/views/services/show.blade.php`; no syntax errors after changing the listing detail dealer seller card to name / optional founding banner / registration rows.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the listing detail seller-card founding banner and 72px avatar update.
- Confirmed local `ProcessServiceImages` jobs were on `default` with `attempts = 0` while the manually started local worker listened only to `services`; moving those local jobs to `services` allowed the running local services worker to consume them. `jobs` and `failed_jobs` were empty afterward.
- Confirmed service `132` received three processed WebP images in `services.images` and matching files under `storage/app/public/services` after the worker processed the moved jobs.
- Ran `php -l config/queue.php` and `php artisan config:clear`; queue config is valid and local config cache was cleared after the queue investigation.
- Ran `php artisan route:list --name=profile.dealerLogo`; confirmed the `POST profile/dealer-logo` and `DELETE profile/dealer-logo` routes point to `ProfileController@uploadDealerLogo` and `ProfileController@deleteDealerLogo`.
- Verified local image support reports `Intervention\Image\ImageManager`, GD, and WebP available.
- Simulated a controller-level dealer logo upload/delete for the first local dealer account without an existing logo: upload returned 200, stored a `dealers/15/*-logo-*.webp` file, the file existed after upload, delete returned 200, `dealer_logo` returned to null, and the file was removed. Repeated after adding the realpath delete guard with the same result.
- Simulated a controller-level dealer-to-individual downgrade using a temporary dealer user with one logo file and one gallery file: without `dealer_downgrade_confirmed`, the response was 422 and both files/data remained; with confirmation, the response was 200, `user_type` became `individual`, dealer fields/gallery/logo were cleared, and both media files were deleted.
- Ran a pre-live audit of the dealer logo/tier changes: reviewed changed files/routes, confirmed the listing publication flow and `ProcessServiceImages` job dispatch were not modified, and confirmed `config/queue.php` only changed explanatory comments.
- Ran `php artisan route:list --name=profile.dealerLogo` and `php artisan route:list --name=admin.users.dealer-tier`; confirmed the two profile logo routes and the admin dealer tier route are registered.
- Ran `php artisan view:cache`, `php artisan route:cache`, and `php artisan config:cache`, then cleared each cache; all cache builds completed successfully.
- Ran `php artisan migrate:status` for `2026_07_03_120000_add_dealer_logo_to_users_table` and `2026_07_03_130000_add_dealer_tier_to_users_table`; both are applied locally.
- Ran `php artisan test`; unit tests passed, but feature tests could not run in the local PHP environment because `pdo_sqlite` is missing (`could not find driver`). Ran `php artisan test --testsuite=Unit`; 4 tests / 12 assertions passed.
- Ran rollback-wrapped controller-level PHP bootstrap checks: dealer logo upload returned 200, saved a WebP path, and created the file; dealer downgrade without confirmation returned 422 and preserved data/files, then confirmed downgrade returned 200, cleared dealer fields/logo/gallery/tier, and removed media files; admin dealer tier update changed a temp dealer from `standard` to `premium`.

- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after the mobile `Localitate` dropdown scroll-space update and saved-search font adjustment.
- Ran `npm run build`; Vite production build completed after the mobile `Localitate` dropdown scroll-space update and saved-search font adjustment.
- Ran `git diff --check`; passed after the mobile `Localitate` dropdown scroll-space update and saved-search font adjustment.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the mobile `Localitate` dropdown scroll-space update and saved-search font adjustment.
- Attempted to verify the mobile `Judet` -> `Localitate` interaction in the in-app browser at `http://iamasina.test/anunturi-auto-de-vanzare` with a 390x430 viewport, but that local browser session was loading Vite assets without exposing the app JS globals (`window.iaCombobox` / listing helpers were absent), so it was not treated as a valid browser verification.

- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after tightening the mobile listing sort combobox.
- Ran `npm run build`; Vite production build completed after tightening the mobile listing sort combobox.
- Ran `git diff --check`; passed after tightening the mobile listing sort combobox.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after tightening the mobile listing sort combobox.

- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after resizing the mobile listing sticky action bar.
- Ran `npm run build`; Vite production build completed after resizing the mobile listing sticky action bar.
- Ran `git diff --check`; passed after resizing the mobile listing sticky action bar.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after resizing the mobile listing sticky action bar.
- Reviewed the `resources/views/services/listing.blade.php` diff to confirm the mobile grid changed from `0.58fr 0.68fr 0.9fr 1.28fr` to `0.58fr 0.68fr 0.74fr 1.5fr`, giving the sort control more width while keeping `Salvează căutarea` stacked on two lines.

- Ran `npm run build`; Vite production build completed after allowing desktop filter dropdowns to overflow the filters card.
- Ran `git diff --check`; passed after allowing desktop filter dropdowns to overflow the filters card.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after allowing desktop filter dropdowns to overflow the filters card.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after allowing desktop filter dropdowns to overflow the filters card.
- Verified locally with Playwright using system Chrome at `http://iamasina.test/anunturi-auto-de-vanzare`: desktop `#filters-panel` and `.filters-panel-sheet` computed `overflow: visible`; at 1280x760, the `Judet` dropdown height was limited by the viewport rather than the filters card; at 1280x980, the same dropdown extended below the filters card (`extendsBelowSheet = true`) while remaining open downward.

- Ran `npm run build`; Vite production build completed after changing mobile filter dropdowns to always open downward with auto-scroll.
- Ran `git diff --check`; passed after changing mobile filter dropdowns to always open downward with auto-scroll.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after changing mobile filter dropdowns to always open downward with auto-scroll.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after changing mobile filter dropdowns to always open downward with auto-scroll.
- Verified locally with Playwright using system Chrome at `http://iamasina.test/anunturi-auto-de-vanzare`: on a 390x430 mobile viewport, opening `Marca` did not auto-scroll the sheet (`scrollTop = 0`), opened downward, and kept form height 542px; opening `Judet` after scrolling it into view auto-scrolled the sheet to `scrollTop = 276`, opened downward, kept the dropdown within the sheet bottom, and kept form height 542px; on a 390x844 viewport, `Judet` auto-scrolled to `scrollTop = 145`, opened downward, and kept form height 542px. In all checked cases the first option was not auto-highlighted (`activeCount = 0`).

- Ran `npm run build`; Vite production build completed after the combobox active-option fix.
- Ran `git diff --check`; passed after the combobox active-option fix.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after the combobox active-option fix.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the combobox active-option fix.
- Verified locally with Playwright using system Chrome at `http://iamasina.test/anunturi-auto-de-vanzare`: opening the brand combobox on desktop produced `activeCount = 0`, `selectedCount = 0`, and no `aria-activedescendant`; pressing `ArrowDown` then produced `activeCount = 1` and `aria-activedescendant = brand_search-option-0`; opening the brand combobox in the mobile filters panel also produced `activeCount = 0`.

- Ran `npm run build`; Vite production build completed after the overlay dropdown JS/CSS change.
- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after the overlay dropdown change.
- Ran `git diff --check`; passed after the overlay dropdown change.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the overlay dropdown change.
- Verified locally with Playwright using system Chrome at `http://iamasina.test/anunturi-auto-de-vanzare`: desktop brand dropdown stayed `position: absolute`, opened down, and `#search-form` height stayed 590px before/after opening; mobile brand dropdown at 390x844 stayed absolute, opened down, and form height stayed 542px; mobile county dropdown at 390x844 opened upward with form height still 542px; mobile county dropdown at 390x430 opened upward within the sheet with max-height about 232px and form height still 542px.

- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after removing the desktop listing header count.
- Ran `git diff --check`; passed after removing the desktop listing header count.
- Ran `rg -n "anunțuri disponibile|anunturi disponibile|disponibile" resources/views/services/listing.blade.php`; no matches remain for the removed desktop count text.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after removing the desktop listing header count.

- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after setting the default sort label and sort cursor styling.
- Ran `git diff --check`; passed after setting the default sort label and sort cursor styling.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after setting the default sort label and sort cursor styling.

- Ran `php -l resources/views/services/listing.blade.php`; no syntax errors after the sort auto-apply change.
- Ran `git diff --check`; passed after the sort auto-apply change.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the sort auto-apply change.

- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the dealer portfolio SEO/share metadata update.
- Ran `git diff --check`; passed after the dealer portfolio SEO/share metadata update.
- Ran `php -l app/Http/Controllers/ServiceController.php`; no syntax errors after wiring dealer portfolio display location to the existing locality/county tables.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the dealer portfolio location display update.
- Ran `git diff --check`; passed after the dealer portfolio location display update.

- Identified the GA4 publication flow before implementation: the create Blade form posts to `services.store` (`POST /anunturi-auto-de-vanzare/adauga-anunt`), which is handled by `ServiceController::store()`. Validation happens before the service model is created, the service is saved before redirect, and edit/update, renew/reactivation, delete/deactivate, admin, import, and promotion flows are separate and were not wired to the GA4 event.
- Verified the GA4 event is server-gated by the new `ga4_listing_published_event` flash, set only after `$service->save()` succeeds, and is client-gated by Analytics consent plus `typeof window.gtag === 'function'`. The layout script uses an in-page `sent` guard and Laravel flash semantics so it does not fire on button click, validation failure, failed save, duplicate consent events, or refresh of the confirmation page.
- Ran `php -l app/Http/Controllers/ServiceController.php` and `php -l resources/views/layouts/app.blade.php`; no syntax errors after the GA4 `listing_published` event integration.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the GA4 `listing_published` event integration.
- Ran `git diff --check`; passed after the GA4 `listing_published` event integration.

- Identified publication flows before implementation: the public create form posts only to `services.store` (`POST /anunturi-auto-de-vanzare/adauga-anunt`), which covers authenticated users, guests without accounts, guests creating an account, individual owners, and dealer accounts through the same path. Edit/update (`services.update`), renew/reactualizare (`services.renew`), delete/deactivate, and admin service routes are separate and were not wired to the event.
- Rendered `layouts.app` with a flashed `meta_listing_published_event` and confirmed the HTML contains `ListingPublished`, `vehicle_listing`, `eventID`, and the UUID only; no email/user/listing internal id literals were present.
- Verified `ListingPublished` behavior in an in-memory browser harness using the real cookie-consent and layout scripts with a fake no-op Meta script: saved Marketing consent produced exactly one PageView and one `trackCustom ListingPublished` with the expected `eventID`; no Marketing consent produced no event until Marketing was accepted on the same page; explicit Marketing denial canceled the pending event; repeated `iaauto:meta-pixel-ready` signals did not duplicate the event.
- Ran `php -l app/Http/Controllers/ServiceController.php`, `php -l resources/views/components/cookie-consent.blade.php`, and `php -l resources/views/layouts/app.blade.php`; no syntax errors after the `ListingPublished` event integration.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the `ListingPublished` event integration.
- Ran `php artisan test --filter=ServicePublishedConfirmationTest`; passed (1 test / 7 assertions).
- Ran `git diff --check`; passed after the `ListingPublished` event integration.

- Ran `php -l config/services.php` and `php -l resources/views/components/cookie-consent.blade.php`; no syntax errors after the Meta Pixel integration.
- Ran `git diff --check`; passed.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared.
- Verified `http://auto.test/politica-cookies` rendered `const metaPixelId = null` while local `META_PIXEL_ID` was unset; the Meta URL exists only inside the gated JS function, and the rendered page had no `<noscript>` block.
- Started a temporary Laravel server on `127.0.0.1:8011` with only the process-level `META_PIXEL_ID=1348462176729702`; before consent, browser inspection showed `fbq` undefined, helper not ready, banner visible, and no `connect.facebook.net` / `facebook.com` scripts, elements, or performance resources.
- Verified consent transitions in an in-memory browser harness using the real cookie-consent script and a fake no-op Meta script to avoid sending test data: no-consent helper returned false with no `fbq`; accepting Marketing without refresh produced exactly one script load, one `consent grant`, one `autoConfig false`, one `init`, and one `PageView`; helper events queued only after consent; withdrawing Marketing queued exactly one `consent revoke` and disabled helper sends; repeated reaccepts did not duplicate script/init/PageView; saved Marketing consent sent one PageView on page load; saved denied consent loaded no Pixel.

- Ran `php -l resources/views/services/show.blade.php`; no syntax errors after applying the Facebook Share Dialog behavior to the show page.
- Ran `git diff --check`; passed after the show-page Facebook share update.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the show-page Facebook share update.

- Ran `php -l resources/views/account/index.blade.php`; no syntax errors after the Facebook mobile-share update.
- Ran `git diff --check`; passed after the Facebook mobile-share update.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the Facebook mobile-share update.

- Verified in the in-app browser at 1884x900 for `http://auto.test/anunturi-auto-de-vanzare/bmw/seria-3/arad/chisineu-cris`: the desktop breadcrumb spans the listing page width, `Salveaza` and `Sortare` start below it, there is no overlap, `scrollbar-width` computes to `none`, and the page has no horizontal overflow.
- Verified the same listing path at 390x844 mobile: exactly one breadcrumb is visible, the internal scrollbar is hidden, and it does not overlap `Filtre` or `Salveaza`.
- Verified `http://auto.test/anunturi-auto-de-vanzare/dacia/logan/buzau/nehoiu/dadfsadas-281` in the in-app browser at 1884x900 and 390x844: the visual breadcrumb stops at `Nehoiu`, the current ad title is absent from the breadcrumb, all six visible segments are links, `Inapoi` stays on the next row, and no horizontal page overflow appears.
- Ran `php -l resources/views/components/breadcrumbs.blade.php`, `php -l resources/views/services/show.blade.php`, and `php -l resources/views/services/listing.blade.php`; no syntax errors after the latest breadcrumb/layout update.
- Ran `git diff --check`; passed with the existing line-ending warning for `resources/views/services/dealer-portfolio.blade.php`.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the latest breadcrumb/layout update.
- Ran Vite build through the bundled Codex Node runtime after moving the global `no-scrollbar` utility; build completed with only existing baseline-browser-mapping/Browserslist age warnings.

- Verified in the in-app browser after tightening breadcrumb placement: service show breadcrumbs measure 10px below `#main-nav` on desktop and mobile, the `Înapoi` button is on the next row and not sharing a row with the breadcrumb, listing breadcrumbs measure 10px below the header on desktop and mobile, dealer breadcrumbs measure 10px below the header on desktop and mobile, and no page overflow or console errors were reported.
- Ran `php -l resources/views/layouts/app.blade.php`, `php -l resources/views/services/show.blade.php`, `php -l resources/views/services/listing.blade.php`, `php -l resources/views/services/dealer-portfolio.blade.php`, and `php -l resources/views/components/breadcrumbs.blade.php`; no syntax errors after the breadcrumb placement update.
- Ran `git diff --check`; passed with the existing line-ending warning for `resources/views/services/dealer-portfolio.blade.php`.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the breadcrumb placement update.
- Ran Vite build through the bundled Codex Node runtime after the breadcrumb spacing classes changed; build completed with only existing baseline-browser-mapping/Browserslist age warnings.
- Ran `php -l resources/views/components/breadcrumbs.blade.php`, `php -l resources/views/services/show.blade.php`, `php -l resources/views/services/listing.blade.php`, and `php -l resources/views/services/dealer-portfolio.blade.php`; no syntax errors after adding the shared breadcrumb component and wiring it into listing/show/dealer pages.
- Ran `git diff --check`; passed with the existing line-ending warning for `resources/views/services/dealer-portfolio.blade.php`.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the breadcrumb update.
- Ran Vite build through the bundled Codex Node runtime after adding the new breadcrumb classes; build completed with only existing baseline-browser-mapping/Browserslist age warnings.
- Verified in the in-app browser at desktop 1280x720 and mobile 390x844 for `/anunturi-auto-de-vanzare`, the local Audi A3 show page, and `/anunturi-auto-de-vanzare/parc-auto/buzau/buzau/ayy-auto-park`: each page renders exactly one visible breadcrumb, every visible breadcrumb segment is an anchor, the show-page mobile breadcrumb scrolls horizontally instead of causing page overflow, dealer `Buzău` links to `/anunturi-auto-de-vanzare/buzau?seller_type=dealer`, and no console errors were reported.
- Ran `php -l resources/views/services/dealer-portfolio.blade.php`; no syntax errors after the dealer CTA and stock-heading update.
- Ran `git diff --check`; passed with the existing line-ending warning for `resources/views/services/dealer-portfolio.blade.php`.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the dealer CTA and stock-heading update.
- Ran Vite build through the bundled Codex Node runtime after the dealer CTA class changes; build completed with only existing baseline-browser-mapping/Browserslist age warnings.
- Verified `http://auto.test/anunturi-auto-de-vanzare/parc-auto/buzau/buzau/ayy-auto-park` in the in-app browser on desktop: `Vezi anunțurile` rendered as white/outlined secondary (`rgb(255,255,255)` background, red border/text), `Sună acum` rendered as the red primary action, the stock heading rendered as `3 anunțuri auto oferite de AYY AUTO PARK`, old `Stocul parcului auto` / `3 rezultate` text was absent, and there were no console errors.
- Verified the same dealer page at 390x844 mobile viewport: the two hero CTAs fit side-by-side without horizontal overflow and the stock heading wrapped cleanly.
- Checked the first local dealer record: it only has `phone` populated while `phone_2` and `phone_3` are null; the Blade still collects all three fields and renders each non-empty phone in the hero and location sections.
- Ran `php -l resources/views/services/dealer-portfolio.blade.php` and `php -l resources/views/account/index.blade.php`; no syntax errors after simplifying the dealer about section and adding the account dealer-page link.
- Ran `git diff --check`; passed with the existing line-ending warning for `resources/views/services/dealer-portfolio.blade.php`.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the dealer about/account tab update.
- Ran Vite build through the bundled Codex Node runtime after adding the account tab grid variant; build completed with only existing baseline-browser-mapping/Browserslist age warnings.
- Verified `http://auto.test/anunturi-auto-de-vanzare/parc-auto/buzau/buzau/ayy-auto-park` in the in-app browser: `#dealer-about` exists, the heading renders as `Despre AYY AUTO PARK`, the previous trust-card labels are absent, and the section contains no SVG/card icons.
- Rendered `account.index` in CLI after logging in the first local dealer user; the HTML included `Pagina parcului`, `grid-cols-6`, and a blank-target marker for the dealer public URL. Direct browser verification of `/contul-meu` was not possible because the in-app browser session was logged out and redirected to `/login`.
- Ran `php -l resources/views/services/show.blade.php`; no syntax errors after the show-page message/dealer/safety color update.
- Ran `git diff --check`; passed after the show-page message/dealer/safety color update.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the show-page message/dealer/safety color update.
- Ran Vite build through the bundled Codex Node runtime after adding the new Tailwind arbitrary colors; build completed with only existing baseline-browser-mapping/Browserslist age warnings.
- Verified `http://auto.test/anunturi-auto-de-vanzare/audi/a3/buzau/buzau/audi-a-3-posibilitate-de-9` in the in-app browser at 390x844 mobile and 1280x720 desktop: message action computed to white `rgb(255,255,255)` / border `rgb(215,222,231)` / text `rgb(23,32,51)`, dealer portfolio link computed to `rgb(248,250,252)` with the same border/text and one chevron, safety card computed to `rgb(255,248,232)` / border `rgb(241,211,138)` with amber title/icon and brown body text, and the phone CTA stayed red `rgb(224,62,45)`; no browser console errors were reported.
- Ran `php -l resources/views/services/dealer-portfolio.blade.php`, `php -l resources/views/services/partials/service_cards_horizontal.blade.php`, `php -l resources/views/services/partials/service_cards_home.blade.php`, `php -l resources/views/services/partials/service_cards.blade.php`, `php -l resources/views/services/index.blade.php`, and `php -l resources/views/services/show.blade.php`; no syntax errors after making the dealer main photo clickable, switching dealer filters to comboboxes, and centering card/show image crops.
- Ran `git diff --check`; passed after the dealer combobox/gallery and image-centering updates.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the updates.
- Ran Vite build through the bundled Codex Node runtime; build completed with only existing Browserslist/baseline age warnings.
- Verified `http://auto.test/anunturi-auto-de-vanzare/parc-auto/buzau/buzau/ayy-auto-park` in the in-app browser: the dealer main gallery image opens the gallery modal when clicked, the modal closes cleanly, the brand/model filters render as shared comboboxes, the available brand list contains the dealer stock brand (`Audi`), selecting it enables the model combobox with the available model (`A3`), clearing the brand disables and empties the model combobox with the `Alege marca` placeholder, and the dealer gallery image computes to `object-fit: cover` / `object-position: 50% 50%`.
- Verified `http://auto.test/`, `http://auto.test/anunturi-auto-de-vanzare`, and the local Audi A3 show page in the in-app browser: visible Home cards, Listing cards, and Show gallery images compute to `object-fit: cover` and `object-position: 50% 50%`; no console errors were reported.
- Ran `rg --pcre2 -n "object-cover(?! object-center)" resources/views/services -g "*.blade.php"`; the remaining public-page match is the Home hero image, while the other remaining matches are create/edit image previews, not Home/Listing/Show listing cards.
- Ran `php -l resources/views/services/dealer-portfolio.blade.php`; no syntax errors after centering the dealer gallery crop.
- Ran `git diff --check`; passed after centering the dealer gallery crop.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after centering the dealer gallery crop.
- Ran Vite build through the bundled Codex Node runtime after adding `object-center` to the dealer gallery images; build completed with only existing Browserslist/baseline age warnings.
- Verified `http://auto.test/anunturi-auto-de-vanzare/parc-auto/buzau/buzau/ayy-auto-park` in the in-app browser: the main dealer gallery image and all visible thumbnails computed to `object-fit: cover` and `object-position: 50% 50%`, with no horizontal overflow and no console errors.
- Ran `php -l resources/views/services/dealer-portfolio.blade.php`; no syntax errors after compacting the dealer hero.
- Ran `git diff --check`; passed after compacting the dealer hero.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after compacting the dealer hero.
- Ran Vite build through the bundled Codex Node runtime after adding the compact dealer gallery height/grid classes; build completed with only existing Browserslist/baseline age warnings.
- Verified `http://auto.test/anunturi-auto-de-vanzare/parc-auto/buzau/buzau/ayy-auto-park` in the in-app browser on desktop: hero, info card, and gallery measured 320px tall, the gallery image and thumbnail column were constrained inside that height, the stock section moved up to about 468px from the viewport top, and there were no console errors.
- Verified the same dealer page at 390x844 mobile viewport after the compacting change: layout stayed stacked, no horizontal overflow appeared, gallery/card behavior remained intact, and there were no console errors.
- Ran `php -l resources/views/services/dealer-portfolio.blade.php`; no syntax errors after the dealer page restyle.
- Ran `git diff --check`; passed after the dealer page restyle.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the dealer page restyle.
- Ran Vite build through the bundled Codex Node runtime after adding the new dealer portfolio Tailwind classes; build completed with only existing Browserslist/baseline age warnings.
- Verified `http://auto.test/anunturi-auto-de-vanzare/parc-auto/buzau/buzau/ayy-auto-park` in the in-app browser on desktop: the page rendered the new dealer info/gallery hero, breadcrumb, stock tabs, two listing cards, about section, and location section with no console errors.
- Verified the same dealer page at 390x844 mobile viewport: hero sections stacked correctly, there was no horizontal overflow, the mobile stock button text fit, the gallery modal opened with an image/counter and closed cleanly, and the filter selects were populated from the dealer's available stock.
- Ran `rg` for `mainHeader`, `header-scrolled`, and `logo-main` across source files excluding build/vendor/node_modules/storage compiled views; no source references remained after removing the unused legacy header logic.
- Ran `php -l resources/views/layouts/app.blade.php`; no syntax errors after making mobile header auto-hide global.
- Ran `git diff --check`; passed after the global mobile header auto-hide cleanup.
- Ran Vite build through the bundled Codex Node runtime after removing the unused header JS/CSS; build completed with only existing Browserslist/baseline age warnings.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the global mobile header auto-hide cleanup.
- Verified in the in-app browser at 390x844 using real scroll gestures on a non-listing show page (`/anunturi-auto-de-vanzare/audi/a3/buzau/buzau/audi-a-3-posibilitate-de-9`): scrolling down hid `#main-nav` to `translateY(-56px)` with `data-mobile-hidden=true`, and scrolling back up returned it to `translateY(0)` with `data-mobile-hidden=false`.
- Verified in the in-app browser at 390x844 on the create listing page (`/anunturi-auto-de-vanzare/adauga-anunt`), which has no `#listing-actions-bar`: scrolling down hid `#main-nav` to `translateY(-56px)` with `data-mobile-hidden=true`, confirming the behavior is no longer limited to Home/Listing.
- Ran `php -l resources/views/services/show.blade.php`; no syntax errors after the show-page mobile/dealer update.
- Ran `git diff --check`; passed after the show-page mobile/dealer update.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the show-page mobile/dealer update.
- Verified `http://auto.test/anunturi-auto-de-vanzare/audi/a3/buzau/buzau/audi-a-3-posibilitate-de-9` in the in-app browser at 390x844: `.important-details-grid` computed as two columns (`148.8px 148.8px`), the first two important-detail cards shared the same row, one visible `Vezi portofoliu dealer` link appeared before the safety warning with a 24px gap, old `Vezi portofoliul parcului` text was absent, and exact `Verificat` text was absent from the seller card.
- Ran `php -l resources/views/services/index.blade.php` and `php -l resources/views/services/listing.blade.php`; no syntax errors after the seller tab dark-mode update.
- Ran `git diff --check`; passed after the seller tab dark-mode update.
- Ran Vite build through the bundled Codex Node runtime after adding the seller tab dark-mode classes; build completed with only existing Browserslist/baseline age warnings.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the seller tab dark-mode update.
- Verified `http://auto.test/` and `http://auto.test/anunturi-auto-de-vanzare` in the in-app browser while `prefers-color-scheme: dark` was true: active seller tabs rendered with dark red background `rgb(42, 16, 19)` and red text `rgb(252, 165, 165)`, inactive tabs stayed transparent/gray, and clicking `Parcuri` moved the active dark styling while keeping `seller_type=dealer`.
- Ran `php -l resources/views/layouts/app.blade.php`; no syntax errors after removing the global overscroll rule.
- Ran `rg` for `overscroll-behavior`, filter `:has(...)` scroll-lock selectors, and `document.body.style.overflow`; confirmed only the mobile filters popup keeps `html/body` overflow lock and `.filters-panel-sheet` keeps `overscroll-behavior: contain`.
- Ran `git diff --check`; passed after the global overscroll removal.
- Ran `php artisan view:cache` and `php artisan view:clear`; Blade templates compiled and cache was cleared after the global overscroll removal.
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

- Dealer logo uploads use the existing public dealer media path pattern under `public/storage/dealers/{user_id}` and the same public URL base as dealer gallery images. Production must run the new migration before dealer logo uploads are available. The account media upload auto-save is client-side JS only and uses the existing `/profile/ajax-update` route before `/profile/dealer-logo` or `/profile/dealer-gallery` when the saved `user_type` is still `individual`; this implicit save intentionally does not submit the password field. No new `.env` keys or secrets were added.
- Dealer tiers use the new `users.dealer_tier` column and application-validated values `standard`, `founding`, and `premium`; production must run `2026_07_03_130000_add_dealer_tier_to_users_table` before assigning tiers from admin. Newly saved users normalize to `standard` when missing/invalid, and switching a dealer account back to individual resets the tier to `standard`. No new `.env` keys or secrets were added.
- Service listing image processing leaves `SERVICE_IMAGES_QUEUE` env-controlled. If the env key is unset, `ProcessServiceImages` uses the connection default queue. On live as checked on 2026-07-03, `queue.service_images_queue = null`, `queue.connections.database.queue = default`, and the iaAuto Supervisor worker runs `queue:work --sleep=1 --tries=3`, so image jobs should stay on `default`. If an environment uses a worker with `--queue=services`, set `SERVICE_IMAGES_QUEUE=services` for that environment and clear/rebuild config cache before restarting the worker.

- Desktop filter dropdown overflow is CSS/JS-only and uses existing combobox/listing filter markup. No routes, controllers, database changes, environment keys, or secrets were added.

- Mobile filter dropdown auto-scroll is client-side JS/CSS only and depends on the existing fixed mobile filters panel plus `.filters-panel-sheet` scroll container. Browser verification used local Laragon host `http://iamasina.test`; no routes, controllers, database changes, environment keys, or secrets were added.

- Combobox active-option refinement is client-side JS only and uses the existing `is-active` keyboard navigation and `is-selected` selected-value states. No routes, controllers, database changes, environment keys, or secrets were added.

- Listing filter dropdown overlay behavior depends only on the existing shared combobox JS/CSS and the existing mobile filters sheet. Browser verification used local Laragon host `http://iamasina.test`; no routes, controllers, database changes, environment keys, or secrets were added.

- Removing the desktop listing header count is a Blade-only presentation change. No routes, controllers, database changes, environment keys, or secrets were added.

- Listing sort default/cursor refinement is Blade/CSS-only and depends on the existing `newest` sort value already handled by the controller. No routes, controllers, database changes, environment keys, or secrets were added.

- Listing sort auto-apply uses the existing client-side `applyListingFilters()` / AJAX listing refresh path and existing `sort` query parameter. No routes, controllers, database changes, environment keys, or secrets were added.

- Dealer portfolio SEO/share metadata uses only existing dealer account fields (`company_name`, `name`, `city`, `county`, `dealer_gallery`) and the active listing collection/count already passed to the view; no new environment keys, secrets, routes, migrations, or tables were added.
- Dealer portfolio location display now depends on the existing `users.locality_id` / `users.county_id` values matching records in `localities` / `counties`; when those IDs are absent, the page still falls back to the legacy dealer `city` / `county` strings. No data migration or environment key was added.

- GA4 remains disabled unless `GOOGLE_ANALYTICS_ID` is configured and the user has granted Analytics cookie consent. No `.env` values or secrets were read or committed.

- Meta Pixel is disabled unless `META_PIXEL_ID` is configured. Keep `META_PIXEL_ID` unset in local/dev unless intentionally testing, and refresh Laravel config after changing it (`php artisan optimize:clear` / `php artisan config:cache` as appropriate for the environment). No `.env` values or secrets were read or committed.

- Facebook sharing depends on `FACEBOOK_APP_ID` being configured through `config/services.php`. Meta does not provide a reliable supported browser-to-Facebook-app deep link that opens a prefilled composer, and platform policy blocks pre-filling the user's message text; this implementation sends the ad URL/title to the Share Dialog and leaves the user's comment empty. No `.env` values or secrets were read or committed.

- Local PowerShell did not expose `npm` in PATH during this update, so Vite verification used the bundled Codex Node executable against the project's local `node_modules/vite/bin/vite.js`; no environment keys or secrets are involved.
- Breadcrumbs use only existing public routes and view data; dealer county links prefer the county slug path (for example `/anunturi-auto-de-vanzare/buzau?seller_type=dealer`) so dealer accounts without `county_id` still get a clickable county breadcrumb. No new environment keys, controllers, migrations, or tables were added.
- The account dealer-page link depends on the existing `User::dealer_public_url` accessor and only appears for users whose `user_type` is `dealer` and whose company data yields a public URL; no new routes, controllers, tables, or environment keys were added.
- Show-page color verification used the local `auto.test` host and an existing local dealer listing (`id=9`) while logged out, so the desktop message action rendered as `Autentifică-te pentru mesaj` and the mobile action rendered as `Mesaj`; no environment keys or secrets are required for this update.
- Dealer filter comboboxes still use the existing dealer stock data passed to the view; no routes, controllers, migrations, or environment keys were changed for this update.
- Dealer page restyle uses only data already collected on dealer accounts (`company_name`, phones, county/city/address, description, gallery) and service stock filters; because there is no dedicated dealer schedule field yet, the page shows a neutral "contactează dealerul" program message.
- Public mobile header auto-hide is now driven only by the shared `#main-nav` logic in `resources/views/layouts/app.blade.php`; the older `mainHeader`/`header-scrolled` source code path was confirmed unused before removal.
- Show-page browser verification used the local `auto.test` host and an existing local dealer listing (`id=9`) that has a dealer portfolio URL and two important details; no new environment keys are required.
- Public dark theme is still driven by Tailwind `darkMode: 'media'` / `prefers-color-scheme`; no manual theme toggle or `.dark` root class was added for the seller tab update.
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

- After deploy, perform a logged-in browser pass on `/contul-meu?tab=profil`: from an individual account select `Parc auto`, fill required dealer details, upload a logo/gallery image before pressing the bottom save button, and confirm the upload first saves the dealer profile. Also test an existing dealer account: upload a logo, confirm the account preview updates, confirm the public dealer page and a dealer listing detail page show the logo, then delete it and confirm the fallback initial returns.
- After deploy, assign `Fondator` or `Premium` to one test dealer from `panou-secret/users`, confirm the admin selector persists, confirm the public dealer portfolio and a listing detail dealer card show the special badge, then reset the test account to `Standard` if needed.

- No new known follow-up from allowing desktop filter dropdowns to overflow the filters card.

- After deploy, retest on a real iPhone Safari device that lower mobile filter fields auto-scroll upward and their dropdowns open downward without being clipped.

- No new known follow-up from the combobox active-option fix.

- No new known follow-up from the listing filter dropdown overlay change; a real-device mobile pass remains useful after deploy, especially for iOS Safari keyboard/viewport behavior on lower filter fields.

- No new known follow-up from removing the desktop listing header count.

- No new known follow-up from the listing sort default/cursor refinement beyond the existing quick browser pass on the listing page after deploy.

- No new known follow-up from the listing sort auto-apply change; a quick browser pass on the live listing page remains useful after deploy.

- After deployment with `GOOGLE_ANALYTICS_ID` configured, verify in GA4 DebugView/Realtime that `listing_published` appears once after a successful public listing submission and only after accepting Analytics cookies.

- After deployment with `META_PIXEL_ID` configured, verify in Meta Events Manager Test Events that `ListingPublished` appears once after a successful public listing submission and only after accepting Marketing; then create the Meta custom conversion separately after the event is visible.

- After deployment with `META_PIXEL_ID` configured, verify in Meta Events Manager Test Events that PageView appears only after accepting Marketing, and verify in DevTools Network that no `connect.facebook.net` or `facebook.com` request is made before Marketing consent.

- Verify the account-page Facebook button on real Android and iOS devices after deploy: desktop should keep the current web popup, mobile should open the Facebook Share Dialog in a new tab so the iaAuto account page is not replaced. The user's own Facebook post text cannot be prefilled by policy.

- No new known follow-up from the shared breadcrumb update; a real-device visual pass remains useful after deploy, especially for long show-page breadcrumbs on narrow phones.
- No new known follow-up from the dealer about/account tab update; a logged-in real-browser pass remains useful after deploy for the `Pagina parcului` tab.
- No new known follow-up from the show-page message/dealer/safety color update; a real-device visual pass remains useful after deploy.
- No new known follow-up from the dealer combobox/gallery click and image-centering update; a real-device visual pass remains useful after deploy.
- No new known follow-up from the dealer portfolio restyle; a real-device visual pass is still useful after deploy, especially for the gallery and bottom mobile action bar around the cookie banner.
- No new known follow-up from the global mobile header auto-hide cleanup; existing real-device/mobile checks below remain unchanged.
- No new known follow-up from the service show page mobile/dealer update; existing real-device/mobile checks below remain unchanged.
- No new known follow-up from the seller tab dark-mode update; existing real-device/mobile checks below remain unchanged.
- Verify on a real iOS Safari device after deploy that native pull-to-refresh works on normal pages and that the mobile listing filters popup still locks background scroll while open.
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
