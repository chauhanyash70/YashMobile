@extends('layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card">
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
                                            <span class="form-control-plaintext">#{{ $invoice->invoice_no }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group row justify-content-end mb-2">
                                        <h5 class="col-lg-3 text-end mb-0 fw-semibold align-self-center">
                                            <span class="text-muted">Invoice Date:</span>
                                        </h5>
                                        <div class="col-lg-3">
                                            <span class="form-control-plaintext">{{ $invoice->invoice_date }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group row justify-content-end mb-2">
                                        <h5 class="col-lg-3 text-end mb-0 fw-semibold align-self-center">
                                            <span class="text-muted">Payment Method:</span>
                                        </h5>
                                        <div class="col-lg-3">
                                            <span
                                                class="form-control-plaintext">{{ $invoice->payment_method ?? 'N/A' }}</span>
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
                                    <p class="mb-1"><strong>Mobile:</strong>
                                        {{ $invoice->customer ? $invoice->customer->phone : 'N/A' }}</p>
                                    <p class="my-1"><strong>Name:</strong>
                                        {{ $invoice->customer ? $invoice->customer->name : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-3 col-md-4 col-xl-4 d-print-flex align-self-center">
                                <div class="">
                                    <address class="fs-13">
                                        <strong class="fs-14">Billed To :</strong><br>
                                        <p>{{ $invoice->customer ? $invoice->customer->address : 'N/A' }}</p>
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
                                                <th>Items</th>
                                                <th>Quantity</th>
                                                <th>Price (₹)</th>
                                                <th>Discount (₹)</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($invoice->items as $item)
                                                <tr>
                                                    <td style="min-width: 250px;">
                                                        <p class="fw-bold mb-1">
                                                            @if ($item->item)
                                                                {{ $item->item->name ?? $item->item->brand->name . ' ' . $item->item->model->name }}
                                                            @else
                                                                Item Deleted
                                                            @endif
                                                        </p>
                                                        @if ($item->item)
                                                            <div class="row mt-1 g-1">
                                                                <div class="col-12">
                                                                    <small class="text-muted">
                                                                        @if ($item->item_type == 'device')
                                                                            @if ($item->item->color)
                                                                                Color: {{ $item->item->color }} |
                                                                            @endif
                                                                            @if ($item->item->storage)
                                                                                Storage: {{ $item->item->storage }} |
                                                                            @endif
                                                                            @if ($item->item->ram)
                                                                                RAM: {{ $item->item->ram }} |
                                                                            @endif
                                                                            @if ($item->deviceImei)
                                                                                IMEI: {{ $item->deviceImei->imei }}
                                                                            @elseif($item->imei_or_serial_number)
                                                                                IMEI/HSN: {{ $item->imei_or_serial_number }}
                                                                            @endif
                                                                        @elseif($item->item_type == 'accessory')
                                                                            @if ($item->item->sku)
                                                                                SKU: {{ $item->item->sku }}
                                                                            @endif
                                                                        @endif
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td style="min-width: 150px; max-width: 120px;">
                                                        {{ $item->quantity }}
                                                    </td>
                                                    <td style="min-width: 100px; max-width: 130px;">
                                                        {{ number_format($item->price, 2) }}
                                                    </td>
                                                    <td style="min-width: 100px; max-width: 130px;">
                                                        {{ number_format($item->discount ?? 0, 2) }}
                                                    </td>
                                                    <td style="min-width: 120px; max-width: 130px;">
                                                        ₹{{ number_format($item->total, 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td class="border-0"></td>
                                                <td class="border-0 fs-14 text-dark"></td>
                                                <td class="border-0 fs-14 text-dark"></td>
                                                <td class="border-0 fs-14 text-dark"><b>Discount</b></td>
                                                <td class="border-0 fs-14 text-dark"><b>₹
                                                        {{ number_format($invoice->items->sum('discount') ?? 0, 2) }}</b>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 fs-14 text-dark"></td>
                                                <td class="border-0 fs-14 text-dark"></td>
                                                <td class="border-0 fs-14 text-dark"></td>
                                                <td class="border-0 fs-14 text-dark"><b>Total</b></td>
                                                <td class="border-0 fs-14 text-dark"><b>₹
                                                        {{ number_format($invoice->total_amount, 2) }}</b></td>
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
                                            <li><small class="fs-14">Warranty claims will be handled by authorized service
                                                    centers only.</small></li>
                                        </ul>
                                    </li>

                                    <li>
                                        <small class="fs-14 fw-bold">2. Warranty on Old/Used Mobiles</small>
                                        <ul class="ps-3">
                                            <li><small class="fs-14">24 Hours store warranty is provided (only for internal
                                                    hardware issues).</small></li>
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
                                            <li><small class="fs-14">Local/unbranded accessories are sold without warranty
                                                    unless specified.</small></li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>

                            <div class="col-lg-6 align-self-center">
                                <div class="float-none float-md-end" style="width: 30%;">
                                    <small>Account Manager</small>
                                    <img src="{{ asset('admin-assets/images/signature.png') }}" alt="" class="mt-2 mb-1"
                                        height="65">
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
                                    <a href="{{ route('invoice.generatePdf', $invoice->id) }}"
                                        class="btn btn-secondary">Download PDF</a>
                                    <a href="{{ route('invoice.index') }}" class="btn btn-danger">Back</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection