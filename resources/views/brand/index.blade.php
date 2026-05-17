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
                        <h4 class="card-title">Brands</h4>
                        <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                            Add Brand
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table datatable" id="brandDatatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Type</th>
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

    <!-- Brand Modal -->
    <div class="modal fade" id="brandModal" tabindex="-1" aria-labelledby="brandModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="brandForm" method="POST">
                    @csrf
                    <div id="methodField"></div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="brandModalLabel">Add Brand</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="device">Device</option>
                                <option value="accessory">Accessory</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                        <input type="hidden" id="slug" name="slug">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('pageScripts')
    <script src="{{ asset('vendor-assets/libs/data-tables/datatables.min.js') }}"></script>

    <script>
        const getBrandDataUrl = "{{ route('getBrandData') }}";
        const brandStoreUrl = "{{ route('brands.store') }}";
        const brandBaseUrl = "/brands";
    </script>
    <script src="{{ asset('vendor-assets/js/pages/brand/index.js') }}"></script>
@endsection
