@extends('layouts.app')
@section('title', 'Invoice Details')

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

                                <div class="mt-3">
                                    <div class="form-group row mb-2">
                                        <h5 class="col-lg-3 text-start mb-0 fw-semibold align-self-center">
                                            <span class="text-muted">Type:</span>
                                        </h5>
                                        <div class="col-lg-9">
                                            <span class="badge bg-soft-info">{{ ucfirst($invoice->invoice_type) }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <h5 class="col-lg-3 text-start mb-0 fw-semibold align-self-center">
                                            <span class="text-muted">Payment:</span>
                                        </h5>
                                        <div class="col-lg-9">
                                            <span
                                                class="form-control-plaintext d-inline-block">{{ $invoice->payment_method == 'bajaj_finance' ? 'Bajaj Finance' : ucfirst($invoice->payment_method) }}</span>
                                            @if ($invoice->bajaj_approval_number)
                                                <span class="badge bg-soft-primary border border-primary text-primary">Appr
                                                    No: {{ $invoice->bajaj_approval_number }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
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
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-3 d-flex justify-content-md-between">
                            <div class="col-12 col-md-3 col-md-4 col-xl-4 d-print-flex align-self-center">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <strong
                                            class="fs-14">{{ $invoice->invoice_type == 'sell' ? 'Invoice to' : 'Purchase from' }}
                                            :</strong><br>
                                        <p class="mb-1"><strong>Mobile:</strong>
                                            {{ $invoice->customer ? $invoice->customer->phone : 'N/A' }}</p>
                                        <p class="my-1"><strong>Name:</strong>
                                            <a href="{{ route('customers.show', $invoice->customer_id) }}"
                                                class="text-primary fw-bold" style="text-decoration: none;">
                                                {{ $invoice->customer ? $invoice->customer->name : 'N/A' }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3 col-md-4 col-xl-4 d-print-flex align-self-center">
                                <div class="">
                                    <address class="fs-13">
                                        <strong class="fs-14">Address :</strong><br>
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
                                                <th>Total</th>
                                                <th class="d-print-none">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($invoice->items as $item)
                                                <tr>
                                                    <td style="min-width: 250px;">
                                                        <p class="fw-bold mb-1">
                                                            @if ($item->mobile)
                                                                <a href="{{ route('mobiles.show', $item->mobile_id) }}"
                                                                    class="text-dark fw-bold hover-primary"
                                                                    style="text-decoration: none;">
                                                                    {{ $item->mobile->brand->name ?? '' }}
                                                                    {{ $item->mobile->model->name ?? '' }}
                                                                </a>
                                                            @elseif ($item->accessory)
                                                                <a href="{{ route('accessories.edit', $item->accessory_id) }}"
                                                                    class="text-dark fw-bold hover-primary"
                                                                    style="text-decoration: none;">
                                                                    {{ $item->accessory->brand->name ?? '' }}
                                                                    {{ $item->accessory->name ?? '' }}
                                                                    {{ $item->accessory->model ? '(' . $item->accessory->model . ')' : '' }}
                                                                </a>
                                                            @else
                                                                Item Deleted
                                                            @endif
                                                        </p>
                                                        @if ($item->mobile)
                                                            <div class="row mt-1 g-1">
                                                                <div class="col-12">
                                                                    <small class="text-muted">
                                                                        @if ($item->mobile->color)
                                                                            Color: {{ $item->mobile->color }} |
                                                                        @endif
                                                                        @if ($item->mobile->storage)
                                                                            Storage: {{ $item->mobile->storage }} |
                                                                        @endif
                                                                        @if ($item->mobile->hsn_number)
                                                                            HSN Number: {{ $item->mobile->hsn_number }}
                                                                        @endif
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        @elseif ($item->accessory)
                                                            <div class="row mt-1 g-1">
                                                                <div class="col-12">
                                                                    <small class="text-muted">
                                                                        @if ($item->accessory->color)
                                                                            Color: {{ $item->accessory->color }} |
                                                                        @endif
                                                                        @if ($item->accessory->hsn)
                                                                            HSN: {{ $item->accessory->hsn }}
                                                                        @endif
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td style="min-width: 80px;">
                                                        {{ $item->qty }}
                                                    </td>
                                                    <td style="min-width: 100px;">
                                                        {{ number_format($item->price, 2) }}
                                                    </td>
                                                    <td style="min-width: 120px;">
                                                        ₹{{ number_format($item->total, 2) }}
                                                    </td>
                                                    <td class="text-center d-print-none">
                                                        @if ($item->mobile)
                                                            <div class="btn-group">
                                                                <a href="{{ route('mobiles.show', $item->mobile_id) }}"
                                                                    class="btn btn-sm btn-outline-info">
                                                                    <i class="iconoir-eye me-1"></i>View
                                                                </a>
                                                                @if ($invoice->invoice_type == 'sell' && !$item->is_bought_back)
                                                                    <a href="{{ route('mobiles.buyback', $item->id) }}"
                                                                        class="btn btn-sm btn-outline-primary">
                                                                        <i class="iconoir-refresh me-1"></i>Buyback
                                                                    </a>
                                                                @elseif($item->is_bought_back)
                                                                    <span class="badge bg-soft-success">
                                                                        <i class="iconoir-check-circle me-1"></i>Returned
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @elseif ($item->accessory)
                                                            <div class="btn-group">
                                                                <a href="{{ route('accessories.edit', $item->accessory_id) }}"
                                                                    class="btn btn-sm btn-outline-info">
                                                                    <i class="iconoir-edit me-1"></i>Edit
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            @if ($invoice->tax_amount > 0)
                                                <tr>
                                                    <td colspan="3" class="border-0 text-end fs-14 text-dark">Tax</td>
                                                    <td class="border-0 fs-14 text-dark">₹
                                                        {{ number_format($invoice->tax_amount, 2) }}</td>
                                                    <td class="border-0 d-print-none"></td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td colspan="3" class="border-0 text-end fs-14 text-dark text-success">
                                                    Total Amount</td>
                                                <td class="border-0 fs-14 text-dark text-success">₹
                                                    {{ number_format($invoice->paid_amount, 2) }}</td>
                                                <td class="border-0 d-print-none"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <h5 class="mt-4">Notes :</h5>
                                <p>{{ $invoice->notes ?? 'No additional notes.' }}</p>
                            </div>

                            <div class="col-lg-6 align-self-center">
                                <div class="float-none float-md-end" style="width: 30%;">
                                    <small>Authorized Signatory</small>
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
                                    <small class="fs-12">Thank You For Your Business.</small>
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
