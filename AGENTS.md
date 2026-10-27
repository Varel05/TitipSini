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
- **Role Finance Ditiadakan**:
  - Peran `finance` dihapus dari seluruh spesifikasi sistem. Tugas rekonsiliasi finansial dan audit transaksi dikonsolidasikan langsung ke `superadmin` dan `admin` sesuai arahan.
  - File [IMPLEMENTATION_PLAN_TitipSini.md](file:///d:/laragon/www/TitipSini/IMPLEMENTATION_PLAN_TitipSini.md) dan [AGENTS.md](file:///d:/laragon/www/TitipSini/AGENTS.md) telah diselaraskan.
- **Workstream 2: Identity, Roles, and Access Control Selesai**:
  - Dibuat Enum [UserRole](file:///d:/laragon/www/TitipSini/app/Enums/UserRole.php) (`customer`, `vendor`, `admin`, `superadmin`).
  - Dibuat Enum [VendorVerificationStatus](file:///d:/laragon/www/TitipSini/app/Enums/VendorVerificationStatus.php) (`unverified`, `pending`, `verified`, `rejected`).
  - Migrasi penambahan kolom role, phone, avatar_url, is_active ke tabel `users`.
  - Migrasi & Model [CustomerProfile](file:///d:/laragon/www/TitipSini/app/Models/CustomerProfile.php) dan [VendorProfile](file:///d:/laragon/www/TitipSini/app/Models/VendorProfile.php).
  - Migrasi & Model [AuditLog](file:///d:/laragon/www/TitipSini/app/Models/AuditLog.php) untuk pencatatan jejak audit aksi administratif.
  - Middleware [EnsureUserHasRole](file:///d:/laragon/www/TitipSini/app/Http/Middleware/EnsureUserHasRole.php) terdaftar di `bootstrap/app.php` dengan alias `role`.
  - Factories ([UserFactory](file:///d:/laragon/www/TitipSini/database/factories/UserFactory.php), [VendorProfileFactory](file:///d:/laragon/www/TitipSini/database/factories/VendorProfileFactory.php), [CustomerProfileFactory](file:///d:/laragon/www/TitipSini/database/factories/CustomerProfileFactory.php)) dan [DatabaseSeeder](file:///d:/laragon/www/TitipSini/database/seeders/DatabaseSeeder.php) siap digunakan.
  - Automated Feature Tests ([UserRoleTest](file:///d:/laragon/www/TitipSini/tests/Feature/UserRoleTest.php), [EnsureUserHasRoleMiddlewareTest](file:///d:/laragon/www/TitipSini/tests/Feature/EnsureUserHasRoleMiddlewareTest.php)) 100% passed (12 tests, 34 assertions).
  - Linter Pint (`vendor/bin/pint --dirty --format agent`) lulus 100%.

### 2. Pending Tasks
- **Workstream 3: Vendor, Lokasi, Katalog & Listing**:
  - Migrasi & Model untuk Category, Location, Listing, Facility, Media, Unit/Slot.
  - Public landing page, search & filter katalog, detail listing dengan Tailwind CSS.
- **Workstream 4 & 5: Booking Core, Xendit Gateway Adapter & Lifecycle Transaksi**:
  - State machine booking: booking dibuat -> konfirmasi vendor -> check-in -> pengambilan & bayar via Xendit -> settlement payout vendor.
  - Adapter Xendit (xenPlatform) dan webhook handler dengan proteksi idempotency.

### 3. Architectural Decisions
- **Eliminasi Role Finance**: Menghilangkan overhead peran terpisah; kontrol rekonsiliasi dan verifikasi pembayaran dialihkan ke level Admin dan Super Admin.
- **Strict Role Separation via Enum**: Menggunakan PHP 8.3 Enum (`UserRole`) untuk keamanan tipe data dan integritas database.
- **Middleware Guard**: `EnsureUserHasRole` memverifikasi status keaktifan akun (`is_active`) dan kecocokan role sebelum mengizinkan eksekusi request controller.
- **Database MySQL**: Menggunakan MySQL dari Laragon lokal sesuai spesifikasi PRD dan keandalan transaksi ACID (innoDB).

### 4. Active Diffs
- `app/Enums/UserRole.php` (Enum role: Customer, Vendor, Admin, Superadmin)
- `app/Enums/VendorVerificationStatus.php` (Enum status verifikasi vendor)
- `app/Models/User.php` (Cast role, helper methods, dan relasi profil)
- `app/Models/CustomerProfile.php` & `app/Models/VendorProfile.php` & `app/Models/AuditLog.php`
- `app/Http/Middleware/EnsureUserHasRole.php` & `bootstrap/app.php`
- `database/migrations/*` (Tabel role di users, customer_profiles, vendor_profiles, audit_logs)
- `database/factories/*` & `database/seeders/DatabaseSeeder.php`
- `tests/Feature/UserRoleTest.php` & `tests/Feature/EnsureUserHasRoleMiddlewareTest.php`
- `IMPLEMENTATION_PLAN_TitipSini.md` & `AGENTS.md`
