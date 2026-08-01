---
name: laravel_frontend_ui
description: Conventions for frontend UI components, DataTables, Datepickers, and keyboard shortcuts in YashMobile.
---

# Frontend UI & Component Standards

Use this skill when building or updating Blade views, form controls, tables, or interactive UI components in the YashMobile project.

## 1. Datepickers
- Use `vanillajs-datepicker` assets:
  ```html
  <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet" type="text/css" />
  <script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>
  ```
- Standard initialization:
  ```javascript
  document.querySelectorAll('.date-picker').forEach(el => {
      new Datepicker(el, {
          autoHide: true,
          format: 'yyyy-mm-dd',
      });
  });
  ```

## 2. Server-side DataTables Filtering
- Pass custom filter fields (e.g. `from_date`, `to_date`, `payment_method`) in DataTable `ajax.data` callback:
  ```javascript
  ajax: {
      url: "{{ route('invoice.getData') }}",
      type: "POST",
      data: function(d) {
          d._token = "{{ csrf_token() }}";
          d.from_date = $('#from_date').val();
          d.to_date = $('#to_date').val();
          d.payment_method = $('#payment_method').val();
      }
  }
  ```
- Reload table on filter/reset click using `tableInstance.ajax.reload()`.

## 3. Global Shortcuts
- Header HSN search shortcut: `Ctrl + K` / `Cmd + K`.
