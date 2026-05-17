@extends('layouts.app')
@section('title', 'Transactions')
@section('header_title', $header_title ?? 'Transactions')
@section('tagline', $tagline ?? 'Track all buy, sell, and repair transactions.')

@section('pageCss')
    <link href="{{ asset('vendor-assets/libs/data-tables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Transaction History</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table datatable" id="transactionDatatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Customer/Supplier</th>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Payment</th>
                                        <th>Invoice No</th>
                                        <th>Actions</th>
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
        let transactionTable;

        $(document).ready(function() {
            transactionTable = $('#transactionDatatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('transactions.getData') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    }
                },
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'transaction_date'
                    },
                    {
                        data: 'transaction_type'
                    },
                    {
                        data: 'customer_name'
                    },
                    {
                        data: 'item'
                    },
                    {
                        data: 'price',
                        render: $.fn.dataTable.render.number(',', '.', 2, '₹')
                    },
                    {
                        data: 'discount',
                        render: $.fn.dataTable.render.number(',', '.', 2, '₹')
                    },
                    {
                        data: 'payment_method'
                    },
                    {
                        data: 'invoice_no'
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                columnDefs: [{
                    targets: -1,
                    render: function(data, type, full, meta) {
                        return `
                                <a href="${full.show_url}" class="btn btn-sm btn-outline-primary">
                                    <i class="iconoir-eye text-primary fs-18"></i>
                                </a>
                               {{--  <button class="btn btn-sm btn-outline-danger" onclick="deleteTransaction(${full.id})">
                                    <i class="iconoir-trash text-danger fs-18"></i>
                                </button> --}}
                            `;
                    }
                }]
            });
        });

        function deleteTransaction(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will only delete the transaction record!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('transactions.destroy', ':id') }}'.replace(':id', id),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(res) {
                            transactionTable.ajax.reload();
                            if (res.success) {
                                toastr.success(res.message);
                            } else {
                                toastr.error(res.message);
                            }
                        }
                    });
                }
            });
        }
    </script>
@endsection
