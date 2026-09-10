@extends('layouts.app')
@section('title', 'Device Buyback')
@section('header_title', $header_title ?? 'Device Buyback')
@section('tagline', $tagline ?? 'Process a buyback transaction and return the unit to stock.')


@section('pageCss')
    <link href="{{ asset('assets/css/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet"
        type="text/css" />
    <style>
        .buyback-card {
            border-left: 4px solid #0d6efd;
        }

        .buyback-header {
            background: rgba(13, 110, 253, 0.05);
        }

        /* ---- New Customer Panel ---- */
        #newCustomerPanel {
            display: none;
            animation: fadeSlideIn 0.25s ease;
        }

        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Select2 dark-mode friendly override */
        .select2-container--default .select2-selection--single {
            height: 38px;
            border-radius: 6px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .select2-container { width: 100% !important; }
    </style>
@endsection

@section('content')
    <div class="container-xxl">
        @php
            $mobile = $invoiceItem->mobile;
            $customer = $invoiceItem->invoice->customer;
        @endphp
        
        <form action="{{ route('mobiles.buybackStore') }}" method="POST" id="buybackForm">
            @csrf
            <input type="hidden" name="invoice_item_id" value="{{ $invoiceItem->id }}">

            <div class="row">
                {{-- Device Specifications --}}
                <div class="col-12 mb-4">
                    <div class="card buyback-card">
                        <div class="card-header buyback-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Device Buyback Detail</h5>
                            <a href="{{ route('invoice.show', $invoiceItem->invoice_id) }}"
                                class="btn btn-secondary btn-sm">Back to Invoice</a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Brand</label>
                                    <input type="text" class="form-control bg-light" value="{{ $mobile->brand->name ?? 'N/A' }}" readonly>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Model Name</label>
                                    <input type="text" class="form-control bg-light" value="{{ $mobile->model->name ?? 'N/A' }}" readonly>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">RAM</label>
                                    <input type="text" class="form-control" name="ram" value="{{ $mobile->ram }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Storage</label>
                                    <input type="text" class="form-control" name="storage" value="{{ $mobile->storage }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Color</label>
                                    <input type="text" class="form-control bg-light" value="{{ $mobile->color }}" readonly>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Condition (Buying As)</label>
                                    <select class="form-select bg-light" disabled>
                                        <option value="used" selected>Used / Old</option>
                                    </select>
                                </div>
                                @if ($mobile->brand->slug == 'apple')
                                    <div class="col-md-3 mb-3">
                                        <label for="battery_health" class="form-label">Battery Health (%)</label>
                                        <input type="text" class="form-control border-primary" id="battery_health" name="battery_health"
                                            value="{{ $mobile->battery_health }}" placeholder="e.g. 95%">
                                    </div>
                                @endif
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">HSN Number</label>
                                    <input type="text" class="form-control fw-bold border-primary bg-light"
                                        value="{{ $mobile->hsn_number }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Buyback Price (₹) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="buyback_price"
                                        class="form-control border-primary" placeholder="0.00" required>
                                    <small class="text-info">Sold for: ₹{{ \App\Http\Traits\Traits::formatINR($invoiceItem->price, 2) }}</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Buyback Date <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="buyback_date" class="form-control date-picker"
                                        value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                                </div>

                                {{-- ===== Customer Information ===== --}}
                                <div class="col-12 mt-4 d-flex align-items-center gap-3">
                                    <h6 class="fw-bold mb-0">Customer Information (Selling to Store)</h6>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="newCustomerToggle" name="new_customer" value="1">
                                        <label class="form-check-label text-warning fw-semibold" for="newCustomerToggle">
                                            Different / New Customer
                                        </label>
                                    </div>
                                </div>

                                {{-- Original customer (shown when checkbox is OFF) --}}
                                <div id="originalCustomerPanel" class="col-12">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Customer Phone</label>
                                            <input type="text" class="form-control bg-light"
                                                value="{{ $customer->phone ?? 'N/A' }}" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Customer Name</label>
                                            <input type="text" class="form-control bg-light"
                                                value="{{ $customer->name ?? 'N/A' }}" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Address</label>
                                            <input type="text" class="form-control bg-light"
                                                value="{{ $customer->address ?? 'N/A' }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                {{-- New / different customer (shown when checkbox is ON) --}}
                                <div id="newCustomerPanel" class="col-12">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">
                                                Mobile Number <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" id="newCustomerPhone" name="customer_phone"
                                                class="form-control" placeholder="Enter mobile number">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Customer Name</label>
                                            <input type="text" id="newCustomerName" name="customer_name"
                                                class="form-control" placeholder="Auto-filled or enter manually">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Address</label>
                                            <input type="text" id="newCustomerAddress" name="customer_address"
                                                class="form-control" placeholder="Auto-filled or enter manually">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-5 text-end">
                                <button type="submit" class="btn btn-primary px-5 py-2">
                                    <i class="iconoir-check-circle me-1"></i>Confirm Buyback & Add to Stock
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('pageScripts')
    <script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>
    <script>
        $(document).ready(function () {

            // ---- Datepicker ----
            new Datepicker(document.querySelector('.date-picker'), {
                autoHide: true,
                format: 'yyyy-mm-dd',
            });

            // ---- New-customer toggle ----
            const $toggle        = $('#newCustomerToggle');
            const $original      = $('#originalCustomerPanel');
            const $newPanel      = $('#newCustomerPanel');
            const $phoneInput    = $('#newCustomerPhone');
            const $nameInput     = $('#newCustomerName');
            const $addressInput  = $('#newCustomerAddress');

            $toggle.on('change', function () {
                if (this.checked) {
                    $original.hide();
                    $newPanel.show();
                    $phoneInput.focus();
                } else {
                    $original.show();
                    $newPanel.hide();
                    $phoneInput.val('');
                    $nameInput.val('');
                    $addressInput.val('');
                }
            });

            // ---- Phone lookup (same as invoice create) ----
            let lookupTimer = null;

            $phoneInput.on('input', function () {
                clearTimeout(lookupTimer);
                const phone = $(this).val().trim();

                if (phone.length < 6) return;

                lookupTimer = setTimeout(function () {
                    $.ajax({
                        url: '{{ route("invoice.getCustomer") }}',
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}', phone: phone },
                        success: function (res) {
                            if (res.status && res.customer) {
                                $nameInput.val(res.customer.name);
                                $addressInput.val(res.customer.address ?? '');
                            } else {
                                $nameInput.val('');
                                $addressInput.val('');
                            }
                        },
                    });
                }, 400);
            });
        });
    </script>
@endsection
