@extends('layouts.app')
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
                                <h4 class="card-title">{{ $title }}</h4>
                            </div>
                            <div class="col-auto">
                                {{-- <a href="{{ route('admin.invoice.create') }}"
									class="btn btn-primary btn-sm px-2 mt-2 mt-md-0 ">Create Invoice</a> --}}
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table datatable" id="purchaseDatatable">
                                <thead class="">
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Supplier</th>
                                        <th>Total</th>
                                        <th>Paid</th>
                                        <th>Due</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody> </tbody>
                            </table>
                            {{-- <button type="button" class="btn btn-sm btn-primary csv">Export CSV</button>
						<button type="button" class="btn btn-sm btn-primary sql">Export SQL</button>
						<button type="button" class="btn btn-sm btn-primary txt">Export TXT</button>
						<button type="button" class="btn btn-sm btn-primary json">Export JSON</button> --}}
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
        var tableVar = $('#purchaseDatatable').DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            responsive: true,
            order: [
                [0, "desc"]
            ],
            ajax: {
                url: "{{ route('getPurchaseData') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                },
            },
            columns: [{
                    data: "id"
                },
                {
                    data: "purchase_date"
                },
                {
                    data: "supplier_name"
                },
                {
                    data: "total_amount"
                },
                {
                    data: "paid_amount"
                },
                {
                    data: "due_amount"
                },
                {
                    data: "actions",
                    sClass: "text-end"
                }
            ],
            columnDefs: [{
                width: "100px",
                targets: -1,
                title: "Actions",
                orderable: false,
                render: function(data, type, full, meta) {
                    return '<a href="' + full.show_url +
                        '" class="btn btn-sm btn-outline-secondary"><i class="iconoir-page text-secondary fs-18"></i></a>';
                },
            }, ],
        });
    </script>
@endsection
