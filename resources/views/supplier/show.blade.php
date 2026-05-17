@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{-- Supplier Info --}}
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">Supplier Details</div>
                    <div class="card-body">
                        <h5>{{ $supplier->name }}</h5>
                        <p><strong>Phone:</strong> {{ $supplier->phone }}</p>
                        <p><strong>Email:</strong> {{ $supplier->email }}</p>
                        <p><strong>Address:</strong> {{ $supplier->address }}</p>

                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary btn-sm">Back</a>
                    </div>
                </div>
            </div>

            {{-- Purchases --}}
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">Purchases</div>
                    <div class="card-body">

                        @if($supplier->purchases->isEmpty())
                            <p>No purchases found.</p>
                        @else
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Paid</th>
                                        {{-- <th>Due</th> --}}
                                        {{-- <th>Action</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplier->purchases as $purchase)
                                        <tr>
                                            <td>{{ $purchase->purchase_date }}</td>
                                            <td>{{ $purchase->itemData->item_type }}</td>
                                            <td>
                                                @if($purchase->itemData && $purchase->itemData->item)
                                                    {{$purchase->itemData->item_type == 'device' ? ($purchase->itemData->item->model->name ?? 'Unknown Device') : ($purchase->itemData->item->name ?? 'Unknown Accessory') }}
                                                    @if($purchase->itemData->item_type == 'device')
                                                        <br>
                                                        <small>IMEI: {{ $purchase->itemData->deviceImei->imei ?? 'N/A' }}</small>
                                                    @elseif($purchase->itemData->item_type == 'accessories')
                                                        <br>
                                                        <small>Serial:
                                                            {{ $purchase->itemData->item->serial ?? $purchase->itemData->item->sku ?? 'N/A' }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-danger">Item Data Missing</span>
                                                @endif
                                            </td>
                                            <td>{{ $purchase->itemData->quantity }}</td>
                                            <td>{{ number_format($purchase->itemData->price, 2) }}</td>
                                            <td>{{ number_format($purchase->total_amount, 2) }}</td>
                                            <td>{{ number_format($purchase->paid_amount, 2) }}</td>
                                            {{-- <td>{{ number_format($purchase->due_amount, 2) }}</td> --}}
                                            {{-- <td>
                                                @if($purchase->items->isNotEmpty())
                                                <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#purchaseModal" data-purchase='@json($purchase)'
                                                    data-items='@json($purchase->items)'>
                                                    View
                                                </button>
                                                @endif
                                            </td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="purchaseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Purchase Details</h5>
                </div>

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Date:</strong> <span id="m_date"></span></p>
                            <p><strong>Total:</strong> <span id="m_total"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Paid:</strong> <span id="m_paid"></span></p>
                            <p><strong>Due:</strong> <span id="m_due"></span></p>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="m_items"></tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>
@endsection
@section('pageScripts')
    <script>
        document.getElementById('purchaseModal').addEventListener('show.bs.modal', function (event) {

            const button = event.relatedTarget;
            const purchase = JSON.parse(button.getAttribute('data-purchase'));
            const items = JSON.parse(button.getAttribute('data-items'));

            document.getElementById('m_date').innerText = purchase.purchase_date;
            document.getElementById('m_total').innerText = purchase.total_amount;
            document.getElementById('m_paid').innerText = purchase.paid_amount;
            document.getElementById('m_due').innerText = purchase.due_amount;

            const tbody = document.getElementById('m_items');
            tbody.innerHTML = '';

            items.forEach(i => {
                let name = '';
                let imei = '';

                if (i.item_type.includes('device') && i.item) {
                    name = i.item.model?.name ?? 'Unknown Device';
                    name += ` (${i.item.storage ?? ''}${i.item.ram ? ' - ' + i.item.ram : ''})`;
                    let imeiStr = i.device_imei?.imei ?? i.item.imei?.imei ?? 'N/A';
                    name = `${name}<br><small>IMEI: ${imeiStr}</small>`;
                } else if (i.item) {
                    name = `${i.item.name ?? 'Unknown Accessory'} (${i.item.model ?? ''}) <br><small>Serial No: ${i.item.sku ?? 'N/A'}</small>`;
                }

                tbody.innerHTML += `
                                                                                                                    <tr>
                                                                                                                        <td>${i.item_type.split('\\').pop().toUpperCase()}</td>
                                                                                                                        <td>${name}</td>
                                                                                                                        <td>${i.quantity}</td>
                                                                                                                        <td>${i.price}</td>
                                                                                                                        <td>${i.quantity * i.price}</td>
                                                                                                                    </tr>
                                                                                                                `;
            });
        });
    </script>
@endsection