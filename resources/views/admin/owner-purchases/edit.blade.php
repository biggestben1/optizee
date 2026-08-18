@extends('layouts.admin')

@section('title', 'Edit Owner Purchase - ' . $ownerPurchase->purchase_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.owner-purchases.index') }}">Owner Purchases</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.owner-purchases.show', $ownerPurchase) }}">{{ $ownerPurchase->purchase_number }}</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Edit Owner Purchase</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.owner-purchases.update', $ownerPurchase) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Purchase Number</label>
                        <input type="text" class="form-control" value="{{ $ownerPurchase->purchase_number }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" 
                               value="{{ old('purchase_date', $ownerPurchase->purchase_date->format('Y-m-d')) }}" required>
                        @error('purchase_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="3" required>{{ old('description', $ownerPurchase->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Items Description</label>
                        <textarea name="items_description" class="form-control @error('items_description') is-invalid @enderror" 
                                  rows="4">{{ old('items_description', $ownerPurchase->items_description) }}</textarea>
                        @error('items_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount (₦) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                                   step="0.01" min="0.01" value="{{ old('amount', $ownerPurchase->amount) }}" required>
                            @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vendor/Store</label>
                            <input type="text" name="vendor" class="form-control @error('vendor') is-invalid @enderror" 
                                   value="{{ old('vendor', $ownerPurchase->vendor) }}">
                            @error('vendor')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            <option value="cash" {{ old('payment_method', $ownerPurchase->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="transfer" {{ old('payment_method', $ownerPurchase->payment_method) == 'transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="pos" {{ old('payment_method', $ownerPurchase->payment_method) == 'pos' ? 'selected' : '' }}>POS</option>
                            <option value="credit" {{ old('payment_method', $ownerPurchase->payment_method) == 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                        @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                  rows="3">{{ old('notes', $ownerPurchase->notes) }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-2"></i> Update Purchase
                        </button>
                        <a href="{{ route('admin.owner-purchases.show', $ownerPurchase) }}" class="btn btn-secondary">
                            <i class="fe fe-x me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
















