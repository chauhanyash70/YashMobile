@extends('layouts.app')
@section('title', 'Edit Repair')
@section('header_title', $header_title ?? 'Edit Repair')
@section('tagline', $tagline ?? 'Modify repair details or update maintenance status.')

@section('pageCss')
    <link href="{{ asset('assets/css/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-0 py-3">
                        <h5 class="mb-0 fw-bold">Edit Repair Record</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('repairs.update', $repair) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label fw-bold">Select Mobile</label>
                                <select name="mobile_id" id="mobile_id" class="form-select" required>
                                    <option value="">Choose Device...</option>
                                    @foreach ($mobiles as $mobile)
                                        <option value="{{ $mobile->id }}"
                                            data-hsn="{{ $mobile->hsn_number }}"
                                            {{ $repair->mobile_id == $mobile->id ? 'selected' : '' }}>
                                            {{ $mobile->model->name }} ({{ $mobile->hsn_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Issue Reported</label>
                                <textarea name="issue" class="form-control" rows="2" placeholder="e.g. Screen Replacement" required>{{ $repair->issue }}</textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Repair Cost</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" name="repair_cost" class="form-control"
                                            value="{{ $repair->repair_cost }}" placeholder="0.00" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Repair Status</label>
                                    <select name="repair_status" class="form-select">
                                        <option value="pending" {{ $repair->repair_status == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="completed"
                                            {{ $repair->repair_status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled"
                                            {{ $repair->repair_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Technician Name</label>
                                <input type="text" name="technician_name" class="form-control"
                                    value="{{ $repair->technician_name }}" placeholder="Optional">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Repair Date</label>
                                <input type="date" name="repair_date" class="form-control"
                                    value="{{ $repair->repair_date }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Extra details...">{{ $repair->notes }}</textarea>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Update Repair Record</button>
                                <a href="{{ route('repairs.index') }}" class="btn btn-link">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="mt-3 text-center">
                    <form action="{{ route('repairs.destroy', $repair) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this repair record?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-link text-danger">Delete Record</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageScripts')
    <script src="{{ asset('assets/js/select2/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#mobile_id').select2({
                placeholder: "Choose Device...",
                allowClear: true,
                width: '100%',
                matcher: function(params, data) {
                    // If there are no search terms, return all of the data
                    if ($.trim(params.term) === '') {
                        return data;
                    }

                    // Do not display the item if there is no 'text' property
                    if (typeof data.text === 'undefined') {
                        return null;
                    }

                    var searchTerm = params.term.toLowerCase();
                    var optionText = data.text.toLowerCase();
                    var hsn = $(data.element).data('hsn') ? $(data.element).data('hsn').toString().toLowerCase() : '';

                    // Match if search term is in option text or HSN number
                    if (optionText.indexOf(searchTerm) > -1 || hsn.indexOf(searchTerm) > -1) {
                        return data;
                    }

                    return null;
                }
            });
        });
    </script>
@endsection
