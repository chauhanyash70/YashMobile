@extends('layouts.app')
@section('title', 'Invoices')
@section('header_title', $header_title ?? 'Invoices')
@section('tagline', $tagline ?? 'View and manage sales invoices and customer payments.')

@section('pageCss')
    <link href="{{ asset('vendor-assets/libs/data-tables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="container">
        <!-- Filter Card -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card mb-0">
                    <div class="card-body">
                        <form id="filterForm" class="row g-3 align-items-end">
                            <div class="col-md-3 col-sm-6">
                                <label for="from_date" class="form-label fw-semibold">From Date</label>
                                <input type="text" class="form-control date-picker" id="from_date" name="from_date" placeholder="YYYY-MM-DD" autocomplete="off">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label for="to_date" class="form-label fw-semibold">To Date</label>
                                <input type="text" class="form-control date-picker" id="to_date" name="to_date" placeholder="YYYY-MM-DD" autocomplete="off">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label for="payment_method" class="form-label fw-semibold">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="">All Payment Methods</option>
                                    <option value="cash">Cash</option>
                                    <option value="upi">UPI</option>
                                    <option value="card">Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="credit">Credit</option>
                                    <option value="bajaj_finance">Bajaj Finance</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6 d-flex gap-2">
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
                                        <th>Subtotal</th>
                                        <th>Total Amount</th>
                                        <th>Paid</th>
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
    <script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>

    <script>
        let invoiceTable;
        let datePickers = [];

        $(document).ready(function() {
            // Initialize Vanillajs Datepickers
            document.querySelectorAll('.date-picker').forEach(el => {
                let dp = new Datepicker(el, {
                    autoHide: true,
                    format: 'yyyy-mm-dd',
                });
                datePickers.push(dp);
            });

            invoiceTable = $('#invoiceDatatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('invoice.getData') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = "{{ csrf_token() }}";
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                        d.payment_method = $('#payment_method').val();
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
                        data: 'subtotal',
                        render: $.fn.dataTable.render.inr()
                    },
                    {
                        data: 'total_amount',
                        render: $.fn.dataTable.render.inr()
                    },
                    {
                        data: 'paid_amount',
                        render: $.fn.dataTable.render.inr()
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
                    render: function(data, type, full, meta) {
                        return `
								<a href="${full.show_url}" class="btn btn-sm btn-outline-secondary"><i class="iconoir-page text-secondary fs-18"></i></a>
								<a href="${full.edit_url}" class="btn btn-sm btn-outline-info"><i class="iconoir-edit text-info fs-18"></i></a>
								<a href="${full.pdf_url}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="iconoir-page-star text-primary fs-18"></i></a>
								<button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice(${full.id})"><i class="iconoir-trash text-danger fs-18"></i></button>
							`;
                    }
                }]
            });

            // Filter button click
            $('#btnFilter').on('click', function() {
                invoiceTable.ajax.reload();
            });

            // Reset button click
            $('#btnReset').on('click', function() {
                $('#from_date').val('');
                $('#to_date').val('');
                $('#payment_method').val('');
                datePickers.forEach(dp => dp.refresh());
                invoiceTable.ajax.reload();
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
                        success: function(res) {
                            invoiceTable.ajax.reload();
                            toastr.success(res.message);
                        },
                        error: function(xhr) {
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
