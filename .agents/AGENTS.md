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
- **Datepicker**: Use `vanillajs-datepicker` (`vendor-assets/libs/vanillajs-datepicker/`) with `autoHide: true` and `format: 'yyyy-mm-dd'` for date fields. Ensure `datepicker-dark.css` is included for high-contrast light & dark mode visibility of calendar cells and weekday headers.
- **DataTables & Filtering**: Implement server-side DataTables with custom filters (e.g. `brand_id`, `status`, `condition`, `from_date`, `to_date`, `payment_method`) passed via AJAX payload in `getData()` controller endpoints.
- **Excel Exports**: Use Maatwebsite/Excel export classes implementing `FromCollection`, `WithHeadings`, `WithMapping`, `ShouldAutoSize`, `WithStyles`. Ensure conditional fields (e.g. `Sold To`, `Sold Date`) are only populated when the item is actually sold (`status === 'sold'`), displaying `N/A` for in-stock items.
- **Header Shortcuts**: HSN search in topbar header is bound to `Ctrl + K` / `Cmd + K`.

## 4. Routing Guidelines
- Always register custom GET/POST routes (such as `/export`, `/export-available`, `/get-data`, `/search-hsn`) **BEFORE** declaring `Route::resource(...)` to prevent parameter collision with `{id}` or `{model}` route bindings.

## 5. Environment Variables & Security
- Never hardcode sensitive credentials, keys, or API tokens.
- Add them to [`.env`](file:///d:/Project/Laravel/YashMobile/.env) and access them using `env()` or `config()`.
- Add placeholders to [`.env.example`](file:///d:/Project/Laravel/YashMobile/.env.example) when introducing new keys.

## 6. Troubleshooting & Logging
- Check `storage/logs/laravel.log` for runtime exceptions and error details.
- Use `Log::info()`, `Log::error()`, or `Log::warning()` to record events.
- Avoid leaving `dd()`, `dump()`, or `print_r()` in production-bound code.

## 7. Device Lifecycle, Invoices & Buyback Rules
- **Multi-Lifecycle Structure**:
  - Each lifecycle cycle of a device (Purchase -> Sale -> Buyback -> Sale...) creates a distinct record in the `mobiles` table with the same `hsn_number`.
  - The inventory and listings query the latest active unit using `MAX(id)` grouped by `hsn_number`.
  - When a device is sold, `status = 'sold'` and its `InvoiceItem` tracks `is_bought_back` (boolean).
  - When a sold device is bought back (`buybackStore`), the previous `InvoiceItem` is marked `is_bought_back = true` and a new `Mobile` row (`status = 'in_stock'`) is created along with a buy invoice and transaction.
- **Invoice Deletion Handling**:
  - **Sell Invoice Deleted**: Mobile status is reverted to `in_stock`, accessory stock incremented, and associated sale transactions deleted. Mobile record is NOT deleted.
  - **Buy Invoice Deleted (Single Lifecycle / Initial Purchase)**: When an initial buy invoice is deleted and the unit has no subsequent sales history, the `Mobile` record, its buy transactions, repairs, and expenses are completely deleted/cleaned up.
  - **Buyback Invoice Deleted (Multi-Lifecycle)**: When a buyback invoice is deleted:
    1. The previous unit's sale `InvoiceItem` must have `is_bought_back` reverted to `false`.
    2. The temporary buyback `Mobile` unit (along with its repairs, expenses, and transactions) is deleted.
    3. The device automatically falls back to its previous cycle unit (e.g. `Sold` state).

