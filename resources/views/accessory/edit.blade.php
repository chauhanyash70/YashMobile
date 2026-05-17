@extends('layouts.app')
@section('title', 'Edit Accessory')
@section('header_title', $header_title ?? 'Edit Accessory')
@section('tagline', $tagline ?? 'Update details for ' . $accessory->name)


@section('pageCss')
    <link href="{{ asset('assets/css/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <form action="{{ route('accessories.update', $accessory->id) }}" method="POST" id="accessoryEditForm">
                    @csrf
                    @method('PUT')

                    {{-- ── Section 1: Accessory Details ── --}}
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="iconoir-headset me-2 text-primary"></i> Edit Accessory
                            </h5>
                            <a href="{{ route('accessories.index') }}" class="btn btn-secondary btn-sm">
                                <i class="iconoir-arrow-left me-1"></i> Back
                            </a>
                        </div>
                        <div class="card-body py-4">
                            <div class="row g-3">

                                {{-- HSN --}}
                                <div class="col-12">
                                    <label for="hsn" class="form-label fw-bold">HSN / Serial Number</label>
                                    <input type="text" class="form-control fw-bold @error('hsn') is-invalid @enderror"
                                        id="hsn" name="hsn"
                                        value="{{ old('hsn', $accessory->hsn) }}"
                                        placeholder="e.g. ACC-001" style="font-weight:700; letter-spacing:0.02em;">
                                    @error('hsn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Purchase Date --}}
                                <div class="col-md-6">
                                    <label for="purchase_date" class="form-label">Purchase Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('purchase_date') is-invalid @enderror"
                                        id="purchase_date" name="purchase_date"
                                        value="{{ old('purchase_date', optional($accessory->purchase_date)->format('Y-m-d') ?? date('Y-m-d')) }}"
                                        required>
                                    @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Brand --}}
                                <div class="col-md-6">
                                    <label for="brand_id" class="form-label">Brand <span class="text-danger">*</span></label>
                                    <select name="brand_id" id="brand_id"
                                        class="form-select @error('brand_id') is-invalid @enderror" required>
                                        <option value="">Select Brand</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}"
                                                {{ (old('brand_id') ?? $accessory->brand_id) == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Accessory Name --}}
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Accessory Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name"
                                        value="{{ old('name', $accessory->name) }}"
                                        placeholder="e.g. Earphones" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Model --}}
                                <div class="col-md-6">
                                    <label for="model" class="form-label">Model</label>
                                    <input type="text" class="form-control @error('model') is-invalid @enderror"
                                        id="model" name="model"
                                        value="{{ old('model', $accessory->model) }}"
                                        placeholder="e.g. HP1234">
                                    @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Color --}}
                                <div class="col-md-6">
                                    <label for="color" class="form-label">Color</label>
                                    <input type="text" class="form-control @error('color') is-invalid @enderror"
                                        id="color" name="color"
                                        value="{{ old('color', $accessory->color) }}"
                                        placeholder="e.g. Black">
                                    @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Stock --}}
                                <div class="col-md-6">
                                    <label for="stock" class="form-label">Current Stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                        id="stock" name="stock"
                                        value="{{ old('stock', $accessory->stock) }}" min="0" required>
                                    @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Buy Price --}}
                                <div class="col-md-6">
                                    <label for="purchase_price" class="form-label">Buy Price (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01"
                                            class="form-control @error('purchase_price') is-invalid @enderror"
                                            id="purchase_price" name="purchase_price"
                                            value="{{ old('purchase_price', $accessory->purchase_price) }}"
                                            placeholder="0.00" required>
                                        @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                {{-- Sell Price --}}
                                <div class="col-md-6">
                                    <label for="sale_price" class="form-label">Sell Price (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01"
                                            class="form-control @error('sale_price') is-invalid @enderror"
                                            id="sale_price" name="sale_price"
                                            value="{{ old('sale_price', $accessory->sale_price) }}"
                                            placeholder="0.00" required>
                                        @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ── Section 2: Dealer / Supplier Details ── --}}
                    <div class="card mb-3">
                        <div class="card-header py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="iconoir-user me-2 text-warning"></i> Dealer / Supplier Details
                            </h5>
                        </div>
                        <div class="card-body py-4">
                            <div class="row g-3">

                                {{-- Supplier Phone --}}
                                <div class="col-md-6">
                                    <label for="supplier_mobile_number" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('supplier_mobile_number') is-invalid @enderror"
                                        id="supplier_mobile_number" name="supplier_mobile_number"
                                        value="{{ old('supplier_mobile_number', $supplier->phone ?? '') }}"
                                        placeholder="e.g. 9876543210" required>
                                    @error('supplier_mobile_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Supplier Name --}}
                                <div class="col-md-6">
                                    <label for="supplier_name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('supplier_name') is-invalid @enderror"
                                        id="supplier_name" name="supplier_name"
                                        value="{{ old('supplier_name', $supplier->name ?? '') }}"
                                        placeholder="e.g. John Doe" required>
                                    @error('supplier_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Address --}}
                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text"
                                        class="form-control @error('address') is-invalid @enderror"
                                        id="address" name="address"
                                        value="{{ old('address', $supplier->address ?? '') }}"
                                        placeholder="e.g. 123 Main Street">
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ── Actions ── --}}
                    <div class="d-flex justify-content-end gap-2 mb-4">
                        <a href="{{ route('accessories.index') }}" class="btn btn-light px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="iconoir-save-floppy-disk me-1"></i> Update Accessory
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

@section('pageScripts')
    <script>
        var supplierSearchUrl = "{{ route('invoice.getCustomer') }}";
        var csrfToken = "{{ csrf_token() }}";
    </script>
    <script src="{{ asset('assets/js/select2/select2.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/js/pages/accessory/edit.js') }}"></script>
@endsection
