@extends('layouts.app')
@section('title', 'Available Mobiles')
@section('header_title', $header_title ?? 'Available Mobiles')
@section('tagline', $tagline ?? 'View and manage all mobile devices currently available.')

@section('pageCss')
    <link href="{{ asset('vendor-assets/libs/data-tables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="container-xxl">
        <!-- Filter Card -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0 shadow-sm">
                    <div class="card-body">
                        <form id="filterForm" class="row g-3 align-items-end">
                            <div class="col-xl-3 col-md-6">
                                <label for="brand_id" class="form-label fw-semibold">Brand</label>
                                <select class="form-select" id="brand_id" name="brand_id">
                                    <option value="">All Brands</option>
                                    @if(isset($brands))
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <label for="condition" class="form-label fw-semibold">Condition</label>
                                <select class="form-select" id="condition" name="condition">
                                    <option value="">All Conditions</option>
                                    <option value="used">Used</option>
                                    <option value="new">New</option>
                                    <option value="refurbished">Refurbished</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-md-4 col-sm-6">
                                <label for="from_date" class="form-label fw-semibold">From Date</label>
                                <input type="text" class="form-control date-picker" id="from_date" name="from_date" placeholder="YYYY-MM-DD" autocomplete="off">
                            </div>
                            <div class="col-xl-2 col-md-4 col-sm-6">
                                <label for="to_date" class="form-label fw-semibold">To Date</label>
                                <input type="text" class="form-control date-picker" id="to_date" name="to_date" placeholder="YYYY-MM-DD" autocomplete="off">
                            </div>
                            <div class="col-xl-2 col-md-4 col-sm-12 d-flex gap-2">
                                <button type="button" id="btnFilter" class="btn btn-primary flex-grow-1">
                                    <i class="iconoir-filter me-1"></i> Filter
                                </button>
                                <button type="button" id="btnReset" class="btn btn-outline-secondary flex-grow-1">
                                    <i class="iconoir-refresh me-1"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('Available Mobiles') }}</h5>
                        <div class="d-flex gap-2">
                            <button type="button" id="btnExport" class="btn btn-success btn-sm">
                                <i class="iconoir-table me-1"></i> Export Excel
                            </button>
                            <a href="{{ route('mobiles.create') }}" class="btn btn-primary btn-sm">
                                <i class="iconoir-plus-circle me-1"></i> Add New Mobile
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table datatable" id="availableDeviceDatatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Brand</th>
                                        <th>Model</th>
                                        <th>Specifications</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageScripts')
    <script src="{{ asset('vendor-assets/libs/data-tables/datatables.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>
    <script>
        // Initialize Datepickers
        var datePickers = [];
        document.querySelectorAll('.date-picker').forEach(el => {
            datePickers.push(new Datepicker(el, {
                autoHide: true,
                format: 'yyyy-mm-dd',
            }));
        });

        var tableVar = $('#availableDeviceDatatable').DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            responsive: true,
            order: [
                [0, "desc"]
            ],
            ajax: {
                url: "{{ route('mobiles.getAvailableData') }}",
                type: "POST",
                data: function(d) {
                    d._token = csrfToken;
                    d.brand_id = $('#brand_id').val();
                    d.condition = $('#condition').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                },
                beforeSend: function () {
                    if (tableVar != null) {
                        tableVar.settings()[0].jqXHR.abort();
                    }
                },
            },
            language: {
                searchPlaceholder: "Search mobiles or IMEIs...",
                search: ""
            },

            columns: [{
                data: ""
            },
            {
                data: "brand"
            },
            {
                data: "model"
            },
            {
                data: "specs"
            },
            {
                data: "actions",
                className: "text-end"
            },
            ],

            columnDefs: [{
                className: "control",
                orderable: false,
                targets: 0,
                searchable: false,
                render: function () {
                    return "";
                }
            },
            {
                targets: 2,
                width: "150px",
                render: function (data, type, full) {
                    return `<div>
                                ${data} <br>
                                <small>HSN Number: ${full.hsn_number_val}</small>
                            </div>`;
                }
            },
            {
                targets: -1,
                title: "Actions",
                orderable: false,
                width: "150px",
                render: function (data, type, full) {
                    let html = '';

                    // View button (always)
                    html += `
                            <a href="${full.show_url}" class="btn btn-sm btn-outline-primary me-1" title="View Details">
                                <i class="iconoir-eye text-primary fs-18"></i>
                            </a>
                        `;

                    // Edit button (always)
                    html += `
                            <a href="${full.edit_url}" class="btn btn-sm btn-outline-info me-1" title="Edit Mobile">
                                <i class="iconoir-edit-pencil text-info fs-18"></i>
                            </a>
                        `;

                    // Delete only if no invoice items
                    if (full.invoice_items_count === 0) {
                        html += `
                                <form action="${full.delete_url}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this device? This will remove all associated stock.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Mobile">
                                        <i class="iconoir-trash text-danger fs-18"></i>
                                    </button>
                                </form>
                            `;
                    }

                    return html;
                }

            }
            ],

            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            return "Device Details: " + row.data().model;
                        }
                    }),
                    type: "column",
                    renderer: function (api, rowIdx, columns) {
                        return $('<table class="table"/>').append(
                            $.map(columns, function (col) {
                                return col.title ?
                                    '<tr><td>' + col.title + ':</td><td>' + col.data + '</td></tr>' :
                                    '';
                            }).join("")
                        );
                    }
                }
            }
        });

        // Filter button click
        $('#btnFilter').on('click', function() {
            tableVar.ajax.reload();
        });

        // Reset button click
        $('#btnReset').on('click', function() {
            $('#brand_id').val('');
            $('#condition').val('');
            $('#from_date').val('');
            $('#to_date').val('');
            datePickers.forEach(dp => dp.refresh());
            tableVar.ajax.reload();
        });

        // Export Excel button click
        $('#btnExport').on('click', function() {
            let params = new URLSearchParams({
                brand_id: $('#brand_id').val() || '',
                condition: $('#condition').val() || '',
                from_date: $('#from_date').val() || '',
                to_date: $('#to_date').val() || '',
                search: tableVar.search() || ''
            });
            window.location.href = "{{ route('mobiles.exportAvailable') }}?" + params.toString();
        });
    </script>
@endsection
