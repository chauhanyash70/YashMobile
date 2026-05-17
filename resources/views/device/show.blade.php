@extends('layouts.app')
@section('title', 'Device Details')
@section('header_title', $header_title ?? $mobile->brand->name . ' ' . $mobile->model->name)
@section('tagline', $tagline ?? 'Comprehensive technical specifications and unit-wise transaction history.')

@section('content')
    <div class="container-xxl">
        <div class="row">
            {{-- Header Stats --}}
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <div class="col-md border-end">
                                <div class="p-4 text-center">
                                    <div
                                        class="avatar-md bg-soft-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                        <i class="iconoir-box fs-24 text-primary"></i>
                                    </div>
                                    <h6 class="text-uppercase text-muted fs-12 mb-1 fw-bold tracking-wider">Status</h6>
                                    <h2 class="mb-0 fw-bold">
                                        <span
                                            class="badge rounded-pill bg-soft-{{ $mobile->status == 'in_stock' ? 'success' : ($mobile->status == 'repair' ? 'warning' : 'danger') }}">
                                            {{ ucfirst(str_replace('_', ' ', $mobile->status)) }}
                                        </span>
                                    </h2>
                                </div>
                            </div>
                            <div class="col-md border-end">
                                <div class="p-4 text-center">
                                    <div
                                        class="avatar-md bg-soft-info rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                        <i class="iconoir-database fs-24 text-info"></i>
                                    </div>
                                    <h6 class="text-uppercase text-muted fs-12 mb-1 fw-bold tracking-wider">Storage / RAM
                                    </h6>
                                    <h4 class="mb-0 fw-bold">{{ $mobile->storage ?? 'N/A' }} / {{ $mobile->ram ?? 'N/A' }}
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md border-end">
                                <div class="p-4 text-center">
                                    <div
                                        class="avatar-md bg-soft-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                        <i class="iconoir-palette fs-24 text-warning"></i>
                                    </div>
                                    <h6 class="text-uppercase text-muted fs-12 mb-1 fw-bold tracking-wider">Color</h6>
                                    <h4 class="mb-0 fw-bold">{{ $mobile->color ?? 'N/A' }}</h4>
                                </div>
                            </div>
                            @if ($mobile->brand->slug == 'apple' || $mobile->battery_health)
                                <div class="col-md border-end">
                                    <div class="p-4 text-center">
                                        <div
                                            class="avatar-md bg-soft-danger rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                            <i class="iconoir-flash fs-24 text-danger"></i>
                                        </div>
                                        <h6 class="text-uppercase text-muted fs-12 mb-1 fw-bold tracking-wider">Battery
                                            Health</h6>
                                        <h4 class="mb-0 fw-bold">{{ $mobile->battery_health ?? 'N/A' }}</h4>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md">
                                <div class="p-4 text-center">
                                    <div
                                        class="avatar-md bg-soft-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                        <i class="iconoir-check-circle fs-24 text-success"></i>
                                    </div>
                                    <h6 class="text-uppercase text-muted fs-12 mb-1 fw-bold tracking-wider">Condition</h6>
                                    <h4 class="mb-0 fw-bold">
                                        <span
                                            class="badge rounded-pill bg-soft-{{ $mobile->condition_type == 'new' ? 'success' : 'info' }} px-3">
                                            {{ ucfirst($mobile->condition_type) }}
                                        </span>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detailed Table --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1 fw-bold">Unit Lifecycle History (HSN Number:
                                {{ $mobile->hsn_number ?: $history->first()->hsn_number ?? 'N/A' }})</h5>
                            <p class="text-muted small mb-0">Track lifecycle and profitability per transaction cycle</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('mobiles.index') }}"
                                class="btn btn-outline-secondary btn-sm d-none d-md-flex align-items-center">
                                <i class="iconoir-nav-arrow-left me-1 fs-16"></i>Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle custom-table">
                                <thead class="text-center">
                                    <tr class="border-top-0">
                                        <th rowspan="2" class="align-middle border-end">#</th>
                                        <th rowspan="2" class="align-middle border-end">Status</th>
                                        <th colspan="4" class="bg-soft-primary text-primary py-2">Purchase Details
                                            (Supplier)</th>
                                        <th colspan="2" class="bg-soft-success text-success py-2">Sale Details (Customer)
                                        </th>
                                        <th rowspan="2" class="align-middle bg-soft-warning text-warning border-start">
                                            Net Profit/Loss</th>
                                    </tr>
                                    <tr>
                                        <th class="bg-soft-primary-light small fw-bold">Supplier & Date</th>
                                        <th class="bg-soft-primary-light small fw-bold">Buy Price</th>
                                        <th class="bg-soft-primary-light small fw-bold">Repair</th>
                                        <th class="bg-soft-primary-light small fw-bold border-end">Subtotal (Cost)</th>
                                        <th class="bg-soft-success-light small fw-bold">Customer & Date</th>
                                        <th class="bg-soft-success-light small fw-bold">Sale Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $grandTotalBuy = 0;
                                        $grandTotalRepair = 0;
                                        $grandTotalCost = 0;
                                        $grandTotalSale = 0;
                                        $grandTotalDiscount = 0;
                                        $grandTotalProfit = 0;
                                    @endphp
                                    @forelse($history as $item)
                                        @php
                                            $buyTransaction = $item->transactions
                                                ->where('transaction_type', 'buy')
                                                ->first();
                                            $sellTransaction = $item->transactions
                                                ->where('transaction_type', 'sell')
                                                ->first();

                                            $buyInvoiceItem = $item
                                                ->invoiceItems()
                                                ->whereHas('invoice', function ($q) {
                                                    $q->where('invoice_type', 'buy');
                                                })
                                                ->first();

                                            $sellInvoiceItem = $item
                                                ->invoiceItems()
                                                ->whereHas('invoice', function ($q) {
                                                    $q->where('invoice_type', 'sell');
                                                })
                                                ->first();

                                            $buyPrice = $buyTransaction->price ?? 0;
                                            $repairCost = $item->repair_cost ?? 0;
                                            $totalCost = $buyPrice + $repairCost;

                                            // Calculate Gross, Discount and Net for proper display
                                            $sellDiscount = $sellInvoiceItem->discount ?? 0;
                                            $sellNet = $sellInvoiceItem
                                                ? $sellInvoiceItem->total
                                                : $sellTransaction->price ?? 0;
                                            $sellGross = $sellInvoiceItem
                                                ? $sellInvoiceItem->price * $sellInvoiceItem->qty
                                                : $sellNet;

                                            $profit = $sellNet > 0 ? $sellNet - $totalCost : 0;

                                            $grandTotalBuy += $buyPrice;
                                            $grandTotalRepair += $repairCost;
                                            $grandTotalCost += $totalCost;
                                            $grandTotalSale += $sellGross;
                                            $grandTotalDiscount += $sellDiscount;
                                            $grandTotalProfit += $profit;
                                        @endphp
                                        <tr>
                                            <td class="text-center border-end fw-bold text-muted">{{ $loop->iteration }}
                                            </td>
                                            <td class="text-center border-end">
                                                @if ($item->status == 'sold')
                                                    <span class="badge rounded-pill bg-soft-danger">Sold</span>
                                                @elseif ($item->status == 'in_stock')
                                                    <span class="badge rounded-pill bg-soft-success">In Stock</span>
                                                @else
                                                    <span
                                                        class="badge rounded-pill bg-soft-secondary">{{ ucfirst($item->status) }}</span>
                                                @endif
                                            </td>
                                            {{-- Purchase Details --}}
                                            <td>
                                                @if ($buyTransaction)
                                                    <div class="d-flex align-items-center">
                                                        <div class="d-flex flex-column">
                                                            <a href="{{ route('customers.show', $buyTransaction->customer_id) }}"
                                                                class="fw-semibold text-dark fs-13 hover-primary">
                                                                {{ $buyTransaction->customer->name ?? 'Unknown' }}
                                                            </a>
                                                            <div class="d-flex align-items-center text-muted mt-1"
                                                                style="font-size: 11px;">
                                                                <i
                                                                    class="iconoir-calendar me-1"></i>{{ \Carbon\Carbon::parse($buyTransaction->transaction_date)->format('d M, Y') }}
                                                                <span class="mx-1">•</span>
                                                                @if ($buyInvoiceItem)
                                                                    <a href="{{ route('invoice.show', $buyInvoiceItem->invoice_id) }}"
                                                                        class="text-info fw-bold hover-primary">
                                                                        #{{ $buyTransaction->invoice_no }}
                                                                    </a>
                                                                @else
                                                                    <span
                                                                        class="text-info fw-bold">#{{ $buyTransaction->invoice_no }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-medium fs-13">₹{{ number_format($buyPrice, 2) }}</td>
                                            <td class="text-end text-danger fs-13">₹{{ number_format($repairCost, 2) }}
                                            </td>
                                            <td class="text-end fw-bold fs-13 border-end bg-soft-primary-light">
                                                ₹{{ number_format($totalCost, 2) }}</td>

                                            {{-- Sale Details --}}
                                            <td>
                                                @if ($sellTransaction)
                                                    <div class="d-flex align-items-center">
                                                        <div class="d-flex flex-column">
                                                            <a href="{{ route('customers.show', $sellTransaction->customer_id) }}"
                                                                class="fw-semibold text-dark fs-13 hover-primary">
                                                                {{ $sellTransaction->customer->name ?? 'Walking Customer' }}
                                                            </a>
                                                            <div class="d-flex align-items-center text-muted mt-1"
                                                                style="font-size: 11px;">
                                                                <i
                                                                    class="iconoir-calendar me-1"></i>{{ \Carbon\Carbon::parse($sellTransaction->transaction_date)->format('d M, Y') }}
                                                                <span class="mx-1">•</span>
                                                                @if ($sellInvoiceItem)
                                                                    <a href="{{ route('invoice.show', $sellInvoiceItem->invoice_id) }}"
                                                                        class="text-success fw-bold hover-primary">
                                                                        #{{ $sellTransaction->invoice_no }}
                                                                    </a>
                                                                @else
                                                                    <span
                                                                        class="text-success fw-bold">#{{ $sellTransaction->invoice_no }}</span>
                                                                @endif
                                                            </div>
                                                            @php
                                                                $sellItem = $item
                                                                    ->invoiceItems()
                                                                    ->whereHas('invoice', function ($q) {
                                                                        $q->where('invoice_type', 'sell');
                                                                    })
                                                                    ->first();
                                                            @endphp
                                                            @php
                                                                $isCurrentlyInStock = $history->contains(
                                                                    'status',
                                                                    'in_stock',
                                                                );
                                                            @endphp
                                                            @if (!$isCurrentlyInStock && $loop->first && $item->status == 'sold' && $sellItem && !$sellItem->is_bought_back)
                                                                <div class="mt-2">
                                                                    <a href="{{ route('mobiles.buyback', $sellItem->id) }}"
                                                                        class="btn btn-xs btn-outline-primary py-0 px-2 fs-10 fw-bold rounded-pill">
                                                                        <i class="iconoir-refresh me-1"></i>Initiate
                                                                        Buyback
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="text-center d-flex flex-column gap-1 align-items-center">
                                                        @if ($item->status == 'in_stock')
                                                            <a href="{{ route('invoice.create', ['hsn_number' => $item->hsn_number]) }}"
                                                                class="btn btn-xs btn-success rounded-pill px-3 fw-bold w-100 mb-1">
                                                                <i class="iconoir-cart me-1"></i>Sell Now
                                                            </a>
                                                            <a href="{{ route('repairs.create', ['mobile_id' => $item->id]) }}"
                                                                class="btn btn-xs btn-outline-warning rounded-pill px-3 fw-bold w-100">
                                                                <i class="iconoir-tools me-1"></i>Repair
                                                            </a>
                                                        @elseif($item->status == 'repair')
                                                            <a href="{{ route('repairs.index', ['mobile_id' => $item->id]) }}"
                                                                class="btn btn-xs btn-warning rounded-pill px-3 fw-bold w-100">
                                                                <i class="iconoir-tools me-1"></i>In Repair
                                                            </a>
                                                        @else
                                                            <span class="text-muted fs-12">-</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-end fw-medium fs-13">
                                                @if ($sellGross > 0)
                                                    <div class="d-flex flex-column align-items-end">
                                                        <span>₹{{ number_format($sellGross, 2) }}</span>
                                                        @if ($sellDiscount > 0)
                                                            <small
                                                                class="text-danger">-₹{{ number_format($sellDiscount, 2) }}</small>
                                                            <div class="border-top w-50 mt-1"></div>
                                                            <span
                                                                class="fw-bold text-success">₹{{ number_format($sellNet, 2) }}</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            {{-- Calculation --}}
                                            <td class="text-end border-start bg-theme-light calculation-cell">
                                                @if ($sellNet > 0)
                                                    <div class="d-flex flex-column align-items-end">
                                                        <span
                                                            class="fw-bold {{ $profit >= 0 ? 'text-success' : 'text-danger' }} fs-13">
                                                            ₹{{ number_format($profit, 2) }}
                                                        </span>
                                                        <span
                                                            class="badge {{ $profit >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} py-0 px-1 mt-1"
                                                            style="font-size: 10px;">
                                                            {{ $totalCost > 0 ? number_format(($profit / $totalCost) * 100, 1) : '0.0' }}%
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="iconoir-info-empty fs-36 mb-2"></i>
                                                    <p class="mb-0">No transaction history found for this unit.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if ($history->count() > 0)
                                    <tfoot class="bg-light fw-bold border-top-2">
                                        <tr>
                                            <td colspan="3" class="text-end py-3">GRAND TOTALS</td>
                                            <td class="text-end">₹{{ number_format($grandTotalBuy, 2) }}</td>
                                            <td class="text-end text-danger">₹{{ number_format($grandTotalRepair, 2) }}
                                            </td>
                                            <td class="text-end border-end bg-soft-primary-light">
                                                ₹{{ number_format($grandTotalCost, 2) }}</td>
                                            <td class="text-end">
                                                <div class="d-flex flex-column align-items-end">
                                                    <span>₹{{ number_format($grandTotalSale, 2) }}</span>
                                                    @if ($grandTotalDiscount > 0)
                                                        <div class="border-top w-50 mt-1"></div>
                                                        <span
                                                            class="text-success">₹{{ number_format($grandTotalSale - $grandTotalDiscount, 2) }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-end text-danger">₹{{ number_format($grandTotalDiscount, 2) }}
                                            </td>
                                            <td class="text-end border-start bg-theme-light">
                                                <div class="d-flex flex-column align-items-end">
                                                    <span
                                                        class="{{ $grandTotalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                                        ₹{{ number_format($grandTotalProfit, 2) }}
                                                    </span>
                                                    @if ($grandTotalCost > 0)
                                                        <span
                                                            class="badge {{ $grandTotalProfit >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} py-0 px-1 mt-1"
                                                            style="font-size: 10px;">
                                                            {{ number_format(($grandTotalProfit / $grandTotalCost) * 100, 1) }}%
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
