@extends('layouts.app')
@section('title', 'Edit Device')
@section('header_title', $header_title ?? 'Edit Device')
@section('tagline', $tagline ?? 'Modify device specifications or supplier information for this unit.')


@section('pageCss')
    <link href="{{ asset('assets/css/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet"
        type="text/css" />
@endsection

@section('content')
    <div class="container-xxl">
        <form action="{{ route('mobiles.update', $device->id) }}" method="POST" id="deviceEditForm">
            @csrf
            @method('PUT')

            <div class="row">
                {{-- Device Specifications --}}
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Device Specifications</h5>
                            <a href="{{ route('mobiles.index') }}" class="btn btn-secondary btn-sm">Back</a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="brand_id" class="form-label">Brand<span class="text-danger">*</span></label>
                                    <select name="brand_id" id="brand_id"
                                        class="form-select @error('brand_id') is-invalid @enderror" required>
                                        <option value="">Select Brand</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}" data-slug="{{ $brand->slug }}"
                                                {{ (old('brand_id') ?? $device->brand_id) == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="model_name" class="form-label">Model Name<span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('model_name') is-invalid @enderror"
                                        id="model_name" name="model_name"
                                        value="{{ old('model_name', $device->model->name ?? '') }}"
                                        placeholder="e.g. iPhone 13" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="storage" class="form-label">Storage<span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="storage" name="storage"
                                        value="{{ old('storage', $device->storage) }}" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="ram" class="form-label">RAM<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ram" name="ram"
                                        value="{{ old('ram', $device->ram) }}" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="color" class="form-label">Color<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="color" name="color"
                                        value="{{ old('color', $device->color) }}" required>
                                </div>
                                <div class="col-md-2 mb-3" id="battery_health_container" style="display: none;">
                                    <label for="battery_health" class="form-label">Battery Health (%)</label>
                                    <input type="text" class="form-control" id="battery_health" name="battery_health"
                                        value="{{ old('battery_health', $device->battery_health) }}"
                                        placeholder="e.g. 95%">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="condition" class="form-label">Condition<span
                                            class="text-danger">*</span></label>
                                    <select name="condition" id="condition" class="form-select">
                                        <option value="used"
                                            {{ (old('condition') ?? $device->condition_type) == 'used' ? 'selected' : '' }}>
                                            Used</option>
                                        <option value="new"
                                            {{ (old('condition') ?? $device->condition_type) == 'new' ? 'selected' : '' }}>
                                            New</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Unit Management (HSN Wise) --}}
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="iconoir-phone-vibration me-2"></i>
                            Unit Details
                        </h5>
                    </div>

                    <div id="units-container">
                        @php
                            $isSold = $device->status == 'sold';
                            $purchaseTransaction = $device->purchaseTransaction;
                            $customer = ($isSold && isset($saleTransaction) && $saleTransaction->customer) 
                                        ? $saleTransaction->customer 
                                        : ($purchaseTransaction->customer ?? null);
                            $sectionTitle = $isSold ? 'Customer Details (Sold To)' : 'Supplier Details (Bought From)';
                            $labelPrefix = $isSold ? 'Customer' : 'Supplier';
                            $priceLabel = $isSold ? 'Sell Price (₹)' : 'Buy Price (₹)';
                            $dateLabel = $isSold ? 'Sale Date' : 'Purchase Date';
                            
                            $displayTransaction = $isSold ? ($saleTransaction ?? $purchaseTransaction) : $purchaseTransaction;
                        @endphp
                        <div class="card mb-3 unit-card" data-index="0">
                            <div class="card-header unit-header d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">Unit Configuration</span>
                                <div>
                                    @if ($device->status == 'sold')
                                        <span class="badge bg-danger">Sold</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">HSN Number <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="hsn_number" class="form-control fw-bold imei-field"
                                            value="{{ $device->hsn_number }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">{{ $priceLabel }} <span
                                                class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="buy_price"
                                            class="form-control buy-price-field" value="{{ number_format($displayTransaction->price ?? 0, 2, '.', '') }}"
                                            required {{ $isSold ? 'readonly' : '' }}>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">{{ $dateLabel }} <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="purchase_date" class="form-control"
                                            value="{{ $displayTransaction ? \Carbon\Carbon::parse($displayTransaction->transaction_date)->format('Y-m-d') : date('Y-m-d') }}"
                                            required {{ $isSold ? 'readonly' : '' }}>
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-1">
                                    </div>
                                    <div class="col-12">
                                        <h6 class="small fw-bold">{{ $sectionTitle }}</h6>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">{{ $labelPrefix }} Phone <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="supplier_phone" class="form-control supplier-phone"
                                            value="{{ $customer->phone ?? '' }}" required {{ $isSold ? 'readonly' : '' }}>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">{{ $labelPrefix }} Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="supplier_name" class="form-control supplier-name"
                                            value="{{ $customer->name ?? '' }}" required {{ $isSold ? 'readonly' : '' }}>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">{{ $labelPrefix }} Address <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="supplier_address"
                                            class="form-control supplier-address" value="{{ $customer->address ?? '' }}"
                                            required {{ $isSold ? 'readonly' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3 mb-5">
                    <div class="card p-3 shadow-sm bg-primary-subtle border-primary">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-primary fw-semibold">Note: Changes will apply to this unit and its
                                history.</span>
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="iconoir-check-circle me-1"></i>Save All Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('pageScripts')
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
    <script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/js/pages/device/edit.js') }}"></script>

@endsection
