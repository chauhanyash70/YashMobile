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
                                    <label for="ram" class="form-label">RAM<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ram" name="ram"
                                        value="{{ old('ram', $device->ram) }}" required>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="storage" class="form-label">Storage<span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="storage" name="storage"
                                        value="{{ old('storage', $device->storage) }}" required>
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
                        @foreach ($devices as $index => $d)
                            @php
                                $isSold = $d->status == 'sold';
                                $purchaseTransaction = $d->purchaseTransaction;
                                $supplier = $purchaseTransaction->customer ?? null;
                                
                                $saleCust = null;
                                if ($isSold && isset($d->saleTransaction)) {
                                    $saleCust = $d->saleTransaction->customer ?? null;
                                }
                            @endphp
                            <div class="card mb-4 unit-card shadow-sm border border-light" data-index="{{ $index }}">
                                <div class="card-header unit-header d-flex justify-content-between align-items-center bg-light-subtle py-3 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; line-height: 28px;">
                                            <span class="fw-bold fs-12">{{ $devices->count() - $index }}</span>
                                        </div>
                                        <span class="fw-bold text-dark fs-15">Lifecycle Cycle #{{ $devices->count() - $index }}</span>
                                    </div>
                                    <div>
                                        @if ($d->status == 'sold')
                                            <span class="badge bg-danger-subtle text-danger px-3 py-1.5 rounded-pill fs-11 border border-danger">Sold</span>
                                        @elseif ($d->status == 'in_stock')
                                            <span class="badge bg-success-subtle text-success px-3 py-1.5 rounded-pill fs-11 border border-success">In Stock</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning px-3 py-1.5 rounded-pill fs-11 border border-warning">{{ ucfirst($d->status) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <input type="hidden" name="units[{{ $index }}][id]" value="{{ $d->id }}">
                                    
                                    {{-- HSN / Serial Number --}}
                                    <div class="mb-4">
                                        <label class="form-label small text-muted fw-bold text-uppercase tracking-wider">HSN / Serial Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted"><i class="iconoir-phone-vibration"></i></span>
                                            <input type="text" name="units[{{ $index }}][hsn_number]" class="form-control fw-bold imei-field fs-14"
                                                value="{{ $d->hsn_number }}" required>
                                        </div>
                                    </div>

                                    <div class="row g-4">
                                        {{-- Phase 1: Purchase --}}
                                        <div class="col-md-{{ $isSold ? '6' : '12' }} {{ $isSold ? 'border-end' : '' }}">
                                            <div class="p-3 rounded bg-light-subtle border border-dashed border-primary-subtle position-relative">
                                                <span class="position-absolute top-0 end-0 translate-middle-y badge rounded-pill bg-primary px-3" style="margin-top: 10px; margin-right: 15px;">
                                                    Phase 1: Purchase
                                                </span>
                                                <h6 class="fw-bold text-primary mb-3 d-flex align-items-center">
                                                    <i class="iconoir-download me-2"></i>
                                                    Purchase Details
                                                </h6>
                                                
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted">Buy Price (₹) <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.01" name="units[{{ $index }}][buy_price]"
                                                            class="form-control buy-price-field" value="{{ number_format($purchaseTransaction->price ?? 0, 2, '.', '') }}"
                                                            required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small text-muted">Purchase Date <span class="text-danger">*</span></label>
                                                        <input type="date" name="units[{{ $index }}][purchase_date]" class="form-control"
                                                            value="{{ $purchaseTransaction ? \Carbon\Carbon::parse($purchaseTransaction->transaction_date)->format('Y-m-d') : date('Y-m-d') }}"
                                                            required>
                                                    </div>

                                                    <div class="col-12 mt-2">
                                                        <label class="form-label small text-muted">Supplier Phone <span class="text-danger">*</span></label>
                                                        <x-intl-tel-input name="units[{{ $index }}][supplier_phone]" class="supplier-phone" value="{{ $supplier->phone ?? '' }}" required />
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small text-muted">Supplier Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="units[{{ $index }}][supplier_name]" class="form-control supplier-name"
                                                            value="{{ $supplier->name ?? '' }}" required>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small text-muted">Supplier Address <span class="text-danger">*</span></label>
                                                        <input type="text" name="units[{{ $index }}][supplier_address]"
                                                            class="form-control supplier-address" value="{{ $supplier->address ?? '' }}"
                                                            required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Phase 2: Sale --}}
                                        @if ($isSold)
                                            <div class="col-md-6">
                                                <div class="p-3 rounded bg-light-subtle border border-dashed border-success-subtle position-relative">
                                                    <span class="position-absolute top-0 end-0 translate-middle-y badge rounded-pill bg-success px-3" style="margin-top: 10px; margin-right: 15px;">
                                                        Phase 2: Sale
                                                    </span>
                                                    <h6 class="fw-bold text-success mb-3 d-flex align-items-center">
                                                        <i class="iconoir-upload me-2"></i>
                                                        Sale Details
                                                    </h6>
                                                    
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small text-muted">Sell Price (₹) <span class="text-danger">*</span></label>
                                                            <input type="number" step="0.01" name="units[{{ $index }}][sell_price]"
                                                                class="form-control sell-price-field" value="{{ number_format($d->saleTransaction->price ?? 0, 2, '.', '') }}"
                                                                required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small text-muted">Sale Date <span class="text-danger">*</span></label>
                                                            <input type="date" name="units[{{ $index }}][sale_date]" class="form-control"
                                                                value="{{ $d->saleTransaction ? \Carbon\Carbon::parse($d->saleTransaction->transaction_date)->format('Y-m-d') : date('Y-m-d') }}"
                                                                required>
                                                        </div>

                                                        <div class="col-12 mt-2">
                                                            <label class="form-label small text-muted">Customer Phone <span class="text-danger">*</span></label>
                                                            <x-intl-tel-input name="units[{{ $index }}][customer_phone]" class="customer-phone" value="{{ $saleCust->phone ?? '' }}" required />
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label small text-muted">Customer Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="units[{{ $index }}][customer_name]" class="form-control customer-name"
                                                                value="{{ $saleCust->name ?? '' }}" required>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label small text-muted">Customer Address <span class="text-danger">*</span></label>
                                                            <input type="text" name="units[{{ $index }}][customer_address]"
                                                                class="form-control customer-address" value="{{ $saleCust->address ?? '' }}"
                                                                required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-12 mt-3 mb-5">
                    <div class="card p-3 shadow-sm bg-primary-subtle border-primary">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-primary fw-semibold">Note: Changes will apply to this unit and its
                                history.</span>
                            <button type="submit" class="btn btn-primary px-5 d-flex justify-content-center align-items-center">
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
