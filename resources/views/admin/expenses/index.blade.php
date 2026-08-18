@extends('layouts.admin')

@section('title', 'Expenses')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Expenses</li>
@endsection

@section('actions')
<a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i> Record Expense
</a>
@endsection

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">
                        <i class="fe fe-x me-1"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary-transparent">
            <div class="card-body text-center">
                <h3 class="text-primary mb-1">₦{{ number_format($expenses->where('is_direct_cost', true)->sum('amount'), 2) }}</h3>
                <p class="text-muted mb-0">Total Direct Costs (COGS)</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info-transparent">
            <div class="card-body text-center">
                <h3 class="text-info mb-1">₦{{ number_format($expenses->where('is_direct_cost', false)->sum('amount'), 2) }}</h3>
                <p class="text-muted mb-0">Total General Expenses</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger-transparent">
            <div class="card-body text-center">
                <h3 class="text-danger mb-1">₦{{ number_format($totalAmount, 2) }}</h3>
                <p class="text-muted mb-0">Grand Total</p>
            </div>
        </div>
    </div>
</div>

<!-- Expenses Table -->
<div class="card">
    <div class="card-header">
        <h4 class="card-title mb-0">All Expenses</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th>Expense #</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Vendor</th>
                        <th>Payment</th>
                        <th class="text-end">Amount</th>
                        <th>Recorded By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr>
                        <td><strong>{{ $expense->expense_number }}</strong></td>
                        <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                        <td>
                            <span class="badge bg-info">{{ $categories[$expense->category] ?? $expense->category }}</span>
                            @if($expense->is_direct_cost)
                                <span class="badge bg-primary">Direct</span>
                            @endif
                        </td>
                        <td>{{ Str::limit($expense->description, 50) }}</td>
                        <td>{{ $expense->vendor ?? '-' }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($expense->payment_method) }}</span>
                        </td>
                        <td class="text-end">₦{{ number_format($expense->amount, 2) }}</td>
                        <td>{{ $expense->user->name }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.expenses.show', $expense) }}" class="btn btn-sm btn-primary">
                                    <i class="fe fe-eye"></i>
                                </a>
                                <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-sm btn-warning">
                                    <i class="fe fe-edit"></i>
                                </a>
                                <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fe fe-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No expenses recorded</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.datatable').DataTable({
        order: [[1, 'desc']],
        pageLength: 25
    });
});
</script>
@endpush
















