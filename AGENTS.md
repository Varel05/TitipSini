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
- **Workstream 7: Admin & Superadmin Dashboard (`/admin`) Selesai & Bugfix Terselesaikan**:
  - Centralized Executive Layout: [layouts/admin.blade.php](file:///d:/laragon/www/TitipSini/resources/views/layouts/admin.blade.php) dengan navigasi sidebar lengkap (Dashboard, Verifikasi KYC, Moderasi Listing, Rekonsiliasi Finansial, dan Jejak Audit), quick link ke portal mitra dan web publik, profil admin, dan role badge.
  - Executive Dashboard: [AdminDashboardController](file:///d:/laragon/www/TitipSini/app/Http/Controllers/Admin/AdminDashboardController.php) & [dashboard.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/admin/dashboard.blade.php) (GMV, platform commission 20%, vendor payout 80%, antrian KYC, antrian moderasi listing, dan ringkasan audit log).
  - Manajemen & Verifikasi KYC Mitra Vendor: [AdminVendorKycController](file:///d:/laragon/www/TitipSini/app/Http/Controllers/Admin/AdminVendorKycController.php), [vendors/index.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/admin/vendors/index.blade.php), dan [vendors/show.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/admin/vendors/show.blade.php) (Review berkas identitas & SKCK sesuai tata kelola privasi ADR-002, tombol Approve dan Reject dengan alasan tertulis).
  - Moderasi Listing Tempat Penitipan: [AdminListingModerationController](file:///d:/laragon/www/TitipSini/app/Http/Controllers/Admin/AdminListingModerationController.php), [listings/index.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/admin/listings/index.blade.php), dan [listings/show.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/admin/listings/show.blade.php) (Approve & publish listing ke katalog publik atau tolak/nonaktifkan).
  - Rekonsiliasi Finansial & Monitoring Payout: [AdminFinanceController](file:///d:/laragon/www/TitipSini/app/Http/Controllers/Admin/AdminFinanceController.php) dan [finance/index.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/admin/finance/index.blade.php) (Buku besar komisi platform 20% vs porsi vendor 80%, mutasi invoice Xendit, dan tombol Retry Payout untuk pencairan manual).
  - Viewer Jejak Audit Sistem: [AdminAuditLogController](file:///d:/laragon/www/TitipSini/app/Http/Controllers/Admin/AdminAuditLogController.php) dan [audit-logs/index.blade.php](file:///d:/laragon/www/TitipSini/resources/views/pages/admin/audit-logs/index.blade.php) (Filter aksi, pencarian IP/user, dan JSON payload viewer).
  - Pembaruan 6 Layanan Resmi TitipSini: Menyelaraskan seluruh kategori dan layanan utama platform sesuai arahan: 1. Penitipan Traveling, 2. Pindahan Kost/Kontrakan, 3. Penitipan Kendaraan, 4. Penitipan Barang Kos, 5. Jasa Security Rumah (Bangunan), dan 6. Jasa Angkut Barang Anak KKN pada `DatabaseSeeder`, navigasi footer `layouts/app.blade.php`, dan katalog etalase `pages/home.blade.php`.
  - Automated Tests: **51 tests, 187 assertions, 100% PASSED** ([VendorPortalTest.php](file:///d:/laragon/www/TitipSini/tests/Feature/VendorPortalTest.php), [UserRoleTest.php](file:///d:/laragon/www/TitipSini/tests/Feature/UserRoleTest.php), [AdminDashboardTest.php](file:///d:/laragon/www/TitipSini/tests/Feature/AdminDashboardTest.php), [XenditPaymentTest.php](file:///d:/laragon/www/TitipSini/tests/Feature/XenditPaymentTest.php), [BookingTest.php](file:///d:/laragon/www/TitipSini/tests/Feature/BookingTest.php), [CatalogTest.php](file:///d:/laragon/www/TitipSini/tests/Feature/CatalogTest.php)).
  - Linter Pint lulus 100% (`vendor/bin/pint --dirty --format agent`).

### 2. Pending Tasks
- **Penyempurnaan & Ekspansi Masa Depan (Opsional / Pasca-MVP)**:
  - Kanal notifikasi tambahan (misal integrasi WhatsApp API resmi bila PO memutuskan untuk mengaktifkannya).
  - Multi-bahasa (ID / EN) untuk turis mancanegara.
  - Setup live credential Xendit xenPlatform di production environment.

### 3. Architectural Decisions
- **Core Multi-Service Taxonomy**: Platform TitipSini mendefinisikan 6 kluster layanan terintegrasi (Traveling, Pindahan Kost/Kontrakan, Penitipan Kendaraan, Penitipan Barang Kos, Jasa Security Rumah/Bangunan, dan Angkut Barang KKN) yang mencakup kebutuhan mahasiswa, perantau, wisatawan, dan pemilik properti.
- **Centralized Executive Governance (ADR-002)**: Administrator dan Superadministrator memegang kendali penuh atas moderasi katalog, persetujuan KYC vendor (termasuk verifikasi dokumen SKCK berisiko tinggi), rekonsiliasi finansial (menggantikan peran finance yang dieliminasi), dan pengawasan jejak audit.
- **Defense-in-Depth & Non-Repudiation**: Setiap tindakan administratif penting (Approve/Reject KYC, Publish Listing, Retry Payout) otomatis menghasilkan entri immutable di `audit_logs` bersama identitas admin dan alamat IP.
- **Zero Upfront Booking & Split Settlement Model**: Platform beroperasi dengan model tanpa DP di depan, penagihan close-out saat barang diambil, verifikasi ketat sebelum serah-terima fisik, dan otomasi split payout 80% ke vendor.

### 4. Active Diffs
- `database/seeders/DatabaseSeeder.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/pages/home.blade.php`
- `AGENTS.md`



