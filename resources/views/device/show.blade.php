@extends('layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ $device->brand->name }} {{ $device->model->name }}</h4>
                        <div>
                            <a href="{{ route('mobiles.edit', $device->id) }}" class="btn btn-info btn-sm">
                                <i class="iconoir-edit-pencil me-1"></i>Edit
                            </a>
                            <a href="{{ route('mobiles.index') }}" class="btn btn-secondary btn-sm">
                                <i class="iconoir-nav-arrow-left me-1"></i>Back to List
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="p-3 border rounded bg-light">
                                    <h6 class="text-uppercase text-muted fs-12 mb-2">Total Stock</h6>
                                    <h3 class="mb-0">{{ $device->stock }}</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 border rounded bg-light">
                                    <h6 class="text-uppercase text-muted fs-12 mb-2">Storage / RAM</h6>
                                    <h5 class="mb-0">{{ $device->storage ?? 'N/A' }} / {{ $device->ram ?? 'N/A' }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 border rounded bg-light">
                                    <h6 class="text-uppercase text-muted fs-12 mb-2">Color</h6>
                                    <h5 class="mb-0">{{ $device->color ?? 'N/A' }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 border rounded bg-light">
                                    <h6 class="text-uppercase text-muted fs-12 mb-2">Condition</h6>
                                    <h5 class="mb-0"><span
                                            class="badge bg-soft-info">{{ ucfirst($device->condition) }}</span></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">IMEI-Wise Details & Calculations</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th rowspan="2" class="align-middle">IMEI Number</th>
                                        <th rowspan="2" class="align-middle">Status</th>
                                        <th colspan="3" class="bg-soft-primary">Purchase Details (Supplier)</th>
                                        <th colspan="2" class="bg-soft-success">Sale Details (Customer)</th>
                                        <th rowspan="2" class="align-middle bg-soft-warning">Net Profit/Loss</th>
                                    </tr>
                                    <tr>
                                        <th class="bg-soft-primary">Supplier & Date</th>
                                        <th class="bg-soft-primary">Buy Price</th>
                                        <th class="bg-soft-primary">Repair</th>
                                        <th class="bg-soft-success">Customer & Date</th>
                                        <th class="bg-soft-success">Sale Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($device->imeis as $imei)
                                        @php
                                            $purchaseItem = $imei->purchaseItem;
                                            $invoiceItem = $imei->invoiceItem;

                                            $buyPrice = $purchaseItem->price ?? 0;
                                            $repairCost = $purchaseItem->repair_cost ?? 0;
                                            $totalCost = $buyPrice + $repairCost;

                                            $sellPrice = $invoiceItem->price ?? 0;
                                            $profit = $sellPrice > 0 ? ($sellPrice - $totalCost) : 0;
                                        @endphp
                                        <tr>
                                            <td class="fw-bold">{{ $imei->imei }}</td>
                                            <td class="text-center">
                                                @if ($imei->status == 'available')
                                                    <span class="badge bg-success">Available</span>
                                                @elseif($imei->status == 'sold')
                                                    <span class="badge bg-danger">Sold</span>
                                                @else
                                                    <span class="badge bg-warning">{{ ucfirst($imei->status) }}</span>
                                                @endif
                                            </td>
                                            {{-- Purchase Details --}}
                                            <td>
                                                @if($purchaseItem)
                                                    <div class="d-flex flex-column">
                                                        <span
                                                            class="fw-semibold">{{ $purchaseItem->purchase->supplier->name ?? 'Unknown' }}</span>
                                                        <small
                                                            class="text-muted">{{ \Carbon\Carbon::parse($purchaseItem->purchase->purchase_date)->format('d M, Y') }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-end">₹{{ number_format($buyPrice, 2) }}</td>
                                            <td class="text-end text-danger">₹{{ number_format($repairCost, 2) }}</td>

                                            {{-- Sale Details --}}
                                            <td>
                                                @if($invoiceItem)
                                                    <div class="d-flex flex-column">
                                                        <span
                                                            class="fw-semibold">{{ $invoiceItem->invoice->customer->name ?? 'Walking Customer' }}</span>
                                                        <small
                                                            class="text-muted">{{ \Carbon\Carbon::parse($invoiceItem->invoice->invoice_date)->format('d M, Y') }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted text-center d-block">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($sellPrice > 0)
                                                    ₹{{ number_format($sellPrice, 2) }}
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            {{-- Calculation --}}
                                            <td class="text-end fw-bold {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
                                                @if($sellPrice > 0)
                                                    ₹{{ number_format($profit, 2) }}
                                                    <br>
                                                    <small
                                                        class="fs-10">({{ $totalCost > 0 ? number_format(($profit / $totalCost) * 100, 1) : '0.0' }}%)</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                No IMEIs registered for this device.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @php
                                    $totalBuy = $device->imeis->sum(fn($i) => $i->purchaseItem->price ?? 0);
                                    $totalRepair = $device->imeis->sum(fn($i) => $i->purchaseItem->repair_cost ?? 0);
                                    $totalSell = $device->imeis->sum(fn($i) => $i->invoiceItem->price ?? 0);
                                    $totalProfit = $device->imeis->filter(fn($i) => ($i->invoiceItem->price ?? 0) > 0)->sum(function ($i) {
                                        return ($i->invoiceItem->price ?? 0) - (($i->purchaseItem->price ?? 0) + ($i->purchaseItem->repair_cost ?? 0));
                                    });
                                @endphp
                                <tfoot class="table-light fw-bold text-end">
                                    <tr>
                                        <td colspan="3">Totals:</td>
                                        <td>₹{{ number_format($totalBuy, 2) }}</td>
                                        <td class="text-danger">₹{{ number_format($totalRepair, 2) }}</td>
                                        <td></td>
                                        <td>₹{{ number_format($totalSell, 2) }}</td>
                                        <td class="text-center {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                            ₹{{ number_format($totalProfit, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection