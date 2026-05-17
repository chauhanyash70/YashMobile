@extends('layouts.app')

@section('pageCss')
    <link href="{{ asset('vendor-assets/libs/data-tables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('Mobiles / Devices') }}</h5>
                        <a href="{{ route('mobiles.create') }}" class="btn btn-primary btn-sm">Add New Device</a>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table datatable" id="deviceDatatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Brand</th>
                                        <th>Model</th>
                                        <th>Specs</th>
                                        <th>Status</th>
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
    <script>
        var tableVar = $('#deviceDatatable').DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            responsive: true,
            order: [
                [0, "desc"]
            ],
            ajax: {
                url: "{{ route('getMobileData') }}",
                type: "POST",
                data: {
                    _token: csrfToken
                },
                beforeSend: function () {
                    if (tableVar != null) {
                        tableVar.settings()[0].jqXHR.abort();
                    }
                },
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
                data: "status"
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
                                            <small>IMEI: ${full.imei_count}</small>
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
                            <a href="${full.show_url}" class="btn btn-sm btn-outline-primary">
                                <i class="iconoir-eye text-primary fs-18"></i>
                            </a>
                        `;

                    // Edit button (always)
                    html += `
                            <a href="${full.edit_url}" class="btn btn-sm btn-outline-info">
                                <i class="iconoir-edit-pencil text-info fs-18"></i>
                            </a>
                        `;

                    // Delete only if no invoice items
                    if (full.invoice_items_count === 0) {
                        html += `
                                <form action="${full.delete_url}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this device? This will remove all associated stock.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
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
    </script>
@endsection