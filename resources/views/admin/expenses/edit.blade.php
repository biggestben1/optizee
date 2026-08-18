@extends('layouts.admin')

@section('title', 'Edit Expense')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.expenses.index') }}">Expenses</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit Expense</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Edit Expense - {{ $expense->expense_number }}</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.expenses.update', $expense) }}">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" 
                                   value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                            @error('expense_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $key => $label)
                                <option value="{{ $key }}" {{ old('category', $expense->category) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="2" required>{{ old('description', $expense->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Items Description</label>
                        <textarea name="items_description" id="items_description" class="form-control @error('items_description') is-invalid @enderror" 
                                  rows="3" readonly>{{ old('items_description', $expense->items_description) }}</textarea>
                        <small class="text-muted">Auto-filled from cart items</small>
                        @error('items_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Amount (₦) <span class="text-danger">*</span></label>
                            <input type="text" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                                   placeholder="2,000" value="{{ old('amount', number_format($expense->amount, 0, '.', ',')) }}" 
                                   oninput="formatCurrency(this)" required>
                            @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="cash" {{ old('payment_method', $expense->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="transfer" {{ old('payment_method', $expense->payment_method) == 'transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="pos" {{ old('payment_method', $expense->payment_method) == 'pos' ? 'selected' : '' }}>POS</option>
                            </select>
                            @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Vendor/Supplier</label>
                            <input type="text" name="vendor" class="form-control @error('vendor') is-invalid @enderror" 
                                   value="{{ old('vendor', $expense->vendor) }}" placeholder="Who was paid">
                            @error('vendor')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Receipt Number</label>
                            <input type="text" name="receipt_number" class="form-control @error('receipt_number') is-invalid @enderror" 
                                   value="{{ old('receipt_number', $expense->receipt_number) }}" placeholder="Optional">
                            @error('receipt_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                  rows="3">{{ old('notes', $expense->notes) }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_direct_cost" id="isDirectCost" {{ old('is_direct_cost', $expense->is_direct_cost) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-primary" for="isDirectCost">
                                Direct Cost (COGS)
                            </label>
                            <div class="form-text">Check this if the expense is directly related to Bar or Kitchen stock (Cost of Goods Sold).</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-2"></i> Update Expense
                        </button>
                        <a href="{{ route('admin.expenses.show', $expense) }}" class="btn btn-secondary">
                            <i class="fe fe-x me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function formatCurrency(input) {
    let value = input.value.replace(/,/g, '');
    if (isNaN(value)) {
        value = '';
    }
    if (value !== '') {
        value = parseFloat(value).toLocaleString('en-US');
    }
    input.value = value;
}

// Remove commas before form submission
document.querySelector('form').addEventListener('submit', function(e) {
    const amountInput = document.querySelector('input[name="amount"]');
    if (amountInput) {
        amountInput.value = amountInput.value.replace(/,/g, '');
    }
});
</script>
@endpush

