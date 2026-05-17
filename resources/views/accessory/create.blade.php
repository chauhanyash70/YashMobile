@extends('layouts.app')

@section('pageCss')
    <link href="{{ asset('assets/css/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet"
        type="text/css" />
@endsection

@section('content')
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('Add New Accessory') }}</h5>
                        <a href="{{ route('accessories.index') }}" class="btn btn-secondary btn-sm">Back</a>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('accessories.store') }}" method="POST" id="accessoryCreateForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="sku" class="form-label">Serial Number<span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku"
                                        name="sku" value="{{ old('sku') }}" placeholder="Scan Barcode or Type"
                                        style="font-size: 16px; font-weight: bold;">
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="purchase_date" class="form-label">Purchase Date<span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('purchase_date') is-invalid @enderror"
                                        id="purchase_date" name="purchase_date" value="{{ old('purchase_date') }}" required>
                                    @error('purchase_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="brand_id" class="form-label">Brand<span class="text-danger">*</span></label>
                                    <select name="brand_id" id="brand_id"
                                        class="form-select @error('brand_id') is-invalid @enderror" required>
                                        <option value="">Select Brand</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Accessory Name<span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                        name="name" value="{{ old('name') }}" placeholder="e.g. Headphone" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="model" class="form-label">Model<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('model') is-invalid @enderror" id="model"
                                        name="model" value="{{ old('model') }}" placeholder="e.g. HP1234">
                                    @error('model')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="color" class="form-label">Color<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('color') is-invalid @enderror" id="color"
                                        name="color" value="{{ old('color') }}" placeholder="e.g. Black">
                                    @error('color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="stock" class="form-label">Opening Stock<span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                        id="stock" name="stock" value="{{ old('stock', 0) }}" min="0" required>
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="purchase_price" class="form-label">Buy Price (₹)<span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="0.01"
                                        class="form-control @error('purchase_price') is-invalid @enderror"
                                        id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}"
                                        placeholder="0.00" required>
                                    @error('purchase_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="sale_price" class="form-label">Sell Price (₹)<span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="0.01"
                                        class="form-control @error('sale_price') is-invalid @enderror" id="sale_price"
                                        name="sale_price" value="{{ old('sale_price') }}" placeholder="0.00" required>
                                    @error('sale_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <hr>
                                <h3>Dealer/Supplier Details</h3>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_mobile_number" class="form-label">Mobile Number<span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('supplier_mobile_number') is-invalid @enderror"
                                        id="supplier_mobile_number" name="supplier_mobile_number"
                                        value="{{ old('supplier_mobile_number') }}" placeholder="e.g. 9876543210" required>
                                    @error('supplier_mobile_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_name" class="form-label">Name<span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('supplier_name') is-invalid @enderror"
                                        id="supplier_name" name="supplier_name" value="{{ old('supplier_name') }}"
                                        placeholder="e.g. John Doe" required>
                                    @error('supplier_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city"
                                        name="city" value="{{ old('city') }}" placeholder="e.g. Mumbai" required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror"
                                        id="address" name="address" value="{{ old('address') }}"
                                        placeholder="e.g. 123 Main Street">
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">Save Accessory</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageScripts')
    <script>
        var supplierSearchUrl = "{{ route('supplier.search') }}";
    </script>
    <script src="{{ asset('assets/js/select2/select2.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/js/pages/accessory/create.js') }}"></script>
@endsection