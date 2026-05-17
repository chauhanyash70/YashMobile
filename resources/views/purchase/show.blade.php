@extends('layouts.app')

@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Purchase Details (#PUR-{{ $purchase->id }})</h4>
                        <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Supplier Information</h5>
                                <p>
                                    <strong>Name:</strong> {{ $purchase->supplier->name }}<br>
                                    <strong>Phone:</strong> {{ $purchase->supplier->phone }}<br>
                                    <strong>City:</strong> {{ $purchase->supplier->city }}<br>
                                    <strong>Address:</strong> {{ $purchase->supplier->address }}
                                </p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h5>Purchase Summary</h5>
                                <p>
                                    <strong>Date:</strong> {{ $purchase->purchase_date }}<br>
                                    <strong>Total Amount:</strong> ₹{{ number_format($purchase->total_amount, 2) }}<br>
                                    <strong>Paid Amount:</strong> ₹{{ number_format($purchase->paid_amount, 2) }}<br>
                                    <strong>Due Amount:</strong> ₹{{ number_format($purchase->due_amount, 2) }}<br>
                                    <strong>Payment Method:</strong> {{ ucfirst($purchase->payment_method) }}
                                </p>
                            </div>
                        </div>

                        <h5>Items Purchased</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Item Type</th>
                                        <th>Item Details</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchase->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ ucfirst($item->item_type) }}</td>
                                            <td>
                                                @if ($item->item_type == 'device')
                                                    {{ $item->item->brand->name }} {{ $item->item->model->name }}
                                                    ({{ $item->item->storage }}, {{ $item->item->color }})
                                                @elseif($item->item_type == 'accessory')
                                                    {{ $item->item->brand->name }} {{ $item->item->name }}
                                                    @if ($item->item->model)
                                                        [{{ $item->item->model }}]
                                                    @endif
                                                    @if ($item->item->color)
                                                        [{{ $item->item->color }}]
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                            <td class="text-end">₹{{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-end">Grand Total</th>
                                        <th class="text-end">₹{{ number_format($purchase->total_amount, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if ($purchase->notes)
                            <div class="mt-4">
                                <h5>Notes</h5>
                                <div class="p-3 bg-light border rounded">
                                    {{ $purchase->notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
