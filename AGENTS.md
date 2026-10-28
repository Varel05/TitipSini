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
- **Task 1: Struktur Wilayah Operasional & Data Boundary (Policies 2.1, 3.1, 13.2, 13.3) Selesai**: Berhasil diuji dan di-push ke GitHub (`commit: 9056815`).
- **Task 2: Tata Kelola SKCK & Privacy Request Dokumen Sensitif (Policies 6.1 - 6.3) Selesai**: Berhasil diuji dan di-push ke GitHub (`commit: db79954`).
- **Task 3: Siklus Booking Granular & Privasi Kontak (Policies 8, 13.1, & 15.1) Selesai**: Berhasil diuji dan di-push ke GitHub (`commit: 6a31ee2`).
- **Task 4: Pembatasan Percobaan QRIS & Siklus Reopen Admin (Policy 10.2) Selesai**: Berhasil diuji dan di-push ke GitHub (`commit: 792faf8`).
- **Task 5: Modul Sengketa (Dispute Management) & Eskalasi Superadmin (Policy 11) Selesai**:
  - **Skema & Model Sengketa**:
    - Migration `create_disputes_and_vendor_suspension_table` membuat tabel `disputes` dan menambahkan kolom `is_suspended`, `suspended_at`, `suspension_reason`, dan `suspended_by` pada `vendor_profiles`.
    - Enums `DisputeCategory` (dengan klasifikasi sengketa biasa vs wajib eskalasi Superadmin) dan `DisputeStatus` (Open, Escalated, Resolved, Rejected).
    - Menambahkan enum case `SettlementStatus::OnHold` (Ditahan Karena Sengketa / Escrow Hold).
  - **Pelaporan Sengketa & Escrow Hold Otomatis (Policy 11.1 Poin 5)**:
    - Pelanggan dapat mengajukan pengaduan sengketa melalui antarmuka tiket `/pesanan/{booking}/komplain`.
    - Saat sengketa diajukan, status booking beralih ke `BookingStatus::Disputed` dan payout mitra otomatis dikunci pada status `SettlementStatus::OnHold`.
    - Sengketa kategori kritis (`LostGoods`, `Fraud`, `PaymentManipulation`, `SafetyThreat`, `CriminalOffense`) langsung berstatus `Escalated` ke Superadmin.
  - **Penyelesaian Wilayah & Pelepasan Dana (Policy 11.2 & 11.3)**:
    - Admin Wilayah berwenang memediasi sengketa operasional biasa di wilayahnya (`DamagedItem`, `OperationalDelay`, `LocationIssue`).
    - Admin dapat menyelesaikan sengketa dengan atau tanpa pemotongan kompensasi/denda (`vendor_penalty_amount`), melepaskan status escrow hold kembali ke `Pending` (nominal disesuaikan), dan menyelesaikan pesanan.
    - Admin biasa dilarang memutus kasus eskalasi Superadmin (HTTP 403 Forbidden).
  - **Arbitrase Superadmin & Pembekuan Akun Mitra (Policy 7.3 & 11.2)**:
    - Superadmin memiliki modul arbitrase eksklusif untuk kasus kritis lintas daerah.
    - Otoritas pembekuan akun mitra (`suspend_vendor`), mencatat alasan penangguhan, dan memblokir mitra secara menyeluruh dari menerima booking baru di platform (`canAcceptBookings() = false`).
  - **Verifikasi Ganda (PHP Tests & Browser Interface)**:
    - Automated Feature Tests: **89 tests, 352 assertions, 100% PASSED** (termasuk 6 skenario komprehensif di `DisputeManagementTest.php` dan otentikasi riwayat pesanan di `BookingTest.php`).
    - Live Browser Interaction: Skenario end-to-end (tiket 48 jam -> ajukan komplain -> booking disputed -> payout escrow hold -> admin investigasi & resolusi denda -> superadmin arbitrase fraud & pembekuan akun mitra) diverifikasi 100% PASS.
    - Linter Pint lulus 100% (`vendor/bin/pint --dirty --format agent`).
- **Perbaikan Keamanan & Privasi Riwayat Pesanan (/pesanan)**:
  - Menghapus dummy fallback `User::where('role', 'customer')->first()` pada `BookingController::index()`.
  - Menerapkan middleware `auth` pada route `booking.index` (`/pesanan`), sehingga tamu anonim yang belum login otomatis dialihkan (redirect HTTP 302) ke halaman login `/login`.
  - Memastikan pengguna yang telah login hanya dapat melihat riwayat pesanannya sendiri (`where('customer_id', auth()->id())`). Terverifikasi via test `BookingTest`.

- **Perbaikan UI Tabel Settlement Mitra (/vendor/settlements)**:
  - Mempersingkat teks label badge enum `SettlementStatus::Pending` dari `'Menunggu Pencairan Mitra'` menjadi `'Menunggu Pencairan'`.
  - Menambahkan styling responsif dengan `whitespace-nowrap` pada kolom ID, kode booking, badge status, nominal, dan ref payout.
  - Membatasi lebar nama pelanggan (`max-w-[150px] truncate`) dan menyesuaikan padding sel tabel (`py-3.5 px-4 sm:px-5`).
  - Menjaga tabel di dalam wrapper `overflow-x-auto w-full` pada card berbatas `overflow-hidden` sehingga tidak memanjang melebihi batas layar, teks tidak terpotong atau ter-wrap berantakan, serta telah dibuild ulang via Vite.

### 2. Pending Tasks
- **Penyelarasan Kebijakan Operasional Menyeluruh (Policy 1-19) Selesai**: Seluruh task utama (Wilayah Operasional & Boundary, Tata Kelola SKCK & Privasi, Siklus Booking & Kontak, Pembatasan QRIS & Reopen, Modul Sengketa & Eskalasi, serta polish antarmuka/responsiveness vendor) telah rampung dan terverifikasi ganda. Siap untuk pengujian regresi platform menyeluruh dan rilis.

### 3. Architectural Decisions
- **Escrow Payout Invariance (Policy 11.1)**: Mengunci `vendor_settlements` pada status `OnHold` saat sengketa diajukan melindungi dana konsumen dan integritas platform sebelum barang atau kerugian diselesaikan.
- **Hierarchical Dispute Escalation**: Membedakan sengketa operasional harian (wewenang Admin Wilayah) dan sengketa kritis hukum/kehilangan (wewenang eksklusif Superadmin) menjamin penegakan tata kelola yang ketat tanpa menghambat kecepatan operasional wilayah.
- **Table Density & Overflow Containment**: Menggunakan token whitespace-nowrap pada data numerik/kode/status serta truncate pada metadata teks panjang untuk memastikan konsistensi layout grid pada dashboard operasional dan mobile viewport.

### 4. Active Diffs
- `app/Enums/SettlementStatus.php`
- `resources/views/pages/vendor/settlements/index.blade.php`
- `AGENTS.md`



