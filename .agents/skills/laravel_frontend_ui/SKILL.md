---
name: laravel_frontend_ui
description: Conventions for frontend UI components, DataTables, Datepickers, and keyboard shortcuts in YashMobile.
---

# Frontend UI & Component Standards

Use this skill when building or updating Blade views, form controls, tables, or interactive UI components in the YashMobile project.

## 1. Datepickers
- Include `vanillajs-datepicker` assets:
  ```html
  @section('pageCss')
      <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet" type="text/css" />
  @endsection

  @section('pageScripts')
      <script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>
  ```
- Standard initialization in `$(document).ready()`:
  ```javascript
  var datePickers = [];
  document.querySelectorAll('.date-picker').forEach(el => {
      datePickers.push(new Datepicker(el, {
          autoHide: true,
          format: 'yyyy-mm-dd',
      }));
  });
  ```
- Make sure `assets/css/datepicker-dark.css` is loaded to guarantee high-contrast text styling for `.dow` and `.datepicker-cell` in both Light & Dark modes.

## 2. Server-side DataTables Filtering & Action Bar
- Pass custom filter fields (e.g. `brand_id`, `status`, `condition`, `from_date`, `to_date`, `payment_method`) in DataTable `ajax.data` callback:
  ```javascript
  ajax: {
      url: "{{ route('getMobileData') }}",
      type: "POST",
      data: function(d) {
          d._token = csrfToken;
          d.brand_id = $('#brand_id').val();
          d.status = $('#status').val();
          d.condition = $('#condition').val();
          d.from_date = $('#from_date').val();
          d.to_date = $('#to_date').val();
      }
  }
  ```
- Reload table on filter click: `tableVar.ajax.reload();`
- Reset button handler:
  ```javascript
  $('#btnReset').on('click', function() {
      $('#filterForm')[0].reset();
      datePickers.forEach(dp => dp.refresh());
      tableVar.ajax.reload();
  });
  ```

## 3. Excel Export Integration
- Always add an **Export Excel** button to listing views that passes active filter parameters to the export route:
  ```javascript
  $('#btnExport').on('click', function() {
      let params = new URLSearchParams({
          brand_id: $('#brand_id').val() || '',
          status: $('#status').val() || '',
          condition: $('#condition').val() || '',
          from_date: $('#from_date').val() || '',
          to_date: $('#to_date').val() || '',
          search: tableVar.search() || ''
      });
      window.location.href = "{{ route('mobiles.export') }}?" + params.toString();
  });
  ```
- In the Export class, ensure sales-specific fields (`Sold To`, `Sold Date`, `Sell Price`, `Profit`) are only filled if `status === 'sold'`. For in-stock items, return `N/A`.

## 4. Global Shortcuts
- Header HSN search shortcut: `Ctrl + K` / `Cmd + K`.
