@extends('layouts.app')
@section('title', 'Record Expense')
@section('header_title', $header_title ?? 'Record Expense')
@section('tagline', $tagline ?? 'Track operational costs or device-specific expenditures.')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Record New Expense</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('expenses.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Expense Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Courier Charges, Case, Screen Protector" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Link to Mobile (Optional)</label>
                            <select name="mobile_id" class="form-select">
                                <option value="">General Shop Expense</option>
                                @foreach($mobiles as $mobile)
                                    <option value="{{ $mobile->id }}" {{ $selected_mobile == $mobile->id ? 'selected' : '' }}>
                                        {{ $mobile->model->name }} ({{ $mobile->hsn_number }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Select a device if this cost is specific to a unit.</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" name="amount" class="form-control" placeholder="0.00" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Expense Date</label>
                                <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Record Expense</button>
                            <a href="{{ route('expenses.index') }}" class="btn btn-link">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
