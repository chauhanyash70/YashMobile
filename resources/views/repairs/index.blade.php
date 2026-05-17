@extends('layouts.app')
@section('title', 'Repairs')
@section('header_title', $header_title ?? 'Repairs')
@section('tagline', $tagline ?? 'Manage device repairs and maintenance costs.')

@section('pageCss')
    <link href="{{ asset('vendor-assets/libs/data-tables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                @if (request('mobile_id') && $repairs->count() > 0)
                    <div class="alert alert-info border-0 shadow-sm mb-4 d-flex justify-content-between align-items-center">
                        <span>Total Repair Cost for this device:</span>
                        <span class="fw-bold fs-5">₹{{ number_format($repairs->sum('repair_cost'), 2) }}</span>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0">Repair Management</h4>
                            @if (request('mobile_id'))
                                <p class="text-muted mb-0 small mt-1">Filtering by Device ID: {{ request('mobile_id') }} <a
                                        href="{{ route('repairs.index') }}" class="text-danger ms-2">Clear Filter</a></p>
                            @endif
                        </div>
                        <a href="{{ route('repairs.create', ['mobile_id' => request('mobile_id')]) }}"
                            class="btn btn-primary">
                            Log New Repair
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table datatable" id="repairsDatatable">
                                <thead>
                                    <tr>
                                        <th>Device</th>
                                        <th>Issue</th>
                                        <th>Technician</th>
                                        <th>Cost</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer border-0 py-3">
                        <div class="d-flex justify-content-center">
                            {{ $repairs->appends(request()->query())->links() }}
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
        $(document).ready(function() {
            $('#repairsDatatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('getRepairData') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    }
                },
                columns: [{
                        data: 'device',
                        name: 'device',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'issue',
                        name: 'issue'
                    },
                    {
                        data: 'technician',
                        name: 'technician'
                    },
                    {
                        data: 'cost',
                        name: 'cost'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will remove the repair record and may update the device status.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                customClass: {
                    confirmButton: 'btn btn-primary shadow-0',
                    cancelButton: 'btn btn-danger shadow-0'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
@endsection
