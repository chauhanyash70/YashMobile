@extends('layouts.app')

@section('pageCss')
    <link href="{{ asset('assets/css/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet"
        type="text/css" />
    <style>
        .unit-card { border-left: 4px solid #198754; transition: all 0.3s ease; }
        .unit-card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .unit-header { background: rgba(25, 135, 84, 0.05); }
    </style>
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
                            <h5 class="mb-0">Add New Device Model (Global Specs)</h5>
                            <a href="{{ route('mobiles.index') }}" class="btn btn-secondary btn-sm">Back</a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="brand_id" class="form-label">Brand<span class="text-danger">*</span></label>
                                    <select name="brand_id" id="brand_id" class="form-select @error('brand_id') is-invalid @enderror" required>
                                        <option value="">Select Brand</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="model_name" class="form-label">Model Name<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="model_name" name="model_name" value="{{ old('model_name') }}" placeholder="e.g. iPhone 13" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="storage" class="form-label">Storage<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="storage" name="storage" value="{{ old('storage') }}" placeholder="128GB" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="ram" class="form-label">RAM<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ram" name="ram" value="{{ old('ram') }}" placeholder="8GB" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="color" class="form-label">Color<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="color" name="color" value="{{ old('color') }}" placeholder="Black" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="condition" class="form-label">Condition<span class="text-danger">*</span></label>
                                    <select name="condition" id="condition" class="form-select">
                                        <option value="old" {{ old('condition') == 'old' ? 'selected' : '' }}>Old</option>
                                        <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>New</option>
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
                            Individual Units (Add multiple IMEIs)
                        </h5>
                        <button type="button" class="btn btn-outline-success btn-sm" id="add-unit-btn">
                            <i class="iconoir-plus-circle me-1"></i>Add Unit Row
                        </button>
                    </div>
                    
                    <div id="units-container">
                        {{-- First row is always there --}}
                        <div class="card mb-3 unit-card" data-index="0">
                            <div class="card-header unit-header d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-success">Unit #1</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">IMEI / Serial Number <span class="text-danger">*</span></label>
                                        <input type="text" name="units[0][imei]" class="form-control fw-bold border-success imei-field" placeholder="Scan or Type" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">Buy Price (₹) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="units[0][buy_price]" class="form-control buy-price-field" placeholder="0.00" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small text-muted">Repair (₹)</label>
                                        <input type="number" step="0.01" name="units[0][repair_cost]" class="form-control" value="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">Purchase Date <span class="text-danger">*</span></label>
                                        <input type="text" name="units[0][purchase_date]" class="form-control date-picker" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                                    </div>

                                    <div class="col-12"><hr class="my-1"></div>
                                    <div class="col-12"><h6 class="small fw-bold">Supplier Information</h6></div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Supplier Phone <span class="text-danger">*</span></label>
                                        <input type="text" name="units[0][supplier_phone]" class="form-control supplier-phone" placeholder="Lookup phone..." required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Supplier Name <span class="text-danger">*</span></label>
                                        <input type="text" name="units[0][supplier_name]" class="form-control supplier-name" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">City</label>
                                        <input type="text" name="units[0][supplier_city]" class="form-control supplier-city">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3 mb-5 text-end">
                    <button type="submit" class="btn btn-success px-5 py-2">
                        <i class="iconoir-check-circle me-1"></i>Save All Units
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('pageScripts')
    <script>
        var supplierSearchUrl = "{{ route('supplier.search') }}";
        var nextIndex = 1;
    </script>
    <script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/js/pages/device/create.js') }}"></script>

@endsection
