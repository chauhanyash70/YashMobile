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
                                    <label class="form-label">Storage</label>
                                    <input type="text" class="form-control bg-light" value="{{ $mobile->storage }}" readonly>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">RAM</label>
                                    <input type="text" class="form-control bg-light" value="{{ $mobile->ram }}" readonly>
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
                                    <small class="text-info">Sold for: ₹{{ number_format($invoiceItem->price, 2) }}</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Buyback Date <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="buyback_date" class="form-control date-picker"
                                        value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                                </div>

                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold">Customer Information (Selling to Store)</h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small text-muted">Customer Phone</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ $customer->phone ?? 'N/A' }}" readonly>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small text-muted">Customer Name</label>
                                    <input type="text" class="form-control bg-light"
                                        value="{{ $customer->name ?? 'N/A' }}" readonly>
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
        $(document).ready(function() {
            new Datepicker(document.querySelector('.date-picker'), {
                autoHide: true,
                format: 'yyyy-mm-dd',
            });
        });
    </script>
@endsection
