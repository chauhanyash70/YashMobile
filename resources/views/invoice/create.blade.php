@extends('layouts.app')
@section('title', 'Create Invoice')

@section('header_title', $header_title ?? 'Create Invoice')
@section('tagline', $tagline ?? 'Generate a new sales receipt for devices or accessories.')


@section('pageCss')
    <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/css/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card invoice-card shadow-sm">
                    <form class="repeater" action="{{ route('invoice.store') }}" method="POST"
                        enctype="multipart/form-data" id="createInvoiceForm">
                        @csrf

                        <!-- Top header brand panel (matches original design layout) -->
                        <div class="card-body invoice-brand-panel">
                            <div class="row g-3">
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
                                                    value="#{{ App\Http\Traits\Traits::getInvoiceNumber() }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group row justify-content-end mb-2">
                                            <h5 class="col-lg-3 text-end mb-0 fw-semibold align-self-center">
                                                <span class="text-muted">Invoice Date:</span>
                                            </h5>
                                            <div class="col-lg-3">
                                                <input class="form-control" type="text" name="invoice_date" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer information and details card body -->
                        <div class="card-body p-4">
                            <div class="row row-cols-3 d-flex justify-content-md-between mb-4">
                                <div class="col-12 col-md-3 col-md-4 col-xl-4 d-print-flex align-self-center">
                                    <div>
                                        <strong class="fs-14 d-block mb-2 text-dark">Invoice to : <span
                                                class="text-danger">*</span></strong>
                                        <input id="mobile" name="mobile" type="text" class="form-control mb-2"
                                            placeholder="Enter Mobile">
                                        @error('mobile')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <input id="name" name="name" type="text" class="form-control"
                                            placeholder="Enter Name">
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12 col-md-3 col-md-4 col-xl-4 d-print-flex align-self-center mt-3 mt-md-0">
                                    <div>
                                        <address class="fs-13 mb-0">
                                            <strong class="fs-14 d-block mb-2 text-dark">Billed To :</strong>
                                            <textarea class="form-control" rows="3" id="address" name="address" placeholder="Enter Address"></textarea>
                                            @error('address')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </address>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Table Area (With integrated barcode scanner in table head) -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="table-responsive shadow-sm">
                                        <table class="table table-bordered mb-0 align-middle">
                                            <thead class="table-light text-nowrap">
                                                <tr>
                                                    <th>
                                                        <div class="row align-items-center">
                                                            <div class="col-4">
                                                                Items
                                                            </div>
                                                            <div class="col-8">
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text bg-white border-end-0"><i
                                                                            class="iconoir-barcode text-muted"></i></span>
                                                                    <input type="text"
                                                                        class="form-control border-start-0 ps-1"
                                                                        id="barcode" placeholder="Scan Barcode">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </th>
                                                    <th style="width: 170px;">Quantity <span class="text-danger">*</span>
                                                    </th>
                                                    <th style="width: 150px;">Price (₹) <span class="text-danger">*</span>
                                                    </th>
                                                    <th style="width: 140px;">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody data-repeater-list="invoice_items">
                                                <tr data-repeater-item>
                                                    <td style="min-width: 300px;">
                                                        <div class="product-select-wrapper">
                                                            <select name="product_id" class="form-select product_id"
                                                                required>
                                                                <option value="">Select Product</option>
                                                                @foreach ($products as $product)
                                                                    <option value="{{ $product->unique_id }}"
                                                                        data-quantity="{{ $product->quantity }}"
                                                                        data-product="{{ json_encode($product) }}">
                                                                        {{ $product->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        @error('invoice_items.*.product_id')
                                                            <span class="text-danger d-block mt-1">{{ $message }}</span>
                                                        @enderror

                                                        <div class="form-check mt-2">
                                                            <label class="form-check-label fs-12 text-muted fw-bold cursor-pointer d-flex align-items-center gap-2">
                                                                <input
                                                                    class="form-check-input manual-entry-checkbox cursor-pointer"
                                                                    type="checkbox" value="" style="min-width: 15px; margin-top: 0;">
                                                                Manual Entry
                                                            </label>
                                                        </div>

                                                        <!-- Manual details box structure -->
                                                        <div
                                                            class="manual-entry-box p-3 mt-2 border rounded-3 bg-light shadow-sm manual_div">
                                                            <div class="row g-2">
                                                                <div class="col-12 row align-items-center">
                                                                    <div class="col-12 col-md-6">
                                                                        <h6 class="text-dark mb-0 mt-0 fs-13 fw-bold">
                                                                            <i
                                                                                class="iconoir-box-iso me-1 text-primary"></i>
                                                                            Product Specifications
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-12 col-md-6 mt-1 mt-md-0">
                                                                        <input name="hsn_number" type="text"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="Enter HSN Code *"
                                                                            @readonly(true)>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 col-md-4">
                                                                    <label class="form-label mb-1 fs-11 text-muted">Item
                                                                        Type <span class="text-danger">*</span></label>
                                                                    <select name="item_type"
                                                                        class="form-select form-select-sm item-type-select"
                                                                        required disabled>
                                                                        <option value="device">Device</option>
                                                                        <option value="accessory">Accessory</option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-12 col-md-4">
                                                                    <label class="form-label mb-1 fs-11 text-muted">Brand
                                                                        <span class="text-danger">*</span></label>
                                                                    <select name="brand_id"
                                                                        class="form-select form-select-sm brand-select"
                                                                        required disabled>
                                                                        <option value="">Select Brand</option>
                                                                        @foreach ($brands as $brand)
                                                                            <option value="{{ $brand->id }}"
                                                                                data-type="{{ $brand->type }}"
                                                                                data-slug="{{ $brand->slug }}">
                                                                                {{ $brand->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-12 col-md-4">
                                                                    <label
                                                                        class="form-label mb-1 fs-11 text-muted">Model/Name
                                                                        <span class="text-danger">*</span></label>
                                                                    <input name="name" type="text"
                                                                        class="form-control form-control-sm"
                                                                        placeholder="Model Name" @readonly(true)>
                                                                </div>
                                                            </div>

                                                            <div class="row mt-2 g-2">
                                                                <div class="col-12 col-md-3">
                                                                    <label class="form-label mb-1 fs-11 text-muted">Color
                                                                        <span class="text-danger">*</span></label>
                                                                    <input name="color" type="text"
                                                                        class="form-control form-control-sm"
                                                                        placeholder="Enter Color" @readonly(true)>
                                                                </div>
                                                                <div class="col-12 col-md-3">
                                                                    <label class="form-label mb-1 fs-11 text-muted">Storage
                                                                        <span class="text-danger">*</span></label>
                                                                    <input name="storage" type="text"
                                                                        class="form-control form-control-sm"
                                                                        placeholder="Enter Storage" @readonly(true)>
                                                                </div>
                                                                <div class="col-12 col-md-3">
                                                                    <label class="form-label mb-1 fs-11 text-muted">RAM
                                                                        <span class="text-danger">*</span></label>
                                                                    <input name="ram" type="text"
                                                                        class="form-control form-control-sm"
                                                                        placeholder="RAM" @readonly(true)>
                                                                </div>
                                                                <div class="col-12 col-md-3 battery-health-div"
                                                                    style="display: none;">
                                                                    <label class="form-label mb-1 fs-11 text-muted">Battery
                                                                        Health (%)</label>
                                                                    <input name="battery_health" type="text"
                                                                        class="form-control form-control-sm"
                                                                        placeholder="e.g. 95%" @readonly(true)>
                                                                </div>
                                                            </div>

                                                            <!-- Supplier specific fields inside manual entry -->
                                                            <div class="supplier-details-div mt-3 pt-3 border-top"
                                                                style="display: none;">
                                                                <div class="row g-2">
                                                                    <div class="col-12 row align-items-center">
                                                                        <div class="col-12 col-md-6">
                                                                            <h6
                                                                                class="text-primary mb-0 mt-0 fs-13 fw-bold">
                                                                                <i class="iconoir-user-circle me-1"></i>
                                                                                Supplier Information
                                                                            </h6>
                                                                        </div>
                                                                        <div class="col-12 col-md-6 mt-1 mt-md-0">
                                                                            <input name="supplier_phone" type="text"
                                                                                class="form-control form-control-sm supplier_phone"
                                                                                placeholder="Phone Number">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-12 col-md-4">
                                                                        <label
                                                                            class="form-label mb-1 fs-11 text-muted">Supplier
                                                                            Name <span class="text-danger">*</span></label>
                                                                        <input name="supplier_name" type="text"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="Enter Name">
                                                                    </div>
                                                                    <div class="col-12 col-md-4">
                                                                        <label
                                                                            class="form-label mb-1 fs-11 text-muted">Supplier
                                                                            Address <span
                                                                                class="text-danger">*</span></label>
                                                                        <input name="supplier_address" type="text"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="Enter Address">
                                                                    </div>
                                                                    <div class="col-12 col-md-4">
                                                                        <label class="form-label mb-1 fs-11 text-muted">Buy
                                                                            Price (₹) <span
                                                                                class="text-danger">*</span></label>
                                                                        <input name="buy_price" type="number"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="Buy Price">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <!-- Quantity input structure inside table row -->
                                                    <td>
                                                        <div class="input-group qty-icons">
                                                            <button type="button" class="btn decrement">-</button>
                                                            <input type="number"
                                                                class="form-control text-center quantity-input"
                                                                min="1" name="quantity" value="1" readonly>
                                                            <button type="button" class="btn increment">+</button>
                                                        </div>
                                                        @error('invoice_items.*.quantity')
                                                            <span class="text-danger d-block mt-1">{{ $message }}</span>
                                                        @enderror
                                                    </td>

                                                    <!-- Price field inside table row -->
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-light">₹</span>
                                                            <input name="price" type="number"
                                                                class="form-control price" placeholder="Price"
                                                                min="1" step="0.01">
                                                        </div>
                                                        @error('invoice_items.*.price')
                                                            <span class="text-danger d-block mt-1">{{ $message }}</span>
                                                        @enderror
                                                    </td>

                                                    <!-- Item Subtotal display -->
                                                    <td>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="fw-semibold">
                                                                ₹<span class="item_sub_total">0.00</span>
                                                            </span>
                                                            <button type="button"
                                                                class="btn btn-soft-danger btn-sm rounded-circle p-2 border-0"
                                                                data-repeater-delete
                                                                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="iconoir-trash fs-16"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>

                                            <!-- Table controls footer row -->
                                            <tfoot>
                                                <tr>
                                                    <td class="border-0">
                                                        <button type="button"
                                                            class="btn btn-outline-info btn-sm px-3 mt-2 rounded-3"
                                                            data-repeater-create><i class="iconoir-plus me-1"></i>Add
                                                            Item</button>
                                                    </td>
                                                    <td class="border-0 fs-14 text-dark"></td>
                                                    <td class="border-0 fs-14 text-dark text-end"><b>Total</b></td>
                                                    <td class="border-0 fs-14 text-dark"><b>₹ <span
                                                                id="total">0.00</span></b></td>
                                                </tr>

                                                <!-- Payment details subrow in footer -->
                                                <tr>
                                                    <td class="border-0" colspan="2"></td>
                                                    <td class="border-0" colspan="2">
                                                        <div class="d-flex justify-content-end gap-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="payment_method" id="payment_cash"
                                                                    value="cash" checked
                                                                    style="min-width: 15px; cursor:pointer;">
                                                                <label class="form-check-label fw-semibold"
                                                                    for="payment_cash"
                                                                    style="cursor:pointer;">Cash</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="payment_method" id="payment_card"
                                                                    value="card"
                                                                    style="min-width: 15px; cursor:pointer;">
                                                                <label class="form-check-label fw-semibold"
                                                                    for="payment_card"
                                                                    style="cursor:pointer;">Card</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="payment_method" id="payment_upi" value="upi"
                                                                    style="min-width: 15px; cursor:pointer;">
                                                                <label class="form-check-label fw-semibold"
                                                                    for="payment_upi" style="cursor:pointer;">UPI</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="payment_method" id="payment_bajaj"
                                                                    value="bajaj_finance"
                                                                    style="min-width: 15px; cursor:pointer;">
                                                                <label class="form-check-label fw-semibold"
                                                                    for="payment_bajaj" style="cursor:pointer;">Bajaj
                                                                    Finance</label>
                                                            </div>
                                                        </div>

                                                        <!-- dynamic bajaj approval layout box -->
                                                        <div id="bajaj_approval_div" style="display: none;"
                                                            class="mt-2">
                                                            <input type="text" name="bajaj_approval_number"
                                                                id="bajaj_approval_number" class="form-control"
                                                                placeholder="Enter Bajaj Approval Number">
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms, signature blocks, and brand statement -->
                            <div class="row mt-4">
                                <div class="col-lg-6">
                                    <h5 class="mt-3 text-dark fw-bold"><i
                                            class="iconoir-warning-circle text-warning me-1"></i> Terms And Conditions :
                                    </h5>
                                    <ul class="ps-3 text-muted">
                                        <li>
                                            <small class="fs-13 fw-bold text-dark">1. Warranty on New Mobiles</small>
                                            <ul class="ps-3">
                                                <li><small class="fs-12">Manufacturer warranty applies as per brand
                                                        policy.</small></li>
                                                <li><small class="fs-12">Warranty claims will be handled by authorized
                                                        service centers only.</small></li>
                                            </ul>
                                        </li>

                                        <li class="mt-2">
                                            <small class="fs-13 fw-bold text-dark">2. Warranty on Old/Used Mobiles</small>
                                            <ul class="ps-3">
                                                <li><small class="fs-12">24 Hours store warranty is provided (only for
                                                        internal hardware issues).</small></li>
                                                <li><small class="fs-12">The original purchase bill is mandatory for any
                                                        claim.</small></li>
                                                <li><small class="fs-12">Mobile displays, batteries, charging ports, and
                                                        physical/liquid damages are not covered.</small></li>
                                            </ul>
                                        </li>

                                        <li class="mt-2">
                                            <small class="fs-13 fw-bold text-dark">3. Accessories</small>
                                            <ul class="ps-3">
                                                <li><small class="fs-12">Only branded accessories carry company
                                                        warranty.</small></li>
                                                <li><small class="fs-12">Local/unbranded accessories are sold without
                                                        warranty unless specified.</small></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-lg-6 align-self-center d-flex justify-content-lg-end mt-4 mt-lg-0">
                                    <div class="text-center signature-wrapper" style="width: 200px;">
                                        <small class="text-muted d-block">Account Manager</small>
                                        {{-- <img src="{{ asset('admin-assets/images/signature.png') }}" alt="signature"
                                            class="mt-2 mb-1" height="50"> --}}
                                        <div class="signature-line">
                                            <small class="fw-bold text-dark">Authorized Signature</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="border-light my-4">
                            <div class="row d-flex justify-content-center align-items-center">
                                <div class="col-lg-12 col-xl-4 ms-auto align-self-center text-center text-xl-start">
                                    <small class="fs-13 text-muted fw-semibold">Thank You For Shopping With Us.</small>
                                </div>
                                <div class="col-lg-12 col-xl-4">
                                    <div class="float-end d-print-none mt-2 mt-md-0">
                                        <button type="submit"
                                            class="btn btn-primary px-4 rounded-3 fw-bold">Submit</button>
                                        <a href="{{ route('invoice.index') }}"
                                            class="btn btn-danger px-4 rounded-3 fw-bold ms-1">Back</a>
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
        var supplierSearchUrl = "{{ route('invoice.getSupplier') }}";
    </script>
    <script src="{{ asset('vendor-assets/libs/repeater/repeater.js') }}"></script>
    <script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/js/pages/invoice/create.js') }}"></script>
    <script>
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const hsn = urlParams.get('hsn_number');
            if (hsn) {
                $('#barcode').val(hsn);
                // Trigger the barcode scan logic (Enter keypress)
                setTimeout(function() {
                    var e = $.Event("keypress");
                    e.which = 13;
                    $("#barcode").trigger(e);
                }, 500); // Small delay to ensure all JS is ready
            }
        });

        $(document).on('change', 'input[name="payment_method"]', function() {
            if ($(this).val() === 'bajaj_finance') {
                $('#bajaj_approval_div').show();
                $('#bajaj_approval_number').attr('required', true);
            } else {
                $('#bajaj_approval_div').hide();
                $('#bajaj_approval_number').attr('required', false).val('');
            }
        });
    </script>
@endsection
