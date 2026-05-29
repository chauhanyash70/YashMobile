@extends('layouts.app')
@section('title', 'Customers')
@section('header_title', $header_title ?? 'Customers')
@section('tagline', $tagline ?? 'View and manage your customer list and their purchase history.')
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
								<a href="{{ route('customers.export') }}"
									class="btn btn-outline-success btn-sm px-3 mt-2 mt-md-0 d-flex align-items-center gap-1">
									<i class="iconoir-download fs-14"></i>
									Export Customers
								</a>
							</div>
						</div>
					</div>
					<div class="card-body pt-0">
						<div class="table-responsive">
							<table class="table datatable" id="customerDatatable">
								<thead class="">
									<tr>
										<th>#</th>
										<th class="text-start">Name</th>
										<th>Phone</th>
										<th>address</th>
										<th>Created At</th>
										<th></th>
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
		var tableVar = $('#customerDatatable').DataTable({
			searchDelay: 500,
			processing: true,
			serverSide: true,
			responsive: true,
			order: [
				[0, "desc"]
			],
			fnDrawCallback: function() {

			},
			ajax: {
				url: "{{ route('getCustomerData') }}",
				type: "POST",
				data: {
					_token: csrfToken,
				},
				beforeSend: function() {
					if (tableVar != null) {
						tableVar.settings()[0].jqXHR.abort();
					}
				},
				error: function(jqXHR, ajaxOptions, thrownError) {
					if (jqXHR.status == 419) {
						// sessionExpire();
					}
				},
			},
			columns: [{
					data: ""
				},
				{
					data: "name",
					sClass: "text-start",
				},
				{
					data: "phone",
				},
				{
					data: "address",
				},
				{
					data: "created_at",
				},
				{
					data: "actions",
					sClass: "text-end",
				},
			],
			columnDefs: [{
					className: "control",
					orderable: !1,
					targets: 0,
					searchable: !1,
					render: function(t, a, e, l) {
						return ""
					}
				},
                {
                    targets: 1,
                    render: function(data, type, full, meta) {
                        return `
                            <div class="d-flex align-items-center">
                                <img src="${full.profile_url}" alt="" class="rounded-circle me-2" height="30" width="30" style="object-fit: cover;">
                                <span>${data}</span>
                            </div>
                        `;
                    }
                },
				{
					width: "150px",
					targets: -1,
					title: "Actions",
					orderable: false,
					responsivePriority: -1,
					render: function (data, type, full, meta) {
						return `
							<a href="${full.details_url}" class="btn btn-sm btn-outline-secondary">
								<i class="iconoir-page text-secondary fs-18"></i>
							</a>

							<a href="${full.edit_url}" class="btn btn-sm btn-outline-info">
								<i class="iconoir-page-edit text-info fs-18"></i>
							</a>

							<a href="javascript:void(0);"
							onclick="deleteCustomer(${full.id})"
							class="btn btn-sm btn-outline-danger">
								<i class="iconoir-trash fs-18"></i>
							</a>
						`;
					},

				},
			],
			responsive: {
				details: {
					display: $.fn.dataTable.Responsive.display.modal({
						header: function(e) {
							return "Details of " + e.data().name
						}
					}),
					type: "column",
					renderer: function(e, a, t) {
						t = $.map(t, function(e, a) {
							return "" !== e.title ? '<tr data-dt-row="' + e.rowIndex +
								'" data-dt-column="' + e.columnIndex + '"><td>' + e.title +
								":</td> <td>" + e.data + "</td></tr>" : ""
						}).join("");
						return !!t && $('<table class="table"/><tbody />').append(t)
					}
				}
			},
		});

		    window.deleteCustomer = function(id) {
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
                   url: '{{ route("customers.destroy", ":id") }}'.replace(':id', id),
                    type: 'POST',
                    data: { 
                        _token: csrfToken,
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        tableVar.ajax.reload();
                        Swal.fire(
                            'Deleted!',
                            res.message || 'Customer has been deleted.',
                            'success'
                        );
                    },
                    error: function(xhr) {
                        let errorMessage = 'Error deleting customer!';
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
    };
	</script>
@endsection
