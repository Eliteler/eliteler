# GoBiz

GoBiz is a SaaS for creating digital business cards (vCards) and WhatsApp stores. It is a Laravel app with a Blade frontend. The **admin panel** is being moved to **Inertia + React + shadcn/ui**, one module at a time.

---

## Tech stack

| Layer | Details |
|---|---|
| Backend | PHP `^8.2`, Laravel `12.65.0`. The app keeps the Laravel 10-style structure: `app/Http/Kernel.php`, `app/Console/Kernel.php`, and providers listed in `config/app.php`. |
| Database | MySQL (local DB `gobiz`) |
| Admin panel (old) | Blade + Tabler (Bootstrap 5) + jQuery, DataTables (Yajra), ApexCharts |
| Admin panel (new) | Inertia v2 + React 19 + TypeScript + Tailwind v4 + shadcn/ui, recharts, react-hot-toast |
| User panel | Blade, or the **ModernDashboard** plugin (Inertia/React, **precompiled only**, see below) |
| Routing in JS | Ziggy (`@routes` → global `route()`) |
| Local dev | Laravel Herd: `https://gobiz-saas.test` |

---

## Directory map

```
app/
  Http/Controllers/
    Admin/            52 admin controllers (Blade, except DashboardController)
    User/             user panel (vCards, stores)
    Auth/             login/register/verify (laravel/ui)
    Payment/          plan checkout gateways (~16)
    Payment/NFC/      the same gateways, copied for NFC card orders
    Payment/AICredits/ the same gateways, copied again for AI credits
  Http/Middleware/    see "Middleware" below
  Services/
    GoBizCommonService.php      settings/config/plans helpers (legacy)
    Admin/DashboardMetricsService.php   admin dashboard queries
  Support/Admin/      new admin panel infrastructure (permissions, navigation, shared props)
  Console/Commands/PlanExpiryCron.php   `expiry:cron` (scheduled daily)
  helpers.php         formatCurrency(), getConfigData(), …
Modules/TranslationManager/   file-based translation editor (loaded manually; nwidart is not installed)
plugins/              42 plugins (payments, notifications, NFC, referral, AI builder, ModernDashboard, …)
templates/            public website themes (GoBizModern, GoBizOriginal, GoBizRich)
packages/biscolab/    vendored reCAPTCHA package
resources/
  views/admin/        Blade admin (layouts/, includes/, pages/) + app.blade.php (Inertia root)
  views/emails/       mail templates
  js/admin/           NEW React admin panel source
  css/admin.css       NEW admin theme (Tailwind v4 + shadcn tokens)
  lang/*.json         translations (key = English text, used by __() and React t())
routes/web.php        all web routes (admin group ≈ lines 355-708, user group, payments)
```

Each plugin usually has `plugin.json`, `routes.php`, `Controllers/` and `Views/`. They are loaded by `app/Providers/PluginServiceProvider.php`. `routes/web.php` also `require`s every `plugins/*/routes.php` and `templates/*/routes.php`.

---

## Users, roles and permissions

- There is one `web` session guard and one `users` table. Admins and customers log in through the same `/login`.
- **Roles** are stored in `users.role_id`: `1` super admin, `2` customer, `3` administrator, `4` manager. The `roles` table only has rows 1 and 2, so role labels come from code.
- `admin` middleware means `role_id != 2`. `user` middleware means `role_id == 2`.
- **Admin permissions** are a JSON text column, `users.permissions` (keys such as `plans`, `customers`, `marketing`). Each admin route is guarded with `->middleware('user.page.permission:<key>')`.
- Default permission keys are in `App\Support\Admin\AdminPermissions::DEFAULTS`.

## Middleware (`app/Http/Kernel.php`)

- **`web` group:** sessions, CSRF, `SetLocale`, `ToSweetAlert`, `HandleInertiaRequests`, TranslationManager sync.
- **Aliases:**
  - `admin`, `user`
  - `user.page.permission`
  - `demo.mode`: blocks writes in demo mode
  - `twofactor`
  - `Installer`: needs `storage/installed`
  - `scriptsanitizer`, `frame.destroyer`
- **Global:** `ForceCustomDomainRoot` and `DynamicCors`.
- **Locale** is resolved in this order: `?change_language=xx` → `user->lang` → session → `config('app.locale')`. Available languages are in `config/app.php` (`languages`, `flag-icons`). RTL locales: `ar`, `ur`, `he`.

## Settings and config

- `config` table: key/value rows (`currency`, `app_version`, `site_name`, `nfc_order_system`, `referral_system`, `web_template`, …).
  - Old Blade code reads them **by numeric index**, e.g. `$config[76]` is `nfc_order_system`.
  - **New code must use keys:** `DB::table('config')->pluck('config_value', 'config_key')`.
- `settings` table (one row): logo, favicon, site name, custom CSS/JS, analytics.
- Currency formatting: `formatCurrency()` in `app/helpers.php`. React mirrors it in `resources/js/admin/lib/format.ts`.

## Payments

Payment code is spread across many places. It is **out of scope** for the admin migration and is left as it is for now.

- Plan, NFC and AI-credit checkouts each have their own copy of about 16 gateway controllers under `app/Http/Controllers/Payment/**`.
- Recurring and extra gateways live in plugins: `StripeRecurring`, `PaypalRecurring`, `RazorpayRecurring`, `PaddleRecurring`, `CashfreeRecurring`, `MercadoPagoRecurring`, `DodoPaymentsRecurring`, `Coinbase`, `Esewa`, `Moneroo`, `SSLCommerz`, `AmwalPay`.
- Admin gateway settings: `Admin/PaymentMethodController` and `Admin/PaymentSettingController`.

---

## ⚠️ Database rules

- **No database changes**: no migrations, tables, columns or indexes. Some migration files **do not match the real schema**.
- **Always check the live schema** before writing queries:
  ```bash
  php artisan tinker --execute 'foreach (DB::select("SHOW COLUMNS FROM transactions") as $c) echo "$c->Field $c->Type\n";'
  ```
- Known differences from what the migrations suggest:
  - `transactions.transaction_amount` is a **varchar**. Sum it with `(transaction_amount + 0)`, not a DECIMAL cast, which rounds.
  - `transactions.payment_status` is upper case (`SUCCESS`/`PENDING`/`FAILED`/`CANCELLED`). Existing `'Success'` filters match because of the case-insensitive collation.
  - `nfc_card_order_transactions.payment_status` is lower case (`success`). `ai_credits_transactions.payment_status` uses `paid`.
  - `business_cards.status` is a varchar (`'0'`/`'1'`). `card_type` is `vcard`/`store`. `card_status` is `activated`/`inactive`/`deleted`.
  - `transactions.transaction_currency` contains junk values (e.g. `enable_webtools`).

---

## Admin panel migration (Blade → Inertia + React)

### Approach

- **Replace in place.** A migrated module keeps its URLs and returns `Inertia::render(...)`.
- Modules not yet migrated stay Blade. The React sidebar links to them with a normal full page load.
- **Remove Blade once a module is done.** Delete its views, its page-only CSS/JS and any dead controller code in the same change. The shared Blade layout, sidebar and header stay until the last Blade page is gone.
- Only code touched by the migration is reorganised. Everything else (payments, user panel, plugins, emails) stays as it is.

### Status

| Module | Status |
|---|---|
| Dashboard (`admin.dashboard`) | ✅ Migrated and redesigned, Blade removed |
| Themes (`admin.themes`, `admin.edit.theme`) and Business Card Intros (`admin.business-card-intros`) | ✅ Migrated, Blade removed |
| Customers menu: Manage Customers (`admin.customers`, `admin.view.customer`, `admin.create.customer`, `admin.edit.customer`), Digital Business Cards (`admin.created.cards`), Stores (`admin.created.stores`), Custom Domains (`admin.custom.domain.requests`) | ✅ Migrated, Blade removed |
| Plans (`admin.plans`, `admin.add.plan`, `admin.edit.plan`) | ✅ Migrated, Blade removed |
| NFC Sales: NFC cards (`admin.designs`), Orders (`admin.orders`, `admin.order.show`), Activation keys (`admin.key.generations`, `admin.key.greeting.letter`), Payments (`admin.transactions`, `admin.transaction.show`) | ✅ Migrated, Blade removed |
| AI Credit Plans: packs (`admin.ai.credits.plans`), purchases (`admin.ai.credits.transactions`, invoice) | ✅ Migrated, Blade removed |
| Referral Program: referrals (`admin.referrals`), payout requests (`admin.referral.withdrawal.request`), settings (`admin.referral.system.configuration`) | ✅ Migrated, Blade removed |
| Coupons (`admin.coupons`, usage `admin.statistics.coupon`) | ✅ Migrated, Blade removed |
| Email Marketing: campaigns (`admin.marketing.campaigns`), contact groups (`admin.marketing.groups`), Mailgun settings (`admin.marketing.mailgun`); `marketing/customers` now redirects to Manage Customers | ✅ Migrated, Blade removed |
| Web Notifications: send (`admin.marketing.pusher.notification`), Pusher settings (`admin.marketing.pusher`) | ✅ Migrated, Blade removed |
| Transactions (`admin.transactions.index`, invoice `admin.view.invoice`) | ✅ Migrated, Blade removed |
| In-app purchases (`admin.in-app-purchases`, read-only) | ✅ Migrated, Blade removed |
| Payment Gateways (`admin.payment.methods`): one page with an on/off switch and a single settings dialog per gateway (name, logo, API keys, test connection). Old add/edit/configure pages redirect to it; adding custom gateways was dropped. Plugin gateways link to their own settings | ✅ Migrated, Blade removed |
| Plugins (`admin.plugins`): list with search and tabs, install from .zip, remove (not built-in ones). Each plugin's settings page stays in the plugin | ✅ Migrated, Blade removed |
| My Account (`admin.account`): profile (name, email, photo) and password on one page; old edit/change-password URLs redirect. Changing the password now needs the current password and at least 8 characters | ✅ Migrated, Blade removed |
| General Settings (`admin.settings`): branding, landing page and sign-ups, language and region, currency, upload limit, share message on one form. The unused `change-website/google/email/subdomain-settings` routes were left as they are | ✅ Migrated, Blade removed |
| Cron Jobs (`admin.cron.jobs`): reminder days + send hour on one form, test email, run reminders now, copyable server cron commands. Fixed: the expiry job now runs at the chosen hour (`dailyAt`), and stored days like `[10,5,3]` are read correctly | ✅ Migrated, Blade removed |
| Sitemap (`admin.sitemap`): choose content types (with link counts), generate, see current file status. Logic in `SitemapService`: each URL once, only real routes (no 404s), plugin folders checked as `plugins/` | ✅ Migrated, Blade removed |
| Invoice & Tax (`admin.tax.setting`): business details, tax, invoice numbering and invoice/email text on one form with a live preview. The old `update.email.setting` route still works but the page no longer uses it | ✅ Migrated, Blade removed |
| Software Updates (`admin.check`): licence + latest-version check loads after the page (Inertia deferred prop, 20s timeout), re-check without reload, release notes, install with confirmation. The install step itself (`updateCode`) is unchanged | ✅ Migrated, Blade removed |
| Backups (`admin.backups`): create database/file backups, delete (no downloads from the panel; files stay in `storage/app/backups`). Logic in `BackupService`: database dump streams row by row, includes `DROP TABLE IF EXISTS` and backticked columns (verified by importing into a scratch DB). The unused in-panel restore is disabled (it could leave the DB half-empty) | ✅ Migrated, Blade removed |
| Clear App Cache (`admin.clear.cache`): now a POST action from the sidebar (`AdminNavigation::ACTIONS`, nav items carry `method`), runs `optimize:clear` and returns to the current page | ✅ Updated |
| Accent colour preference (General Settings): shadcn Neutral/Blue/Green/Orange/Red/Rose/Violet/Yellow, stored in `localStorage` (`gobiz.admin.accent`), applied before paint in `admin/app.blade.php`; no backend save. Default stays `AdminSharedProps::ACCENT` | ✅ Added |
| Currencies (`admin.currencies`): searchable list (default first), add/edit in one dialog with live symbol preview and "More details" for rarely used fields, delete via POST. Default currency can't be deleted; re-adding a deleted code restores it; codes are unique among active currencies | ✅ Migrated, Blade removed |
| Administrators & Staff (`admin.users`): list + one dialog (role, login, grouped permissions from `AdminPermissions::GROUPS`). Guards: only roles 3/4 and never yourself can be changed; staff can only grant permissions they have; sign-in-as is super admin only; status/delete/sign-in-as are POST. Deactivated staff are now blocked at login (`LoginController::authenticated`) | ✅ Migrated, Blade removed |
| Email Templates (`admin.email.templates.index`): template list + editor on one page (subject, on/off, HTML with click-to-insert placeholders, live desktop/mobile preview using the real email layout CSS and sample values). Content still cleaned with Purifier | ✅ Migrated, Blade removed |
| Translations (`translation-manager.index`, module `Modules/TranslationManager`): language list with % translated, add/import/export/delete; editor with file picker, All/Untranslated, search, 50 per page, save only changed rows, add text, background auto-translate with progress. Security: routes now need `admin` + `translations` permission, `{locale}` restricted to real codes, files limited to the source language's own files (blocked `..` paths that could delete `resources/` or `require` arbitrary PHP), source/default language can't be deleted. JSON writer keeps key order and indentation, so saves change only edited lines | ✅ Migrated, Blade removed |
| Website templates (`admin.web-templates`): template cards (active first, preview image, Pages/Settings links into each template plugin), switch with confirmation, install .zip, remove. Fixed: remove never worked (manager not loaded), activate accepted any name, activate is now POST, built-in GoBizOriginal and the active template can't be removed, unsafe zip paths rejected | ✅ Migrated, Blade removed |
| Blogs (`admin.blogs`, `admin.create.blog`, `admin.edit.blog`) and Blog Categories (`admin.blog.categories`): post list with Published/Drafts tabs + search; full-page editor (TinyMCE via `components/rich-text-editor.tsx`, loaded from cdnjs), auto URL from title, cover, category, tag chips, optional SEO with search preview (blank SEO falls back to title/summary/tags), publish or save as draft. Categories in a dialog with post counts; deleting one moves its posts to Uncategorized (which can't be deleted). Fixed: editing a URL was ignored / could duplicate another post's; slugs unique; actions are POST | ✅ Migrated, Blade removed |
| Plugin pages in React — pilot: AI Business Card Builder (`admin.plugin.aibuilder`). See "Plugin admin pages (React)" below | ✅ Migrated, Blade removed |
| Plugin: AmwalPay (`admin.plugin.amwalpay`) — React page in the plugin (mode, merchant/terminal ID, secret key kept server-side), validation per field | ✅ Migrated, Blade removed |
| Plugin: Cashfree Recurring (`admin.plugin.cashfree-recurring.index`) — React page in the plugin (on/off, checkout name + logo, mode, App ID, secret key and webhook secret kept server-side, test connection, copyable webhook URL); log switches no longer reset on save | ✅ Migrated, Blade removed |
| Plugin: Coinbase (`admin.plugin.coinbase`) — React page (mode, API key + webhook secret kept server-side, test connection) | ✅ Migrated, Blade removed |
| Plugin: Cookie Consent (`admin.plugin.cookie-consent`) — React page (banner on/off → `COOKIE_CONSENT_ENABLED`) | ✅ Migrated, Blade removed |
| Plugin: Directory (`admin.plugin.directory`) — React page (directory on/off, list every card vs opted-in only); update route now demo-mode protected | ✅ Migrated, Blade removed |
| Plugin: Dodo Payments Recurring (`admin.plugin.dodo-payments-recurring`) — React page (on/off, checkout name + logo, mode, API key + webhook secret kept server-side, test connection, copyable webhook URL) | ✅ Migrated, Blade removed |
| Plugin: eSewa (`admin.plugin.esewa`) — React page (test/live, product code, secret key kept server-side, signed test connection) | ✅ Migrated, Blade removed |
| Plugin: Firebase App Notification (`admin.plugin.firebase.app-notification`) — React page (connection on/off + service account upload with project shown, no re-upload needed; composer with searchable customer picker, image, "sent to X of Y") | ✅ Migrated, Blade removed |
| Plugin: Google AdSense (`admin.plugin.google_adsense.settings`) — React page (publisher ID validated; blank = off, stored as `DISABLE_ADSENSE_ONLY` which the site checks) | ✅ Migrated, Blade removed |
| Plugin: Google Analytics (`admin.plugin.google_analytics.settings`) — React page (measurement ID validated, blank = off; fixed wrong "Google OAuth" success message) | ✅ Migrated, Blade removed |
| Plugin: Google OAuth (`admin.plugin.google_oauth.settings`) — React page (on/off, client ID, secret kept server-side, copyable redirect URL; ID/secret required only when turning on) | ✅ Migrated, Blade removed |
| Plugin: Google OAuth Mobile (`admin.plugin.google_oauth.mobile-app.settings`) — React page (app on/off via `GOOGLE_ENABLE_MOBILE`; client ID/secret shared with the website, secret kept server-side) | ✅ Migrated, Blade removed |
| Plugin: Google reCAPTCHA (`admin.plugin.google_recaptcha.settings`) — React page (on/off, site key, secret key kept server-side; keys required only when turning on) | ✅ Migrated, Blade removed |
| Plugin: Maintenance Mode (`admin.plugin.maintenance-mode`) — React page (on/off with confirmation, applies instantly; copyable bypass link while offline; turning on does a full-page redirect to the bypass URL) | ✅ Migrated, Blade removed |
| Plugin: Mercado Pago Recurring (`admin.plugin.mercado-pago-recurring`) — React page (on/off, checkout name + logo, access token kept server-side with test/live shown, test connection, copyable webhook URL) | ✅ Migrated, Blade removed |
| Plugin: Mobile App API (`admin.plugin.mobile-app`) — React page (API on/off, latest app version, API key hidden until "Show key" (Inertia optional prop), new key with confirmation, copyable in-app purchase webhooks); implemented the missing `generateKey` route action | ✅ Migrated, Blade removed |
| Plugin: Modern React Dashboard (`admin.plugin.modern-dashboard`) — React page (switch customers between modern and classic dashboard) | ✅ Migrated, Blade removed |
| Plugin: Moneroo (`admin.plugin.moneroo`) — React page (test/live, secret key kept server-side, test/live key mismatch caught on save and test) | ✅ Migrated, Blade removed |
| Plugin: Order NFC System (`admin.plugin.status.nfc.cards.order`) — React page (ordering on/off, show on website, activation key length 6–64) | ✅ Migrated, Blade removed |
| Plugin: Referral System (`admin.plugin.referral.system`) — React page (program on/off, link to Commission and payouts) | ✅ Migrated, Blade removed |
| Plugin: SubDomain (`admin.plugin.sub-domain`) — React page (subdomains on/off with DNS/SSL requirement explained) | ✅ Migrated, Blade removed |
| Plugin: Tawk.to Chat (`admin.plugin.tawkchat.settings`) — React page (widget ID validated, accepts the full embed link, blank = off) | ✅ Migrated, Blade removed |
| Plugin: Web Tools (`admin.plugin.webtools`) — React page (web tools menu and pages on/off) | ✅ Migrated, Blade removed |
| Plugin: Two-Factor Authentication (`admin.plugin.two-factor-authentication.settings`) — React settings page (on/off, warns when SMTP login is missing since the check is skipped then); the OTP sign-in screen stays Blade | ✅ Migrated, settings Blade removed |
| Plugin: SSLCommerz (`admin.plugin.sslcommerz`) — React page (test/live, store ID, store password kept server-side, test connection) | ✅ Migrated, Blade removed |
| Plugin: Slack Notification (`admin.plugin.slack.settings`) — React page (webhook URL kept server-side, event switches, send test message); update route now demo-mode protected; webhook only required when an event is on | ✅ Migrated, Blade removed |
| Plugin: Telegram Notification (`admin.plugin.telegram.settings`) — React page (bot token kept server-side, chat ID, event switches, send test message); update route now demo-mode protected; token/chat only required when an event is on | ✅ Migrated, Blade removed |
| Plugin: WhatsApp Chat Button (`admin.plugin.whatsapp_chat_button.settings`) — React page (website / customer dashboard switches, number normalised to digits with country code, first message) | ✅ Migrated, Blade removed |
| Plugin: WhatsApp Chat Button Mobile (`admin.plugin.whatsapp_chat_button_mobile.settings`) — React page (app button on/off, number normalised, first message; number only required when on) | ✅ Migrated, Blade removed |
| Plugin: SMTP (`admin.plugin.smtp.settings`) — React page (sender, mailer/host/port/encryption/username, password kept server-side, send test email to yourself, "ask new customers to verify their email" switch for the misnamed `disable_user_email_verification`) | ✅ Migrated, Blade removed |
| Plugin: PayPal Recurring (`admin.plugin.paypal-recurring`) — React page (checkout name/logo, sandbox/live tabs with client ID + secret per mode kept server-side, required for the selected mode when turned on, OAuth test connection, webhook URL + webhook ID; return/cancel URLs set automatically) | ✅ Migrated, Blade removed |
| Plugin: Razorpay Recurring (`admin.plugin.razorpay-recurring`) — React page (checkout name/logo, key ID checked for rzp_test_/rzp_live_, key secret + webhook secret kept server-side and required to turn it on, test connection, webhook URL; customer checkout view stays Blade) | ✅ Migrated, Blade removed |
| Plugin: Stripe Recurring (`admin.plugin.stripe-recurring`) — React page (checkout name/logo, publishable key pk_ + secret key sk_ checked and not mixed test/live, secret + webhook signing secret kept server-side and required to turn it on, test connection, webhook URL) | ✅ Migrated, Blade removed |
| Plugin: Paddle Recurring (`admin.plugin.paddle-recurring`) — React page (checkout name/logo, sandbox/live tabs, seller ID + client-side token checked for test_/live_, API key + webhook secret kept server-side and required to turn it on, test connection incl. missing-permission hint, webhook URL; customer checkout view stays Blade) | ✅ Migrated, Blade removed |
| Plugin: Twilio SMS Notification (`admin.plugin.twilio_sms_notification.settings`) — React page (Account SID checked, auth token kept server-side, phone numbers normalised; 8 message groups each with on/off + Content template SID per recipient, SID required to turn one on, copyable variables, cron note; template routes share one save method and now have demo.mode) | ✅ Migrated, Blade removed |
| Plugin: Twilio WhatsApp Notification (`admin.plugin.twilio_whatsapp_notification.settings`) — React page, same layout as Twilio SMS (credentials with auth token kept server-side, 8 message groups with on/off + approved Content template SID per recipient, variables, cron note; demo.mode on writes) | ✅ Migrated, Blade removed |
| Plugin: MSG91 SMS Notification (`admin.plugin.msg91_sms_notification.settings`) — React page, same layout as Twilio SMS (auth key kept server-side, sender ID, admin number; 8 message groups with on/off + Flow template ID per recipient; fixed first-install seed writing a non-existent `template_sid` column; demo.mode on writes) | ✅ Migrated, Blade removed |
| Plugin: MSG91 WhatsApp Notification (`admin.plugin.msg91_whatsapp_notification.settings`) — React page (auth key kept server-side, WhatsApp number + admin number validated; 8 message groups with on/off, template name + namespace required to turn on, ordered variable picker for {{1}}…{{8}} checked against each group's values; one shared save method; demo.mode on writes; all Blade views + includes removed) | ✅ Migrated, Blade removed |
| Plugin: Zapier (`admin.plugin.zapier`) — React page (on/off switch, Catch Hook URL per event with URL validation, per-event “Send test” via Inertia POST, setup guide dialog with Google Sheets columns, copyable message templates and app mappings; routes now require admin and demo.mode on writes) | ✅ Migrated, Blade removed |
| Plugin: Google Wallet (`admin.plugin.google-wallet`) — React admin page (numeric issuer ID, service account key upload checked to be a real service_account JSON, only needed once; shows which account the saved key belongs to, private key never sent; key written to the exact path the wallet code reads; customer-panel wallet pages stay Blade) | ✅ Migrated, Blade removed |
| Plugin: System Health (`admin.plugin.health-check`) — React dashboard (health check results + “Run checks again”, free-space/SSL/uptime/response-time stats, per-item cache clearing and “Clear everything”/“Free up memory” with confirm, production/debug switches, disk/RAM meters, largest tables, error log download via an admin route + clear (now POST), PHP extensions and storage link; actions are Inertia POSTs with validated targets; route+config item now clears both caches) | ✅ Migrated, Blade removed |
| Web template: GoBiz Modern (`admin.web-template.gobiz-modern`) — React pages in the template: pages list (website + custom pages, on/off switches with required pages locked, delete with confirm), GrapesJS page editor (`@grapesjs/react`, same blocks/SVG editing/duplicate as before) + SEO, custom page add/edit (TinyMCE, address auto-filled and unique), template settings (colour, slider, images, custom CSS/JS, announcement bar with live preview, popup, app banner); status/delete are now POST; per-field validation | ✅ Migrated, admin Blade removed |
| Web template: GoBiz Original (`admin.web-template.gobiz-original`) — React pages in the template: pages list (on/off, home + footer locked), section-based page editor (text rows grouped by section, saved by row id, SEO for every page except the footer), custom pages and template settings as in GoBiz Modern; status/delete are now POST | ✅ Migrated, admin Blade removed |
| Web template: GoBiz Rich UI (`admin.web-template.gobiz-rich`) — React pages in the template: pages list, GrapesJS editor with Rich's blocks and colour palette in the canvas (counters keep their value), custom pages, settings (appearance + blog share, app store links, announcement bar, popup; one config route saves only the fields each card sends); status/delete are now POST; public `check-slug-availability` unchanged | ✅ Migrated, admin Blade removed |
| Plugin: Auto Backup (`admin.plugin.auto-backup`) — new React plugin (v1.5: “keep the newest N backups” (`auto_backup_settings.keep_last`, migration `2026_10_12_200000_add_keep_last_to_auto_backup_settings`; null = keep all): after each fully successful backup, older backups are deleted from every storage and from the server, history rows stay with uploads marked `removed`; failed deletes are retried after the next backup; HTTP retries only for network/429/5xx; v1.4: page split into Backups / Storage / Schedule tabs (tab kept in `?tab=`), storage services listed as compact rows with brand logos, each set up in a dialog; OAuth callbacks return to the Storage tab; v1.3: + OneDrive (personal or Business) via Microsoft Graph with the admin's own Azure app, tenant choice, rotating refresh tokens stored encrypted, upload sessions in 10 MB pieces, webUrl links in history; MEGA was tried and left out: no OAuth/SDK and client-side encryption can't be verified without a real account; v1.2: + Dropbox via OAuth with the app's own key/secret, App-folder access, refresh tokens encrypted, upload sessions in 8 MB chunks through Dropbox's HTTP API (no SDK), folder setting, test/disconnect; v1.1: + Amazon S3 and S3-compatible storage such as R2/Wasabi/Spaces/MinIO via optional endpoint + path-style; keys encrypted, multipart upload in 16 MB parts, optional folder prefix, test connection; every backup goes to each active storage and history shows a link/status per storage): connect Google Drive with OAuth (client secret and tokens encrypted with APP_KEY, `drive.file` scope, “GoBiz Backups” folder), schedule (hourly → monthly, files and/or database, folders to leave out, result email), “Back up now” (runs as a detached `php artisan auto-backup:run`, page polls progress), full history with Drive links and “Upload again”. Database dump is gzipped, project zip skips node_modules/.git/caches; local copies are deleted after upload, failed ones kept for retry. Tables `auto_backup_{destinations,settings,runs,files,uploads}` (migration `2026_10_12_000000_create_auto_backup_tables`); destinations are driver-based so S3/R2/Mega can be added. Needs the server cron to run `schedule:run` **every minute** (the page warns when it doesn't). Core `BackupService` gained public `writeProjectZip()`/`writeDatabaseDump()` (same behaviour) | ✅ New |
| Everything else | Blade |

### Dashboard metric definitions

The dashboard is `DashboardMetricsService` + `resources/js/admin/pages/dashboard`.

- **Period:** `?period=7d|30d|90d|12m` (default `30d`). Every metric is compared with the previous period of the same length.
  - Charts are grouped by day for `7d`/`30d`, by week for `90d`, and by month for `12m`.
  - Changing the period is an Inertia partial reload of `period, kpis, revenueSeries, growthSeries, gateways, usage`.
- **Revenue** is money actually collected:
  - successful plan payments, **excluding the `FREE` and `TRIAL` gateways** (these rows carry the list price but no payment was taken),
  - plus NFC orders with `payment_status = success`,
  - plus AI credits with `payment_status = paid`.
  - Amounts are stored in the base currency.
- **Paid subscriptions:** successful plan payments excluding `FREE`/`TRIAL`. This includes renewals.
- **Active subscribers:** enabled customers whose `plan_validity` is in the future, split into paid and free using `plan_details.plan_price`.
- **Needs attention** (each item is shown only if the admin has the matching permission or feature):

  | Item | Condition |
  |---|---|
  | Offline payments | `Offline` gateway with status `PENDING` |
  | NFC orders | `pending`, `processing` or `hold` |
  | Low NFC stock | fewer than 10 in stock |
  | Domain requests | `transfer_status = 0` |
  | Referral withdrawals | `payment_status = 0` |
  | Expiring subscriptions | paid plans ending within 7 days |

- **Platform activity:** new vCards/stores, `visitors`, `contact_forms`, `store_orders` and `booked_appointments` in the period.

### ⚠️ Build isolation (important)

- The **ModernDashboard** plugin's React source is **not in this repo**. Only its compiled output exists, in `plugins/ModernDashboard/Views/js/build`, served from `public/build`.
- The default `vite.config.ts` writes into that folder. **Do not run `npm run build` / `npm run dev`.** It would overwrite the user panel, and it cannot be rebuilt.
- The admin panel uses its own config, build directory and hot file:

| | Admin panel |
|---|---|
| Vite config | `vite.admin.config.ts` |
| Entry | `resources/js/admin/app.tsx` |
| Output | `public/build-admin/` |
| Hot file | `public/hot-admin` |
| Root view | `resources/views/admin/app.blade.php` |
| TS config | `tsconfig.admin.json` |

```bash
npm install
npm run dev:admin      # Vite dev server for the admin panel
npm run build:admin    # production build → public/build-admin
npm run types:admin    # type-check admin sources
```

- **Tailwind:** the root `tailwindcss` package is **v3**, and `npm run build:store` still needs it. The admin CSS imports Tailwind **v4** through an npm alias: `@import "tailwindcss-v4";`.

### Server side

| File | Purpose |
|---|---|
| `app/Http/Middleware/HandleInertiaRequests.php` | For `/admin/*`, uses root view `admin.app`, an asset version from `build-admin/manifest.json`, and `AdminSharedProps`. User-panel behaviour is unchanged. |
| `app/Support/Admin/AdminSharedProps.php` | Shared props: `app`, `ui` (`accent`, `appearance`), `auth.user`, `permissions`, `features`, `navigation`, `currency`, `locale`, `flash`, `ziggy` |
| `app/Support/Admin/AdminPermissions.php` | Effective admin permissions (read-only merge with defaults, **never writes**) |
| `app/Support/Admin/AdminNavigation.php` | Sidebar tree (mirrors the Blade sidebar). The `INERTIA` constant lists migrated route names. |
| `app/Services/Admin/*` | Query/business logic for migrated modules (e.g. `DashboardMetricsService`) |

### Frontend (`resources/js/admin`)

```
app.tsx                 Inertia bootstrap (i18n, theme, default layout)
layouts/admin-layout.tsx
components/ui/          shadcn primitives (hand-written on @radix-ui/react-*; don't run the shadcn CLI blindly, it pulls the "radix-ui" package)
components/             list-toolbar (tabs+search), copy-button, app-sidebar, nav-main, nav-user, site-header, command-search (⌘K), flash-toaster,
                        page-header, pagination, empty-state, confirm-dialog, image-input, field-error
hooks/                  use-appearance, use-range-data, use-mobile
lib/                    utils (cn), i18n, format (currency), theme (accents), toast, language
pages/<module>/<page>.tsx           page, resolved as Inertia::render('<module>/<page>')
pages/<module>/components/          page-specific components
types/                  SharedProps, NavEntry, global route()
```

### Conventions

- **Theme:** shadcn **Neutral** base with a **Blue** accent.
  - Use only theme tokens (`bg-primary`, `text-muted-foreground`, `var(--chart-1)` …), never hard-coded colours.
  - Accents live in `resources/css/admin.css` as `[data-accent="…"]` blocks and are registered in `lib/theme.ts`. The active accent is `AdminSharedProps::ACCENT`, which is where the planned admin colour preference will plug in.
- **Dark mode:** comes from `users.choosed_theme`, toggled through the existing `admin.change.theme` route.
- **Translations:** `t("English text")` reads the same `resources/lang/{locale}.json` files as `__()`. Don't use interpolation keys. Switching language is a full reload (`?change_language=xx`).
- **Toasts:** use `toast` from `@admin/lib/toast` (react-hot-toast). Controller flashes `->with('success' | 'failed', …)` are shown automatically.
- **RTL:** use logical classes (`ms-*`, `me-*`, `start-*`, `end-*`, `text-start`). The sidebar moves to the right for RTL locales.
- **Links:** use `NavLink` / Inertia `<Link>` only for migrated pages. Blade pages need a plain `<a>`.

### Migrating a module: checklist

1. Check the live DB schema for every table the module uses.
2. Move query and business logic out of the controller/Blade into `app/Services/Admin/<Module>Service.php`.
3. Return `Inertia::render('<module>/index', [...])` from the controller. Keep route names, URLs and `user.page.permission` middleware the same.
4. Build `resources/js/admin/pages/<module>/<page>.tsx` (rendered as `'<module>/<page>'`). Reuse `PageHeader`, `Pagination`, `EmptyState`, `ConfirmDialog` and `useForm` (validation errors come back through `$request->validate()`). Replace DataTables with paginated props (`->paginate()->withQueryString()->through(...)`).
   - Turn state-changing `GET` routes into `POST` (same URL and name).
   - Make sure every route has its `user.page.permission:<key>` middleware.
5. Add the module's route names to `AdminNavigation::INERTIA`.
6. Check data parity against the old implementation (tinker), then run `npm run types:admin` and `npm run build:admin`.
7. Delete the module's Blade views and page-only assets (grep for references first).
8. Update the status table above.

### Translations (en.json)

All UI text goes through `t()` / `__()` with natural English keys. After building a module, run:

```bash
npm run i18n:admin              # append missing admin keys to resources/lang/en.json
npm run i18n:admin -- --check   # list missing keys (exit 1 if any)
```

The script only appends new keys; it never rewrites existing entries or other locales.

### Testing requests in tinker

The middleware has a few quirks, so requests need specific setup:

```bash
cd public   # Installer middleware checks the relative path ../storage/installed
php ../artisan tinker --execute '
Auth::login(App\User::find(1));
$req = Illuminate\Http\Request::create("https://gobiz-saas.test/admin/dashboard", "GET", [], [], [], [
    "HTTP_X_INERTIA" => "true",
    "HTTP_X_INERTIA_VERSION" => hash_file("xxh128", public_path("build-admin/manifest.json")),
]);
echo app(Illuminate\Contracts\Http\Kernel::class)->handle($req)->getContent();'
```

- Use the full app URL, because `ForceCustomDomainRoot` redirects other hosts.
- A `409` response means the `X-Inertia-Version` header is missing or wrong.

---

## Digital business card templates (`resources/views/templates/cards/<theme_code>`)

`resources/views/templates/` has two sides that share nothing: `cards/` (vCards) and `stores/` (WhatsApp stores).

```
cards/<theme_code>/index.blade.php    the card (themes.theme_code = folder name)
cards/<theme_code>/includes/…         sections / pieces only that card uses (e.g. cards/modern/includes/*)
cards/includes/                       pieces many cards use: appointment variants, newsletter / information popups,
                                      instagram-embed, recaptcha, service-booking, language switchers, open-whatsapp
cards/partials/                       payment-link-image, real-estate-payment-link-image
```

Profile, CustomDomain, Subdomain and User/Preview controllers render `view('templates.cards.' . $theme_code . '.index', $datas)`. A card only includes `templates.cards.includes.*`, `templates.cards.partials.*` and its own `templates.cards.<theme_code>.includes.*`.

## WhatsApp store themes (`resources/views/templates/stores/<theme>`)

Every store theme uses one shared layer, so cart, product options and checkout behave the same everywhere and are fixed in one place. A theme only provides HTML and styling.

**Shared parts**

| Part | What it does |
|---|---|
| `App\View\Composers\StorefrontComposer` | Gives every `templates.stores.*` view `$storefront`, `$storeUrl`, `$productUrlPrefix`, `$isCustomDomainOrSubdomain`, `$isRtl`, `$storeTheme` |
| `App\Support\Store\StoreTheme` | Which folder renders a theme code. The 16 `modern-store-{light\|dark}-{colour}` codes share `templates/stores/modern-store`; `$storeTheme->mode`, `->color` (Tabler class; gray = `secondary`), `->hex`. Controllers render `'templates.stores.' . StoreTheme::resolve($code)->view . '.<page>'` |
| `App\Support\Store\StorefrontContext` | Store addresses (main domain / subdomain / custom domain), `categoriesUrl`, `onHome()`, `onCategories()`, and the settings for the storefront script (money format, WhatsApp, opening hours, delivery methods, texts) |
| `App\Support\Store\ProductOptions` | Product page options (`$productOptions`, passed by `ProfileController::profileSingleProduct`): starts on the first in-stock option, `canBuy`, prices, `attributes()` for the product element |
| `App\StoreProduct` | `hasOptions()`, `listingPrice()` (cheapest in-stock option for "Starts from"; the listing queries select `min_variant_price`), `listingRegularPrice()`, `cartAttributes()` |
| `public/js/store/storefront.js` | The cart engine: storage (reads old carts too), cart list from a `<template>`, add to cart, option switching, quantity, checkout, opening hours, order POST (one at a time), WhatsApp message. Driven only by `data-*` attributes — see the header comment for the full list. Themes without Bootstrap pass `openCart`/`closeCart`/`openCheckout`/`closeCheckout` hooks (digital-products) |
| `templates/stores/shared/head` · `custom-code` · `scripts` · `product-options` | Meta/SEO/PWA; the owner's custom CSS/JS; the storefront script + settings; option buttons |

**Theme layout**

```
<theme>/index.blade.php, single-product, categories, policy
<theme>/includes/head.blade.php       shared.head + theme fonts/colours/base styles
<theme>/includes/scripts.blade.php    theme libraries (+ optional notify hook) + shared.scripts
<theme>/includes/header, footer, bottom-bar, …   use $storeUrl / $storefront, cart buttons use data-cart-open + data-cart-count
<theme>/partials/product-card.blade.php   listing + related products (styles inside @once)
<theme>/partials/cart-order-modal.blade.php   markup only: data-cart-drawer, <template data-cart-item>, form data-order-form
```

A page is: `@include('<theme>.includes.head')`, its own `<style>`, `@include('templates.stores.shared.custom-code')` in `<head>`; at the end of `<body>` the cart partial and `@include('<theme>.includes.scripts')`. Pages keep only page-specific scripts (gallery, sort, category search).

All 12 theme folders use this layer: cosmetics, digital-products (Tailwind), e-commerce, fashion, furniture, gift-shop, grocery, jewellery-shop, modern-store (16 colour/mode codes), pet-store, restaurant, toy-store. `templates/stores/includes/` holds the includes several store themes use: `header` (modern-store), `bottom-bar` (modern, pet, restaurant, toy), `bs-language-switcher`, `whatsapp-float`, `share` — wired to the same `data-cart-*` hooks.

## Known issues (not fixed yet)

- Many destructive admin actions are `GET` routes (e.g. `delete-plan`, `update-status`). Fix these as each module migrates.
- `Modules/TranslationManager` routes use only `web` + `auth`, with no `admin` check, so any logged-in customer can reach them.
- The middleware array at `routes/web.php:253` is malformed. Only `frame.destroyer` applies.
- `CheckDemoMode` redirects to `admin.login`, which doesn't exist.
- `GoBizCommonService` calls `Cache::forget()` before every `Cache::remember()`, so caching never takes effect.
- The Blade sidebar (`admin/includes/sidebar.blade.php`) writes default permissions to the DB and runs unused transaction counts on every render. It goes away when Blade is removed.
- Payment gateway controllers are duplicated three times (plans / NFC / AI credits).

## Plugin admin pages (React)

Guides for Claude Code and developers (in `.claude/skills/`, which is git-ignored, so they stay local):

- `convert-plugin-to-react/SKILL.md`: convert a plugin's Blade admin page (starter files in `templates/`)
- `convert-template-to-react/SKILL.md`: convert a web template's admin pages (references: GoBizModern, GoBizRich, GoBizOriginal)
- `create-plugin/SKILL.md`: scaffold a new plugin from a description
- `create-template/SKILL.md`: scaffold a new web template, including the public `::Website.*` view contract

Plugin zips are built by our team and uploaded by customers; customers' servers never compile JavaScript. A plugin's React admin page lives in the plugin and ships prebuilt:

```
plugins/<Name>/resources/js/<page>.tsx   source (not shipped)
plugins/<Name>/dist/<page>.js            built page
plugins/<Name>/dist/style.css            Tailwind utilities used by the plugin
plugins/<Name>/dist/manifest.json        pages + version
```

- **Build:** `npm run plugin:build -- <Name>` (config: `vite.plugin.config.ts`). **Release zip:** `npm run plugin:zip -- <Name>` → `storage/app/plugin-releases/<Name>-<version>.zip` (sources excluded). Type-check: `npm run types:plugins`.
- **Imports:** plugin pages import React, `@inertiajs/react`, `react-i18next` and `@gobiz/admin` (shadcn ui, PageHeader, FieldError, ConfirmDialog, toast, format helpers… see `resources/js/admin/plugin-sdk/admin.ts`). These are **not bundled**; they come from `window.GoBizAdmin` (`resources/js/admin/plugin-runtime.ts`), so plugin bundles are tiny and look identical to core pages. Icons (`lucide-react`) are bundled per plugin.
- **Controller:** `return PluginPage::render('<Name>', '<page>', $props);` (`app/Support/Admin/PluginPage.php`) → Inertia component `plugin:<Name>@<version>/<page>`; `app.tsx` loads `/admin/plugin-assets/<Name>/<page>.js?v=<version>` + `style.css` and wraps it in the normal admin layout.
- **Assets** are served by `PluginAssetController` from `dist/` only (whitelisted types, path-escape guarded, cached for a year; the `?v=` changes with the plugin version — bump `version` in `plugin.json` on every release).
- **Compatibility:** `plugin.json` `"requires_admin"` must be ≤ `AdminSharedProps::PLUGIN_API` (= `PLUGIN_API` in `plugin-runtime.ts`); otherwise the plugin shows a "needs a newer GoBiz" page. Bump `PLUGIN_API` only for breaking changes to `plugin-sdk/admin.ts`.
- **Upload check:** a zip needs `plugin.json`, `routes.php`, `Controllers/` and either `Views/*.blade.php` or `dist/manifest.json`.
- Translations: `npm run i18n:admin` also scans `plugins/*/resources/js` and `templates/*/resources/js`, and their `Controllers`.
- **Web templates** work the same way: sources in `templates/<Name>/resources/js`, `npm run plugin:build -- <Name>` / `plugin:zip` (zip → `storage/app/template-releases/`), controllers call `PluginPage::template('<Name>', '<page>', $props)` (component `template:<Name>@<version>/<page>`, assets from `/admin/template-assets/<Name>/…`), manifest is `template.json`. Libraries a page needs beyond the SDK (e.g. GrapesJS for the page editor) are bundled into that page's own file. `react-dom` is shared too.

