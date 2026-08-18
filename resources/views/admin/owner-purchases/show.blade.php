@extends('layouts.admin')

@section('title', 'Owner Purchase - ' . $ownerPurchase->purchase_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.owner-purchases.index') }}">Owner Purchases</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ $ownerPurchase->purchase_number }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Owner Purchase Details</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Purchase Number</label>
                        <p class="mb-0 fw-bold">{{ $ownerPurchase->purchase_number }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Date</label>
                        <p class="mb-0">{{ $ownerPurchase->purchase_date->format('F d, Y') }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Recorded By</label>
                        <p class="mb-0">{{ $ownerPurchase->user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Payment Method</label>
                        <p class="mb-0">
                            <span class="badge bg-info">{{ ucfirst($ownerPurchase->payment_method) }}</span>
                        </p>
                    </div>
                </div>

                @if($ownerPurchase->vendor)
                <div class="mb-3">
                    <label class="text-muted small">Vendor/Store</label>
                    <p class="mb-0">{{ $ownerPurchase->vendor }}</p>
                </div>
                @endif

                <div class="mb-3">
                    <label class="text-muted small">Description</label>
                    <p class="mb-0">{{ $ownerPurchase->description }}</p>
                </div>

                @if($ownerPurchase->items_description)
                <div class="mb-3">
                    <label class="text-muted small">Items Description</label>
                    <p class="mb-0">{{ nl2br(e($ownerPurchase->items_description)) }}</p>
                </div>
                @endif

                <div class="mb-3">
                    <label class="text-muted small">Amount</label>
                    <p class="mb-0 fw-bold h4 text-primary">₦{{ number_format($ownerPurchase->amount, 2) }}</p>
                </div>

                @if($ownerPurchase->notes)
                <div class="mb-3">
                    <label class="text-muted small">Notes</label>
                    <p class="mb-0">{{ nl2br(e($ownerPurchase->notes)) }}</p>
                </div>
                @endif
            </div>
            <div class="card-footer">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.owner-purchases.edit', $ownerPurchase) }}" class="btn btn-primary">
                        <i class="fe fe-edit me-2"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('admin.owner-purchases.destroy', $ownerPurchase) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this purchase?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fe fe-trash me-2"></i> Delete
                        </button>
                    </form>
                    <a href="{{ route('admin.owner-purchases.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
















