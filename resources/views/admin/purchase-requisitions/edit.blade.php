@extends('layouts.admin')

@section('title', 'Edit PR - ' . $purchaseRequisition->pr_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.purchase-requisitions.index') }}">Purchase Requisitions</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.purchase-requisitions.show', $purchaseRequisition) }}">{{ $purchaseRequisition->pr_number }}</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Edit Purchase Requisition</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.purchase-requisitions.update', $purchaseRequisition) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">PR Number</label>
                        <input type="text" class="form-control" value="{{ $purchaseRequisition->pr_number }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">PR Date <span class="text-danger">*</span></label>
                        <input type="date" name="pr_date" class="form-control @error('pr_date') is-invalid @enderror" 
                               value="{{ old('pr_date', $purchaseRequisition->pr_date->format('Y-m-d')) }}" required>
                        @error('pr_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="3" required>{{ old('description', $purchaseRequisition->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Items Description</label>
                        <textarea name="items_description" class="form-control @error('items_description') is-invalid @enderror" 
                                  rows="4">{{ old('items_description', $purchaseRequisition->items_description) }}</textarea>
                        @error('items_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount (₦) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" 
                               step="0.01" min="0.01" value="{{ old('amount', $purchaseRequisition->amount) }}" required>
                        @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                  rows="3">{{ old('notes', $purchaseRequisition->notes) }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-2"></i> Update PR
                        </button>
                        <a href="{{ route('admin.purchase-requisitions.show', $purchaseRequisition) }}" class="btn btn-secondary">
                            <i class="fe fe-x me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
















