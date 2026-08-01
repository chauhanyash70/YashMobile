# Laravel Project Guidelines & Rules

Welcome to the **YashMobile** Laravel project workspace! Always follow these guidelines and conventions when working on this codebase.

## 1. Project Directory Structure
- **Controllers**: `app/Http/Controllers/`
- **Models**: `app/Models/` (Use Eloquent relationships properly)
- **Migrations**: `database/migrations/`
- **Views**: `resources/views/` (Use Blade templating system)
- **Routes**: `routes/web.php` for web endpoints, `routes/api.php` for API endpoints, `routes/console.php` for artisan commands.

## 2. Coding Standards & Styles
- Follow PSR-12 coding standards.
- **Naming Conventions**:
  - Controllers: StudlyCase (e.g., `InvoiceController`)
  - Models: Singular StudlyCase (e.g., `Invoice`)
  - Migrations: snake_case (e.g., `2026_07_31_000000_create_invoices_table`)
  - Views: kebab-case/snake_case (e.g., `invoice/index.blade.php`, `invoice/print-layout.blade.php`)
  - Database Tables: Plural snake_case (e.g., `invoices`)
- Use Eloquent ORM instead of raw SQL queries whenever possible to ensure security and clean code.

## 3. UI Components & Frontend Conventions
- **Datepicker**: Use `vanillajs-datepicker` (`vendor-assets/libs/vanillajs-datepicker/`) with `autoHide: true` and `format: 'yyyy-mm-dd'` for date fields.
- **DataTables & Filtering**: Implement server-side DataTables with custom filters (e.g. `from_date`, `to_date`, `payment_method`) passed via AJAX payload in `getData()` controller endpoints.
- **Header Shortcuts**: HSN search in topbar header is bound to `Ctrl + K` / `Cmd + K`.

## 4. Environment Variables & Security
- Never hardcode sensitive credentials, keys, or API tokens.
- Add them to [`.env`](file:///d:/Project/Laravel/YashMobile/.env) and access them using `env()` or `config()`.
- Add placeholders to [`.env.example`](file:///d:/Project/Laravel/YashMobile/.env.example) when introducing new keys.

## 5. Troubleshooting & Logging
- Check `storage/logs/laravel.log` for runtime exceptions and error details.
- Use `Log::info()`, `Log::error()`, or `Log::warning()` to record events.
- Avoid leaving `dd()`, `dump()`, or `print_r()` in production-bound code.
