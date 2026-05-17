@extends('layouts.app')
@section('title', 'Customer Details')
@section('header_title', $header_title ?? $customer->name)
@section('tagline', $tagline ?? 'Profile, contact information, and transaction history for this customer.')

@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card overflow-hidden">
                    <div class="card-body p-0">
                        <div class="profile-cover-bg p-5 d-flex align-items-end dynamic-mesh-gradient">
                            <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25"
                                style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');">
                            </div>
                        </div>
                        <div class="p-4 pt-1">
                            <div class="row align-items-end">
                                <div class="col-auto">
                                    <div class="mt-n5 position-relative">
                                        <a href="{{ $customer->profile_url }}" class="lightbox">
                                            <img src="{{ $customer->profile_url }}" alt="" height="120"
                                                width="120"
                                                class="rounded-circle border border-4 border-card-bg shadow-sm object-fit-cover">
                                        </a>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-2">
                                        <h3 class="fw-bold mb-1">{{ $customer->name }}</h3>

                                    </div>
                                </div>
                                <div class="col-md-auto text-end">
                                    <div class="d-flex gap-2 mb-2">
                                        <div class="text-center bg-light rounded-3 p-2 px-3 border">
                                            <h5 class="mb-0 fw-bold">{{ $customer->invoices->count() }}</h5>
                                            <p class="text-muted mb-0 small">Total Invoices</p>
                                        </div>
                                        <div class="text-center bg-light rounded-3 p-2 px-3 border">
                                            <h5 class="mb-0 fw-bold">
                                                {{ number_format($customer->invoices->sum('grand_total'), 0) }}</h5>
                                            <p class="text-muted mb-0 small">Total Spent</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-transparent border-bottom d-flex align-items-center">
                        <i class="iconoir-user-circle me-2 text-primary fs-18"></i>
                        <h4 class="card-title mb-0">Contact Information</h4>
                    </div>
                    <div class="card-body p-2">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex align-items-center px-2 border-0">
                                <div class="bg-soft-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 40px; height: 40px;">
                                    <i class="iconoir-phone text-primary fs-18"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0 small">Phone</p>
                                    <h6 class="mb-0">{{ $customer->phone ?? 'N/A' }}</h6>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center px-2 border-0">
                                <div class="bg-soft-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 40px; height: 40px;">
                                    <i class="iconoir-mail text-primary fs-18"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0 small">Email</p>
                                    <h6 class="mb-0">{{ $customer->email ?? 'N/A' }}</h6>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center px-2 border-0">
                                <div class="bg-soft-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 40px; height: 40px;">
                                    <i class="iconoir-pin text-primary fs-18"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0 small">Address</p>
                                    <h6 class="mb-0">{{ $customer->address ?? 'N/A' }}</h6>
                                </div>
                            </li>
                        </ul>

                        @if ($customer->documents)
                            <div class="mt-4">
                                <h6 class="mb-2">Documents</h6>
                                <div class="d-grid gap-2">
                                    @foreach ($customer->documents as $index => $doc)
                                        <div class="d-flex align-items-center p-2 border rounded bg-light">
                                            <i class="iconoir-page me-2 text-primary"></i>
                                            <span class="small text-truncate">Doc {{ $index + 1 }}</span>
                                            <div class="ms-auto">
                                                @if (Str::endsWith($doc, ['.jpg', '.jpeg', '.png', '.gif', '.webp']))
                                                    <a href="{{ asset('storage/' . $doc) }}"
                                                        class="text-primary me-2 lightbox"><i class="fas fa-eye"></i></a>
                                                @else
                                                    <a href="{{ asset('storage/' . $doc) }}" target="_blank"
                                                        class="text-primary me-2"><i class="fas fa-eye"></i></a>
                                                @endif
                                                <a href="{{ asset('storage/' . $doc) }}" download class="text-success"><i
                                                        class="fas fa-download"></i></a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 d-flex gap-2">
                            <a href="{{ route('customers.edit', $customer->id) }}"
                                class="btn btn-warning btn-sm w-100">Edit</a>
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm w-100">Back</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="iconoir-reports me-2 text-primary fs-18"></i>
                            <h4 class="card-title mb-0">Transaction History</h4>
                        </div>
                        <span
                            class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ $customer->invoices->count() }}
                            Invoices</span>
                    </div>
                    <div class="card-body">
                        @if ($customer->invoices->isEmpty())
                            <div class="text-center py-5">
                                <i class="iconoir-search-window fs-48 text-muted mb-3 d-block"></i>
                                <p class="text-muted">No transaction history found for this customer.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light-subtle">
                                        <tr>
                                            <th class="ps-3">Invoice</th>
                                            <th>Item Details</th>
                                            <th>Date</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-end pe-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($customer->invoices as $invoice)
                                            @php
                                                $item = $invoice->items->first();
                                            @endphp
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-soft-primary rounded me-2 d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <i class="iconoir-page text-primary fs-14"></i>
                                                        </div>
                                                        <div>
                                                            <span
                                                                class="fw-bold text-dark">#{{ $invoice->invoice_no }}</span>
                                                            <br>
                                                            <small class="text-uppercase text-muted fw-semibold"
                                                                style="font-size: 9px;">{{ $invoice->invoice_type }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($item?->mobile)
                                                        <div>
                                                            <h6 class="mb-0 fs-13 text-dark">
                                                                {{ $item->mobile->brand->name ?? '' }}
                                                                {{ $item->mobile->model->name ?? 'N/A' }}</h6>
                                                            <small class="text-muted">
                                                                HSN : <span
                                                                    class="text-dark">{{ $item->mobile->hsn_number }}</span>
                                                            </small>
                                                        </div>
                                                    @else
                                                        <span class="text-muted small">Accessory/Other</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center text-muted">
                                                        <i class="iconoir-calendar me-1 fs-12"></i>
                                                        <small>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M, Y') }}</small>
                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold text-dark">
                                                    ₹{{ number_format($invoice->grand_total, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    @if ($invoice->payment_status == 'paid')
                                                        <span
                                                            class="badge bg-success-subtle text-success rounded-pill px-2">Paid</span>
                                                    @elseif($invoice->payment_status == 'partial')
                                                        <span
                                                            class="badge bg-warning-subtle text-warning rounded-pill px-2">Partial</span>
                                                    @else
                                                        <span
                                                            class="badge bg-danger-subtle text-danger rounded-pill px-2">Unpaid</span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-3">
                                                    <a href="{{ route('invoice.show', $invoice->id) }}"
                                                        class="btn btn-sm btn-light border-0 shadow-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center"
                                                        style="width: 28px; height: 28px;">
                                                        <i class="iconoir-eye fs-14 text-primary"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
