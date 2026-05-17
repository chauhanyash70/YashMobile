@extends('layouts.app')

@section('pageCss')
    <link href="{{ asset('vendor-assets/libs/data-tables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Invoices</h4>
                        <a href="{{ route('invoice.create') }}" class="btn btn-primary btn-sm">Create Invoice</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table datatable" id="invoiceDatatable">
                                <thead>
                                    <tr>
                                        <th>Invoice No</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Total Amount</th>
                                        <th>Paid</th>
                                        <th>Due</th>
                                        <th>Payment Method</th>
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
        let invoiceTable;

        $(document).ready(function () {
            invoiceTable = $('#invoiceDatatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('invoice.getData') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    }
                },
                order: [
                    [0, 'desc']
                ],
                columns: [{
                    data: 'invoice_no'
                },
                {
                    data: 'invoice_date'
                },
                {
                    data: 'customer_name'
                },
                {
                    data: 'total_amount',
                    render: $.fn.dataTable.render.number(',', '.', 2, '₹')
                },
                {
                    data: 'paid_amount',
                    render: $.fn.dataTable.render.number(',', '.', 2, '₹')
                },
                {
                    data: 'due_amount',
                    render: $.fn.dataTable.render.number(',', '.', 2, '₹')
                },
                {
                    data: 'payment_method'
                },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false
                }
                ],
                columnDefs: [{
                    width: "200px",
                    targets: -1,
                    render: function (data, type, full, meta) {
                        return `
                                                                                                <a href="${full.show_url}" class="btn btn-sm btn-outline-secondary"><i class="iconoir-page text-secondary fs-18"></i></a>
                                                                                                <a href="${full.edit_url}" class="btn btn-sm btn-outline-info"><i class="iconoir-edit text-info fs-18"></i></a>
                                                                                                <a href="${full.pdf_url}" class="btn btn-sm btn-outline-primary"><i class="iconoir-page-star text-primary fs-18"></i></a>
                                                                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice(${full.id})"><i class="iconoir-trash text-danger fs-18"></i></button>
                                                                                            `;
                    }
                }]
            });
        });

        // Delete Invoice
        function deleteInvoice(id) {

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('invoice.destroy', ':id') }}'.replace(':id', id),
                        type: 'POST',
                        data: {
                            _token: csrfToken,
                            _method: 'POST'
                        },
                        success: function (res) {
                            invoiceTable.ajax.reload();
                            toastr.success(res.message);
                        },
                        error: function (xhr) {
                            let errorMessage = 'Error deleting invoice!';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire(
                                'Error!',
                                errorMessage,
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
@endsection