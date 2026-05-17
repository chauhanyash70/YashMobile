@extends('layouts.app')
@section('title', 'Add Device')
@section('header_title', $header_title ?? 'Add New Device')
@section('tagline', $tagline ?? 'Register a new mobile unit in the inventory with full specifications.')


@section('pageCss')
    <link href="{{ asset('assets/css/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet"
        type="text/css" />
@endsection

@section('content')
    <div class="container-xxl">
        <form action="{{ route('mobiles.store') }}" method="POST" id="deviceCreateForm">
            @csrf

            <div class="row">
                {{-- Device Specifications --}}
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Add New Device (Specifications)</h5>
                            <div class="d-flex gap-2">
                                <a href="{{ route('mobiles.index') }}" class="btn btn-secondary btn-sm">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label for="brand_id" class="form-label">Brand<span class="text-danger">*</span></label>
                                    <select name="brand_id" id="brand_id" class="form-select" required>
                                        <option value="">Select Brand</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}" data-slug="{{ $brand->slug }}">
                                                {{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="model_name" class="form-label">Model Name<span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="model_name" name="model_name"
                                        placeholder="e.g. iPhone 13" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="storage" class="form-label">Storage<span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="storage" name="storage"
                                        placeholder="128GB" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="ram" class="form-label">RAM<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ram" name="ram"
                                        placeholder="8GB" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="color" class="form-label">Color<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="color" name="color"
                                        placeholder="Black" required>
                                </div>
                                <div class="col-md-2 mb-3" id="battery_health_container" style="display: none;">
                                    <label for="battery_health" class="form-label">Battery Health (%)</label>
                                    <input type="text" class="form-control" id="battery_health" name="battery_health"
                                        placeholder="e.g. 95%">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="condition" class="form-label">Condition<span
                                            class="text-danger">*</span></label>
                                    <select name="condition" id="condition" class="form-select">
                                        <option value="used">Used</option>
                                        <option value="new">New</option>
                                        <option value="refurbished">Refurbished</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-3">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="iconoir-phone-vibration me-2"></i>
                            Unit Details
                        </h5>
                    </div>

                    <div id="unit-details">
                        <div class="card mb-3 unit-card shadow-sm">
                            <div class="card-header unit-header d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">Unit Configuration</span>
                                <span class="badge bg-success">New Entry</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">HSN Number <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="hsn_number" class="form-control fw-bold imei-field"
                                            placeholder="Enter HSN Number" value="{{ $prefilledHsn ?? '' }}" required
                                            autofocus>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Buy Price (₹) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="buy_price"
                                            class="form-control buy-price-field" placeholder="0.00" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Purchase Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="purchase_date" class="form-control"
                                            value="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-1">
                                    </div>
                                    <div class="col-12">
                                        <h6 class="small fw-bold">Supplier Details</h6>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Supplier Phone <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="supplier_phone" class="form-control supplier-phone"
                                            placeholder="Lookup phone..." required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Supplier Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="supplier_name" class="form-control supplier-name"
                                            required placeholder="Supplier Name">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Supplier Address <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="supplier_address"
                                            class="form-control supplier-address" placeholder="Supplier Address" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3 mb-5">
                    <div class="card p-3 shadow-sm bg-primary-subtle border-primary">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-primary fw-semibold">Note: Ensure all specifications and supplier details are
                                correct.</span>
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="iconoir-check-circle me-1"></i>Save Device
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('pageScripts')
    <script src="{{ asset('vendor-assets/libs/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/libs/jquery-validation/additional-methods.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>
    <script>
        var supplierSearchUrl = "{{ route('invoice.getCustomer') }}";
        var csrfToken = "{{ csrf_token() }}";

        $(document).ready(function() {
            $('#brand_id').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var slug = selectedOption.data('slug');
                if (slug === 'apple') {
                    $('#battery_health_container').show();
                } else {
                    $('#battery_health_container').hide();
                    $('#battery_health').val('');
                }
            });

            // Trigger on load if brand is already selected
            $('#brand_id').trigger('change');
        });
    </script>
    <script src="{{ asset('vendor-assets/js/pages/device/create.js') }}"></script>
@endsection
