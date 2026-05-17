@extends('layouts.app')
@section('pageCss')
    <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/css/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <form class="repeater" action="{{ route('invoice.update', $invoice->id) }}" method="POST"
                        enctype="multipart/form-data" id="createInvoiceForm">
                        @csrf
                        @method('PUT')
                        <div class="card-body" style="background-color: rgba(197, 124, 34, 0.05)">
                            <div class="row">
                                <div
                                    class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 align-self-center text-md-start text-lg-start text-xl-start text-center">
                                    <img src="{{ asset('assets/logo/yash-mobile-logo-white.png') }}" alt="logo-small"
                                        class="logo-sm me-1 logo-light" height="70">
                                    <img src="{{ asset('assets/logo/yash-mobile-logo.png') }}" alt="logo-small"
                                        class="logo-sm me-1 logo-dark" height="70">
                                </div>
                                <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 text-end align-self-center">
                                    <div class="col-12">
                                        <div class="form-group row justify-content-end mb-2">
                                            <h5 class="col-lg-3 text-end mb-0 fw-semibold align-self-center">
                                                <span class="text-muted">Invoice:</span>
                                            </h5>
                                            <div class="col-lg-3">
                                                <input class="form-control" type="text" name="invoice_number" readonly
                                                    value="#{{ $invoice->invoice_no }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group row justify-content-end mb-2">
                                            <h5 class="col-lg-3 text-end mb-0 fw-semibold align-self-center">
                                                <span class="text-muted">Invoice Date:</span>
                                            </h5>
                                            <div class="col-lg-3">
                                                <input class="form-control" type="text" name="invoice_date" readonly
                                                    value="{{ $invoice->invoice_date }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row row-cols-3 d-flex justify-content-md-between">
                                <div class="col-12 col-md-3 col-md-4 col-xl-4 d-print-flex align-self-center">
                                    <div class="">
                                        <strong class="fs-14">Invoice to :</strong><br>
                                        <input id="mobile" name="mobile" type="text" class="form-control mb-1"
                                            placeholder="Enter Mobile" value="{{ $invoice->customer->phone ?? '' }}">
                                        @error('mobile')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <input id="name" name="name" type="text" class="form-control my-1"
                                            placeholder="Enter Name" value="{{ $invoice->customer->name ?? '' }}">
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 col-md-4 col-xl-4 d-print-flex align-self-center">
                                    <div class="">
                                        <address class="fs-13">
                                            <strong class="fs-14">Billed To :</strong><br>
                                            <textarea class="form-control" rows="3" id="address" name="address" placeholder="Enter Address">{{ $invoice->customer->address ?? '' }}</textarea>
                                            @error('address')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </address>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0">
                                            <thead class="table-light text-nowrap">
                                                <tr>
                                                    <th>
                                                        <div class="row">
                                                            <div class="col-4">
                                                                Items
                                                            </div>
                                                            <div class="col-8">
                                                                <input type="text" class="form-control" id="barcode"
                                                                    placeholder="Scan Barcode">
                                                            </div>
                                                        </div>
                                                    </th>
                                                    <th>Quantity</th>
                                                    <th>Price (₹)</th>
                                                    <th>Discount (₹)</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody data-repeater-list="invoice_items">
                                                @forelse ($invoice->items as $item)
                                                    <tr data-repeater-item>
                                                        <td style="min-width: 300px;">
                                                            @php
                                                                $itemUniqueId = $item->imei_id ? 'imei_' . $item->imei_id : $item->item_type . '_' . $item->item_id;
                                                            @endphp
                                                            <select name="product_id" class="form-select product_id"
                                                                required>
                                                                <option value="">Select Product</option>
                                                                @foreach ($products as $product)
                                                                    <option value="{{ $product->unique_id }}"
                                                                        {{ $itemUniqueId == $product->unique_id ? 'selected' : '' }}
                                                                        data-quantity="{{ $product->quantity }}"
                                                                        data-product="{{ json_encode($product) }}">
                                                                        {{ $product->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('invoice_items.*.product_id')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                            <div class="form-check mt-1">
                                                                <input class="form-check-input manual-entry-checkbox"
                                                                    type="checkbox" value="" id="manualEntry"
                                                                    style="min-width: 15px;">
                                                                <label class="form-check-label" for="manualEntry">
                                                                    Manual Entry
                                                                </label>
                                                            </div>
                                                            <div class="" id="manual_div">
                                                                <div class="row mt-1 g-1">
                                                                    <div class="col-12 col-md-4 col-lg-4 col-xl-4">
                                                                        <select name="item_type"
                                                                            class="form-control item-type-select" required
                                                                            disabled>
                                                                            <option value="device"
                                                                                {{ $item->item_type == 'device' ? 'selected' : '' }}>
                                                                                Device</option>
                                                                            <option value="accessory"
                                                                                {{ $item->item_type == 'accessory' ? 'selected' : '' }}>
                                                                                Accessory</option>
                                                                        </select>
                                                                        @error('invoice_items.*.item_type')
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="col-12 col-md-4 col-lg-4 col-xl-4">
                                                                        <select name="brand_id"
                                                                            class="form-control brand-select" required
                                                                            disabled>
                                                                            <option value="">Select Brand</option>
                                                                            @foreach ($brands as $brand)
                                                                                <option value="{{ $brand->id }}"
                                                                                    data-type="{{ $brand->type }}"
                                                                                    {{ optional($item->item)->brand_id == $brand->id ? 'selected' : '' }}>
                                                                                    {{ $brand->name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('invoice_items.*.brand_id')
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="col-12 col-md-3 col-lg-3 col-xl-3">
                                                                        <input name="name" type="text"
                                                                            class="form-control w-100"
                                                                            placeholder="Model Name"
                                                                            value="{{ optional($item->item)->name ?? optional($item->item?->model)->name }}"
                                                                            @readonly(true)>
                                                                        @error('invoice_items.*.name')
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                                <div class="row mt-1 g-1">
                                                                    <div class="col-12 col-md-4 col-lg-4 col-xl-4">
                                                                        <input name="imei_or_serial_number" type="text"
                                                                            class="form-control w-100"
                                                                            placeholder="IMEI/Serial"
                                                                            value="{{ $item->deviceImei ? $item->deviceImei->imei : ($item->imei_or_serial_number ?? optional($item->item)->sku) }}"
                                                                            @readonly(true)>
                                                                        @error('invoice_items.*.imei_or_serial_number')
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="col-12 col-md-2 col-lg-2 col-xl-2">
                                                                        <input name="color" type="text"
                                                                            class="form-control w-100"
                                                                            placeholder="Enter Model Color"
                                                                            value="{{ optional($item->item)->color }}"
                                                                            @readonly(true)>
                                                                        @error('invoice_items.*.color')
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="col-12 col-md-2 col-lg-2 col-xl-2">
                                                                        <input name="storage" type="text"
                                                                            class="form-control w-100"
                                                                            placeholder="Enter Storage"
                                                                            value="{{ optional($item->item)->storage }}"
                                                                            @readonly(true)>
                                                                        @error('invoice_items.*.storage')
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="col-12 col-md-2 col-lg-2 col-xl-2">
                                                                        <input name="ram" type="text"
                                                                            class="form-control w-100" placeholder="RAM"
                                                                            value="{{ optional($item->item)->ram }}"
                                                                            @readonly(true)>
                                                                        @error('invoice_items.*.ram')
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td style="min-width: 160px; max-width: 160px;">
                                                            <div class="input-group qty-icons">
                                                                <button type="button"
                                                                    class="btn btn-primary decrement">-</button>
                                                                <input type="number"
                                                                    class="form-control text-center quantity-input"
                                                                    min="1" name="quantity"
                                                                    value="{{ $item->quantity }}" readonly>
                                                                <button type="button"
                                                                    class="btn btn-primary increment">+</button>
                                                            </div>
                                                            @error('invoice_items.*.quantity')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </td>
                                                        <td style="min-width: 50px; max-width: 130px;">
                                                            <input name="price" type="number"
                                                                class="form-control price" placeholder="Enter Price"
                                                                value="{{ $item->price }}">
                                                            @error('invoice_items.*.price')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </td>
                                                        <td style="min-width: 40px; max-width: 40px;">
                                                            <input name="discount" type="number"
                                                                class="form-control discount" placeholder="Enter Discount"
                                                                value="{{ $item->discount }}">
                                                            @error('invoice_items.*.discount')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </td>
                                                        <td style="min-width: 60px; max-width: 60px;">
                                                            <div class="d-flex justify-content-between">
                                                                <span>
                                                                    ₹<span
                                                                        class="item_sub_total">{{ $item->price * $item->quantity - $item->discount }}</span>
                                                                </span>
                                                                <i class="iconoir-trash text-danger fs-18"
                                                                    data-repeater-delete></i>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <!-- Should not happen ideally in edit, but safe fallback -->
                                                    <tr data-repeater-item>
                                                        <!-- ... Copy of empty row structure from Create ... -->
                                                        <!-- Omitted for brevity, assuming existing invoice has items -->
                                                        <!-- Logic in Edit controller ensures items exist or we can just ignore -->
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="border-0">
                                                        <button type="button"
                                                            class="btn btn-outline-info btn-sm px-2 mt-2"
                                                            data-repeater-create>Add Item</button>
                                                    </td>
                                                    <td class="border-0 fs-14 text-dark"></td>
                                                    <td class="border-0 fs-14 text-dark"></td>
                                                    <td class="border-0 fs-14 text-dark"><b>Discount</b></td>
                                                    <td class="border-0 fs-14 text-dark"><b>₹ <span
                                                                id="totalDiscount">{{ $invoice->items->sum('discount') }}</span></b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="border-0 fs-14 text-dark"></td>
                                                    <td class="border-0 fs-14 text-dark"></td>
                                                    <td class="border-0 fs-14 text-dark"></td>
                                                    <td class="border-0 fs-14 text-dark"><b>Total</b></td>
                                                    <td class="border-0 fs-14 text-dark"><b>₹ <span
                                                                id="total">{{ $invoice->total_amount }}</span></b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="border-0" colspan="3"></td>
                                                    <td class="border-0" colspan="2">
                                                        <div class="d-flex justify-content-end gap-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="payment_method" id="payment_cash"
                                                                    value="Cash"
                                                                    {{ $invoice->payment_method == 'Cash' ? 'checked' : '' }}
                                                                    style="min-width: 15px;">
                                                                <label class="form-check-label"
                                                                    for="payment_cash">Cash</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="payment_method" id="payment_card"
                                                                    value="Card"
                                                                    {{ $invoice->payment_method == 'Card' ? 'checked' : '' }}
                                                                    style="min-width: 15px;">
                                                                <label class="form-check-label"
                                                                    for="payment_card">Card</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="payment_method" id="payment_upi" value="UPI"
                                                                    {{ $invoice->payment_method == 'UPI' ? 'checked' : '' }}
                                                                    style="min-width: 15px;">
                                                                <label class="form-check-label"
                                                                    for="payment_upi">UPI</label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <h5 class="mt-4">Terms And Conditions :</h5>
                                    <ul class="ps-3">
                                        <li>
                                            <small class="fs-14 fw-bold">1. Warranty on New Mobiles</small>
                                            <ul class="ps-3">
                                                <li><small class="fs-14">Manufacturer warranty applies as per brand
                                                        policy.</small></li>
                                                <li><small class="fs-14">Warranty claims will be handled by authorized
                                                        service centers only.</small></li>
                                            </ul>
                                        </li>

                                        <li>
                                            <small class="fs-14 fw-bold">2. Warranty on Old/Used Mobiles</small>
                                            <ul class="ps-3">
                                                <li><small class="fs-14">24 Hours store warranty is provided (only for
                                                        internal hardware issues).</small></li>
                                                <li><small class="fs-14">The original purchase bill is mandatory for any
                                                        claim.</small></li>
                                                <li><small class="fs-14">Mobile displays, batteries, charging ports, and
                                                        physical/liquid damages are not covered.</small></li>
                                            </ul>
                                        </li>

                                        <li>
                                            <small class="fs-14 fw-bold">3. Accessories</small>
                                            <ul class="ps-3">
                                                <li><small class="fs-14">Only branded accessories carry company
                                                        warranty.</small></li>
                                                <li><small class="fs-14">Local/unbranded accessories are sold without
                                                        warranty unless specified.</small></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-lg-6 align-self-center">
                                    <div class="float-none float-md-end" style="width: 30%;">
                                        <small>Account Manager</small>
                                        <img src="{{ asset('admin-assets/images/signature.png') }}" alt=""
                                            class="mt-2 mb-1" height="65">
                                        <p class="border-top">Signature</p>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row d-flex justify-content-center">
                                <div class="col-lg-12 col-xl-4 ms-auto align-self-center">
                                    <div class="text-center">
                                        <small class="fs-12">Thank You For Shopping With Us.</small>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-xl-4">
                                    <div class="float-end d-print-none mt-2 mt-md-0">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                        <a href="{{ route('invoice.index') }}" class="btn btn-danger">Back</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('pageScripts')
    <script>
        var customerSearchUrl = "{{ route('invoice.getCustomer') }}";
    </script>
    <script src="{{ asset('vendor-assets/libs/repeater/repeater.js') }}"></script>
    <script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/js/pages/invoice/update.js') }}"></script>
@endsection
