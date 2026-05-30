@extends('layouts.app')
@section('title', 'Invoices')
@section('header_title', $header_title ?? 'Invoices')
@section('tagline', $tagline ?? 'View and manage sales invoices and customer payments.')

@section('pageCss')
    <link href="{{ asset('vendor-assets/libs/data-tables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="container">
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

    <script>
        let invoiceTable;

        $(document).ready(function() {
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
                        data: 'subtotal',
                        render: $.fn.dataTable.render.number(',', '.', 2, '₹')
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
								<button class="btn btn-sm btn-outline-primary" onclick="showPdfModal('${full.pdf_url}')"><i class="iconoir-page-star text-primary fs-18"></i></button>
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

        // Show PDF Download Modal
        function showPdfModal(pdfUrl) {
            Swal.fire({
                title: '<span class="text-primary fw-bold">Print / Download Options</span>',
                html: `
                    <div class="text-start p-2">
                        <p class="text-muted fs-13 mb-3">Choose what pages you want to include in the generated PDF document:</p>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="includeTandC" checked style="cursor: pointer;">
                            <label class="form-check-label fw-semibold text-dark fs-14 ms-2" for="includeTandC" style="cursor: pointer;">
                                Include Terms, Conditions & Disclaimer
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">If turned off, only the invoice page will be printed.</small>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<span class="d-inline-flex align-items-center"><i class="iconoir-download me-1"></i> Download PDF</span>',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-primary me-2',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false,
                preConfirm: () => {
                    return {
                        includeTandC: document.getElementById('includeTandC').checked
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const includeTandC = result.value.includeTandC;
                    const type = includeTandC ? 'both' : 'invoice';
                    const finalUrl = pdfUrl + (pdfUrl.includes('?') ? '&' : '?') + 'type=' + type;
                    window.open(finalUrl, '_blank');
                }
            });
        }
    </script>
@endsection
