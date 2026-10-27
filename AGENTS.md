<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.3. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>

## State Tracking & Handover

### 1. Current Status
- **Workstream 6: Vendor Portal (`/vendor`) Selesai**:
  - Modul Autentikasi & Registrasi: [AuthController](file:///d:/laragon/www/TitipSini/app/Http/Controllers/AuthController.php), [login.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/auth/login.blade.php) (dengan 1-click Quick Demo Role Switcher untuk Mitra, Pelanggan, Superadmin, dan Admin Ops), dan [register.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/auth/register.blade.php).
  - Dedicated Portal Layout: [layouts/vendor.blade.php](file:///d:/laragon/www/TitipSini/resources/views/layouts/vendor.blade.php) dengan sidebar responsif, status operasional toko (Buka/Tutup switch), dan badge verifikasi KYC mitra.
  - Dashboard Operasional Mitra: [VendorDashboardController](file:///d:/laragon/www/TitipSini/app/Http/Controllers/Vendor/VendorDashboardController.php) dan [dashboard.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/vendor/dashboard.blade.php) dengan 4 metrik KPI (Total Booking Aktif, Barang Tersimpan Tersegel, Siap Diambil, dan Total Pendapatan Bersih 80% Net Share).
  - Manajemen Antrian Penitipan & Check-In Segel: [VendorBookingController](file:///d:/laragon/www/TitipSini/app/Http/Controllers/Vendor/VendorBookingController.php), [bookings/index.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/vendor/bookings/index.blade.php), dan lembar kerja operasional [bookings/show.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/vendor/bookings/show.blade.php) dengan input nomor segel barcode fisik (`SEAL-...`).
  - Strict Handover & Payment Verification: Tombol serah-terima fisik terkunci rapat secara server-side dan hanya dapat dieksekusi jika pembayaran Xendit telah terkonfirmasi LUNAS (`paid`), sesuai aturan tata kelola ADR-002 section 9.3.
  - Kelola Listing & Tarif Vendor: [VendorListingController](file:///d:/laragon/www/TitipSini/app/Http/Controllers/Vendor/VendorListingController.php), [listings/index.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/vendor/listings/index.blade.php), dan [listings/edit.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/vendor/listings/edit.blade.php).
  - Riwayat Settlement & Pengaturan Rekening Bank: [VendorSettlementController](file:///d:/laragon/www/TitipSini/app/Http/Controllers/Vendor/VendorSettlementController.php) dan [settlements/index.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/vendor/settlements/index.blade.php) untuk mengelola nomor rekening BCA/Mandiri/BRI dan meninjau pencairan dana 80% net.
  - Automated Tests: **36 tests, 126 assertions, 100% PASSED** ([VendorPortalTest.php](file:///d:/laragon/www/TitipSini/tests/Feature/VendorPortalTest.php), [XenditPaymentTest.php](file:///d:/laragon/www/TitipSini/tests/Feature/XenditPaymentTest.php), [BookingTest.php](file:///d:/laragon/www/TitipSini/tests/Feature/BookingTest.php), [CatalogTest.php](file:///d:/laragon/www/TitipSini/tests/Feature/CatalogTest.php), [UserRoleTest.php](file:///d:/laragon/www/TitipSini/tests/Feature/UserRoleTest.php)).
  - Linter Pint lulus 100% (`vendor/bin/pint --dirty --format agent`).

### 2. Pending Tasks
- **Workstream 7: Admin & Superadmin Dashboard (`/admin`)**:
  - Manajemen & verifikasi KYC Mitra Vendor (review dokumen identitas dan SKCK sesuai tata kelola privasi ADR-002, tombol approve/reject).
  - Moderasi Listing publik (review kelayakan lokasi penitipan sebelum tayang publik).
  - Rekonsiliasi Finansial & Monitoring Payout (menggantikan peran finance yang dieliminasi; review komisi 20% platform TitipSini vs 80% mitra).
  - Viewer Jejak Audit Sistem ([AuditLog](file:///d:/laragon/www/TitipSini/app/Models/AuditLog.php)).

### 3. Architectural Decisions
- **Vendor Isolation & Physical Security Sealing**: Setiap vendor hanya dapat mengakses booking miliknya sendiri. Saat barang dititipkan, nomor segel fisik (`SEAL-...`) wajib tercatat di sistem sebagai bukti tamper-evident.
- **Strict Payment-Gated Handover (ADR-002 section 9.3)**: Mencegah barang dilepas tanpa pembayaran. Sistem memblokir serah-terima fisik sampai webhook atau pemeriksaan server membuktikan status invoice Xendit lunas.
- **Automatic Settlement Trigger**: Payout settlement 80% mitra langsung dieksekusi otomatis setelah serah-terima selesai dikonfirmasi.
- **No Finance Role**: Peran finansial dipegang penuh oleh Superadmin dan Admin untuk rekonsiliasi dan monitoring transaksi.

### 4. Active Diffs
- `app/Enums/VendorVerificationStatus.php`
- `app/Http/Middleware/EnsureUserHasRole.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/Vendor/VendorDashboardController.php`
- `app/Http/Controllers/Vendor/VendorBookingController.php`
- `app/Http/Controllers/Vendor/VendorListingController.php`
- `app/Http/Controllers/Vendor/VendorSettlementController.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/vendor.blade.php`
- `resources/views/pages/auth/login.blade.php`
- `resources/views/pages/auth/register.blade.php`
- `resources/views/pages/vendor/dashboard.blade.php`
- `resources/views/pages/vendor/bookings/index.blade.php`
- `resources/views/pages/vendor/bookings/show.blade.php`
- `resources/views/pages/vendor/listings/index.blade.php`
- `resources/views/pages/vendor/listings/edit.blade.php`
- `resources/views/pages/vendor/settlements/index.blade.php`
- `routes/web.php`
- `tests/Feature/VendorPortalTest.php`
- `AGENTS.md`


