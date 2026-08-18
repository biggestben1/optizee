@extends('layouts.admin')

@section('title', 'Expense - ' . $expense->expense_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.expenses.index') }}">Expenses</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ $expense->expense_number }}</li>
@endsection

@section('actions')
<a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-primary">
    <i class="fe fe-edit me-2"></i> Edit Expense
</a>
<a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">
    <i class="fe fe-arrow-left me-2"></i> Back
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Expense Details</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Expense Number</label>
                        <p class="mb-0 fw-bold">{{ $expense->expense_number }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Date</label>
                        <p class="mb-0">{{ $expense->expense_date->format('F d, Y') }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Category</label>
                        <p class="mb-0">
                            <span class="badge bg-info">{{ \App\Models\Expense::getCategories()[$expense->category] ?? $expense->category }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Amount</label>
                        <p class="mb-0 fw-bold h4 text-danger">₦{{ number_format($expense->amount, 2) }}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Description</label>
                    <p class="mb-0">{{ $expense->description }}</p>
                </div>

                @if($expense->items_description)
                <div class="mb-3">
                    <label class="text-muted small">Items</label>
                    <div class="mb-0">{!! str_replace(['&lt;br /&gt;', '&lt;br&gt;', '&lt;br/&gt;'], '<br />', nl2br(e($expense->items_description))) !!}</div>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Payment Method</label>
                        <p class="mb-0">
                            <span class="badge bg-secondary">{{ ucfirst($expense->payment_method) }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Vendor/Supplier</label>
                        <p class="mb-0">{{ $expense->vendor ?? 'N/A' }}</p>
                    </div>
                </div>

                @if($expense->receipt_number)
                <div class="mb-3">
                    <label class="text-muted small">Receipt Number</label>
                    <p class="mb-0"><code>{{ $expense->receipt_number }}</code></p>
                </div>
                @endif

                @if($expense->notes)
                <div class="mb-3">
                    <label class="text-muted small">Notes</label>
                    <p class="mb-0">{{ nl2br(e($expense->notes)) }}</p>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Recorded By</label>
                        <p class="mb-0">{{ $expense->user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Recorded At</label>
                        <p class="mb-0">{{ $expense->created_at->format('F d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fe fe-trash me-2"></i> Delete Expense
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

