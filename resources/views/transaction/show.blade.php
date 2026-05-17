@extends('layouts.app')
@section('title', 'Transaction Details')
@section('header_title', $header_title ?? 'Transaction Details')
@section('tagline', $tagline ?? 'Detailed overview of the transaction.')


@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-transparent border-bottom">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="d-flex align-items-center">
                                    <div class="bg-soft-primary rounded p-2 me-2">
                                        <i class="iconoir-reports text-primary fs-18"></i>
                                    </div>
                                    <div>
                                        <h4 class="card-title mb-0">Transaction #{{ $transaction->id }}</h4>
                                        <p class="mb-0 text-muted small">
                                            {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M, Y h:i A') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                @php
                                    $typeClass = 'bg-soft-info text-info';
                                    switch ($transaction->transaction_type) {
                                        case 'buy':
                                            $typeClass = 'bg-soft-success text-success';
                                            break;
                                        case 'sell':
                                            $typeClass = 'bg-soft-info text-info';
                                            break;
                                        case 'return':
                                            $typeClass = 'bg-soft-danger text-danger';
                                            break;
                                        case 'exchange':
                                            $typeClass = 'bg-soft-warning text-warning';
                                            break;
                                        case 'repair':
                                            $typeClass = 'bg-soft-primary text-primary';
                                            break;
                                    }
                                @endphp
                                <span
                                    class="badge {{ $typeClass }} rounded-pill px-3 py-2 fs-12 fw-bold text-uppercase">{{ $transaction->transaction_type }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-uppercase font-11 text-muted">Item Details</h6>
                                @if ($transaction->mobile)
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0">
                                            <i class="iconoir-smartphone-device fs-36 text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="mb-1">{{ $transaction->mobile->brand->name ?? '' }}
                                                {{ $transaction->mobile->model->name ?? '' }}</h5>
                                            <p class="text-muted mb-0">HSN Number:
                                                {{ $transaction->mobile->hsn_number ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <tr>
                                                <th class="ps-0 border-0">Color:</th>
                                                <td class="border-0">{{ $transaction->mobile->color ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="ps-0 border-0">Storage/RAM:</th>
                                                <td class="border-0">{{ $transaction->mobile->storage ?? '' }} /
                                                    {{ $transaction->mobile->ram ?? '' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                @elseif($transaction->accessory)
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0">
                                            <i class="iconoir-headset fs-36 text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="mb-1">{{ $transaction->accessory->brand->name ?? '' }}
                                                {{ $transaction->accessory->name ?? '' }}</h5>
                                            <p class="text-muted mb-0">{{ $transaction->accessory->model ?? 'Accessory' }}
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <p>No item details available.</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase font-11 text-muted">Financial Info</h6>
                                <div class="bg-soft-primary p-3 rounded-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted fw-medium">Price:</span>
                                        <h4 class="mb-0 fw-bold text-primary">₹{{ number_format($transaction->price, 2) }}
                                        </h4>
                                    </div>
                                    @if (($transaction->invoiceItem->discount ?? 0) > 0)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted fw-medium">Discount:</span>
                                            <h5 class="mb-0 fw-bold text-danger">
                                                ₹{{ number_format($transaction->invoiceItem->discount, 2) }}</h5>
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted fw-medium">Total:</span>
                                        <h4 class="mb-0 fw-bold text-primary">
                                            ₹{{ number_format($transaction->invoiceItem->total, 2) }}</h4>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted fw-medium">Payment:</span>
                                        <span
                                            class="badge bg-white text-dark border shadow-sm px-2">{{ $transaction->payment_method == 'bajaj_finance' ? 'Bajaj Finance' : ucfirst($transaction->payment_method) }}</span>
                                    </div>
                                    @if ($transaction->bajaj_approval_number)
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <span class="text-muted fw-medium small">Appr No:</span>
                                            <span
                                                class="text-dark small fw-semibold">{{ $transaction->bajaj_approval_number }}</span>
                                        </div>
                                    @endif
                                </div>
                                <h6 class="text-uppercase font-11 text-muted">Related Records</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="iconoir-page-star me-2 text-muted"></i>
                                        <strong>Invoice:</strong>
                                        @if ($transaction->invoice)
                                            <a href="{{ route('invoice.show', $transaction->invoice->id) }}"
                                                class="text-primary">{{ $transaction->invoice_no }}</a>
                                        @else
                                            {{ $transaction->invoice_no ?: 'N/A' }}
                                        @endif
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <hr class="hr-dashed">

                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-uppercase font-11 text-muted">Notes</h6>
                                <p class="mb-0">{{ $transaction->notes ?: 'No notes available for this transaction.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Customer / Supplier</h4>
                    </div>
                    <div class="card-body">
                        @if ($transaction->customer)
                            <div class="text-center mb-4">
                                <div class="avatar-lg mx-auto mb-3">
                                    <span class="avatar-title bg-soft-primary text-primary rounded-circle fs-24">
                                        {{ strtoupper(substr($transaction->customer->name, 0, 1)) }}
                                    </span>
                                </div>
                                <h5>{{ $transaction->customer->name }}</h5>
                                <p class="text-muted">{{ $transaction->customer->phone }}</p>
                            </div>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <tr>
                                        <th class="ps-0 border-0">Email:</th>
                                        <td class="border-0">{{ $transaction->customer->email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="ps-0 border-0">Address:</th>
                                        <td class="border-0">{{ $transaction->customer->address ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('customers.show', $transaction->customer_id) }}"
                                    class="btn btn-outline-primary w-100">View Profile</a>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="iconoir-user-x fs-48 text-muted"></i>
                                <p class="mt-2 text-muted">No customer linked.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if ($transaction->invoice)
                                <a href="{{ route('invoice.show', $transaction->invoice->id) }}" class="btn btn-primary">
                                    <i class="iconoir-page me-2"></i> View Original Invoice
                                </a>
                            @elseif($transaction->invoice_no)
                                <a href="{{ route('invoice.index', ['search' => $transaction->invoice_no]) }}"
                                    class="btn btn-primary">
                                    <i class="iconoir-page me-2"></i> Search Original Invoice
                                </a>
                            @endif
                            {{--  <button class="btn btn-outline-danger" onclick="deleteTransaction({{ $transaction->id }})">
                                <i class="iconoir-trash me-2"></i> Delete Transaction
                            </button> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageScripts')
    <script>
        function deleteTransaction(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will only delete the transaction record!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('transactions.destroy', ':id') }}'.replace(':id', id),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(res) {
                            if (res.success) {
                                toastr.success(res.message);
                                window.location.href = "{{ route('transactions.index') }}";
                            } else {
                                toastr.error(res.message);
                            }
                        }
                    });
                }
            });
        }
    </script>
@endsection
