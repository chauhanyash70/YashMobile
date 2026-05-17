@extends('layouts.app')
@section('title', 'HSN Number History')
@section('header_title', $header_title ?? 'Unit Lifecycle History')
@section('tagline', $tagline ?? 'Detailed audit trail and profitability analysis for ' . $imei->brand->name . ' ' .
    $imei->model->name)


@section('content')
    <div class="container-xxl pb-5">
        {{-- Header Section --}}
        <div class="page-header-card p-4 mb-4 bg-soft-primary" style="">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary text-white p-3 rounded-4 shadow-sm">
                            <i class="iconoir-activity fs-1"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-1">Unit Lifecycle History</h2>
                            <p class="text-muted mb-0 fs-5">
                                <span
                                    class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill me-2">{{ $imei->hsn_number }}</span>
                                {{ $imei->brand->name ?? '' }} {{ $imei->model->name ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('mobiles.show', $imei->id) }}"
                        class="btn btn-light rounded-pill px-4 shadow-sm border">
                        <i class="iconoir-nav-arrow-left me-2"></i>Back to Device
                    </a>
                </div>
            </div>
        </div>

        {{-- Summary Stats --}}
        @php
            $totalCycles = $history->count();
            $currentStatus = $imei->status;
            $totalProfit = 0;
            $totalInvestment = 0;
            foreach ($history as $h) {
                $buy = $h->transactions->where('transaction_type', 'buy')->first();
                $sell = $h->transactions->where('transaction_type', 'sell')->first();
                $sellItem = $h
                    ->invoiceItems()
                    ->whereHas('invoice', function ($q) {
                        $q->where('invoice_type', 'sell');
                    })
                    ->first();

                $repair = $h->repair_cost ?? 0;
                $buyPrice = $buy->price ?? 0;
                $sellPrice = $sellItem ? $sellItem->total : $sell->price ?? 0;

                $totalProfit += $sellPrice > 0 ? $sellPrice - ($buyPrice + $repair) : 0;
                $totalInvestment += $buyPrice + $repair;
            }
        @endphp

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <span class="text-muted small">Current Status</span>
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <div class="rounded-circle"
                            style="width: 10px; height: 10px; background-color: {{ $currentStatus == 'in_stock' ? '#10b981' : '#ef4444' }}">
                        </div>
                        <span class="fw-bold">{{ ucfirst(str_replace('_', ' ', $currentStatus)) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <span class="text-muted small">Total Lifecycles</span>
                    <span class="fw-bold fs-4 mt-1">{{ $totalCycles }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <span class="text-muted small">Total Investment</span>
                    <span class="fw-bold fs-4 mt-1">₹{{ number_format($totalInvestment, 2) }}</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <span class="text-muted small">Accumulated Profit</span>
                    <span
                        class="fw-bold fs-4 mt-1 {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">₹{{ number_format($totalProfit, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="timeline">
            @foreach ($history as $h)
                <div class="card mb-4 border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-soft-primary border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">Lifecycle Entry #{{ $loop->iteration }}</h6>
                            <span
                                class="badge bg-white text-primary rounded-pill border">{{ $h->created_at->format('d M, Y') }}</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Event Type</th>
                                        <th>Date</th>
                                        <th>Party</th>
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Reference</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($h->transactions->sortBy('transaction_date') as $t)
                                        <tr>
                                            <td>
                                                <span
                                                    class="badge bg-soft-{{ $t->transaction_type == 'buy' ? 'success' : 'info' }} text-{{ $t->transaction_type == 'buy' ? 'success' : 'info' }} rounded-pill px-3">
                                                    {{ $t->transaction_type == 'buy' ? 'Purchased' : 'Sold' }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($t->transaction_date)->format('d M, Y') }}</td>
                                            <td>{{ $t->customer->name ?? 'N/A' }}</td>
                                            <td
                                                class="fw-bold text-{{ $t->transaction_type == 'buy' ? 'danger' : 'success' }}">
                                                @php
                                                    $displayPrice = $t->price;
                                                    if ($t->transaction_type == 'sell') {
                                                        $invItem = $h->invoiceItems
                                                            ->where('transaction_id', $t->id)
                                                            ->first();
                                                        if ($invItem) {
                                                            $displayPrice = $invItem->total;
                                                        }
                                                    }
                                                @endphp
                                                {{ $t->transaction_type == 'buy' ? '-' : '+' }}₹{{ number_format($displayPrice, 2) }}
                                            </td>
                                            <td>
                                                @if ($t->transaction_type == 'sell' && isset($invItem) && $invItem && $invItem->discount > 0)
                                                    <span
                                                        class="text-danger fw-bold">₹{{ number_format($invItem->discount, 2) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted small">Inv: {{ $t->invoice_no }}</span>
                                            </td>
                                            <td>
                                                @if ($t->transaction_type == 'sell' && $h->status == 'sold' && $loop->parent->first)
                                                    @php
                                                        $invoiceItem = $h->invoiceItems
                                                            ->where('transaction_id', $t->id)
                                                            ->first();
                                                    @endphp
                                                    @if ($invoiceItem && !$invoiceItem->is_bought_back)
                                                        <a href="{{ route('mobiles.buyback', $invoiceItem->id) }}"
                                                            class="btn btn-sm btn-outline-danger rounded-pill py-0 px-2 fs-10">
                                                            <i class="iconoir-refresh me-1"></i>Buyback
                                                        </a>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if ($h->repair_cost > 0)
                                        <tr class="table-light">
                                            <td>
                                                <span class="badge bg-soft-warning text-warning rounded-pill px-3">Repair
                                                    Cost</span>
                                            </td>
                                            <td>{{ $h->created_at->format('d M, Y') }}</td>
                                            <td>Internal</td>
                                            <td class="fw-bold text-danger">-₹{{ number_format($h->repair_cost, 2) }}</td>
                                            <td><span class="text-muted">-</span></td>
                                            <td colspan="2"><span class="text-muted small">Maintenance</span></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
