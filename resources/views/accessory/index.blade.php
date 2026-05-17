@extends('layouts.app')
@section('title', 'Accessories')
@section('header_title', $header_title ?? 'Accessories')
@section('tagline', $tagline ?? 'Manage your inventory and accessory stock.')

@section('pageCss')
<link href="{{ asset('vendor-assets/libs/data-tables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-xxl">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Accessories</h4>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('accessories.create') }}" class="btn btn-primary btn-sm px-2">
                                Add New Accessory
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table datatable" id="accessoryDatatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th class="text-start">Name</th>
                                    <th>HSN</th>
                                    <th>Stock</th>
                                    <th>Buy Price</th>
                                    <th>Sell Price</th>
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
var tableVar = $('#accessoryDatatable').DataTable({
    searchDelay: 500,
    processing: true,
    serverSide: true,
    responsive: true,
    order: [[0, "desc"]],

    ajax: {
        url: "{{ route('getAccessoryData') }}",
        type: "POST",
        data: { _token: csrfToken },
        beforeSend: function () {
            if (tableVar != null && tableVar.settings()[0].jqXHR) {
                tableVar.settings()[0].jqXHR.abort();
            }
        },
    },

    columns: [
        { data: "" },
        { data: "name", sClass: "text-start" },
        { data: "hsn" },
        { data: "stock" },
        { data: "purchase_price" },
        { data: "sale_price" },
        { data: "actions", sClass: "text-end" },
    ],

    columnDefs: [
        {
            className: "control",
            orderable: false,
            targets: 0,
            searchable: false,
            render: function () { return "" }
        },
        {
            width: "150px",
            targets: -1,
            title: "Actions",
            orderable: false,
            responsivePriority: -1,
            render: function (data, type, full) {
                return `
                    <a href="${full.edit_url}" class="btn btn-sm btn-outline-info">
                        <i class="iconoir-edit-pencil text-info fs-18"></i>
                    </a>

                    <form action="${full.delete_url}" method="POST" 
                        class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this accessory?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="iconoir-trash text-danger fs-18"></i>
                        </button>
                    </form>
                `;
            }
        }
    ],

    responsive: {
        details: {
            display: $.fn.dataTable.Responsive.display.modal({
                header: function (row) {
                    return "Details of " + row.data().name;
                }
            }),
            type: "column",
            renderer: function (api, rowIdx, columns) {
                var data = $.map(columns, function (col) {
                    return col.title
                        ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                              <td>${col.title}:</td>
                              <td>${col.data}</td>
                           </tr>`
                        : "";
                }).join("");

                return data
                    ? $('<table class="table"/><tbody />').append(data)
                    : false;
            }
        }
    }
});
</script>
@endsection
