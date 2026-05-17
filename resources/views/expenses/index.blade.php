@extends('layouts.app')
@section('title', 'Expenses')
@section('header_title', $header_title ?? 'Expense Ledger')
@section('tagline', $tagline ?? 'Track shop operational costs and device-specific expenses.')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Expense Ledger</h4>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary shadow-sm">Record New Expense</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Title</th>
                        <th>Linked Device</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                    <tr>
                        <td class="ps-4">
                            <strong>{{ $expense->title }}</strong><br>
                            <small class="text-muted">{{ Str::limit($expense->notes, 30) }}</small>
                        </td>
                        <td>
                            @if($expense->mobile)
                                <span class="text-primary">{{ $expense->mobile->model->name }}</span><br>
                                <small class="text-muted">{{ $expense->mobile->hsn_number }}</small>
                            @else
                                <span class="text-muted">General Shop Expense</span>
                            @endif
                        </td>
                        <td class="fw-bold text-danger">₹{{ number_format($expense->amount, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M, Y') }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $expenses->links() }}
        </div>
    </div>
</div>
@endsection
