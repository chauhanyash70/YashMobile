@extends('layouts.app')

@section('pageCss')
    <link href="{{ asset('assets/css/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet"
        type="text/css" />
    <style>
        .unit-card {
            border-left: 4px solid #0d6efd;
            transition: all 0.3s ease;
        }

        .unit-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .unit-header {
            background: rgba(13, 110, 253, 0.05);
        }
    </style>
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
                            <h5 class="mb-0">Device Specifications (Global)</h5>
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
                                            <option value="{{ $brand->id }}" {{ (old('brand_id') ?? $device->brand_id) == $brand->id ? 'selected' : '' }}>
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
                                <div class="col-md-3 mb-3">
                                    <label for="storage" class="form-label">Storage<span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="storage" name="storage"
                                        value="{{ old('storage', $device->storage) }}" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="ram" class="form-label">RAM<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ram" name="ram"
                                        value="{{ old('ram', $device->ram) }}" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="color" class="form-label">Color<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="color" name="color"
                                        value="{{ old('color', $device->color) }}" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="condition" class="form-label">Condition<span
                                            class="text-danger">*</span></label>
                                    <select name="condition" id="condition" class="form-select">
                                        <option value="old" {{ (old('condition') ?? $device->condition) == 'old' ? 'selected' : '' }}>Old</option>
                                        <option value="new" {{ (old('condition') ?? $device->condition) == 'new' ? 'selected' : '' }}>New</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Unit Management (IMEI Wise) --}}
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="iconoir-phone-vibration me-2"></i>
                            Manage Units (Total: <span id="total-units">{{ $device->imeis->count() }}</span>)
                        </h5>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-unit-btn">
                            <i class="iconoir-plus-circle me-1"></i>Add Another Unit
                        </button>
                    </div>

                    <div id="units-container">
                        @foreach($device->imeis as $index => $imei)
                            @php
                                $pItem = $imei->purchaseItem;
                                $purchase = $pItem->purchase ?? null;
                                $supplier = $purchase->supplier ?? null;
                            @endphp
                            <div class="card mb-3 unit-card" data-index="{{ $index }}">
                                <div class="card-header unit-header d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-primary">Unit #{{ $index + 1 }}</span>
                                    <div>
                                        @if($imei->status == 'sold')
                                            <span class="badge bg-danger">Sold</span>
                                        @else
                                            <span class="badge bg-success">In Stock</span>
                                        @endif
                                        @if($device->imeis->count() > 1)
                                            <button type="button" class="btn btn-link text-danger p-0 ms-2 remove-unit"
                                                title="Remove Unit">
                                                <i class="iconoir-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="units[{{ $index }}][id]" value="{{ $imei->id }}">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">IMEI / Serial Number <span class="text-danger">*</span></label>
                                            <input type="text" name="units[{{ $index }}][imei]"
                                                class="form-control fw-bold imei-field" value="{{ $imei->imei }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted">Buy Price (₹) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" name="units[{{ $index }}][buy_price]"
                                                class="form-control buy-price-field" value="{{ $pItem->price ?? 0 }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small text-muted">Repair (₹)</label>
                                            <input type="number" step="0.01" name="units[{{ $index }}][repair_cost]"
                                                class="form-control" value="{{ $pItem->repair_cost ?? 0 }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted">Purchase Date <span class="text-danger">*</span></label>
                                            <input type="text" name="units[{{ $index }}][purchase_date]"
                                                class="form-control date-picker"
                                                value="{{ $purchase->purchase_date ?? \Carbon\Carbon::now()->format('Y-m-d') }}"
                                                required>
                                        </div>

                                        <div class="col-12">
                                            <hr class="my-1">
                                        </div>
                                        <div class="col-12">
                                            <h6 class="small fw-bold">Supplier Details</h6>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Supplier Phone <span class="text-danger">*</span></label>
                                            <input type="text" name="units[{{ $index }}][supplier_phone]"
                                                class="form-control supplier-phone" value="{{ $supplier->phone ?? '' }}"
                                                required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Supplier Name <span class="text-danger">*</span></label>
                                            <input type="text" name="units[{{ $index }}][supplier_name]"
                                                class="form-control supplier-name" value="{{ $supplier->name ?? '' }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">City</label>
                                            <input type="text" name="units[{{ $index }}][supplier_city]"
                                                class="form-control supplier-city" value="{{ $supplier->city ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-12 mt-3 mb-5">
                    <div class="card p-3 shadow-sm bg-primary-subtle border-primary">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-primary fw-semibold">Note: Global specs change will apply to all
                                {{ $device->imeis->count() }} units.</span>
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
        var supplierSearchUrl = "{{ route('supplier.search') }}";
        var nextIndex = {{ $device->imeis->count() }};
    </script>
    <script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/js/pages/device/edit.js') }}"></script>

@endsection