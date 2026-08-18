@extends('layouts.admin')

@section('title', 'Owner Purchases')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Owner Purchases</li>
@endsection

@section('actions')
<a href="{{ route('admin.owner-purchases.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i> Record Purchase
</a>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h4 class="mb-0">Total Owner Purchases</h4>
                <h2 class="text-primary mb-0">₦{{ number_format($totalAmount, 2) }}</h2>
                <small class="text-muted">All time purchases (not included in profit/loss)</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Owner Purchases</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fe fe-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.owner-purchases.index') }}" class="btn btn-secondary">
                    <i class="fe fe-x me-1"></i> Clear
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Purchase #</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Vendor</th>
                        <th>Payment</th>
                        <th class="text-end">Amount</th>
                        <th>Recorded By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                    <tr>
                        <td><strong>{{ $purchase->purchase_number }}</strong></td>
                        <td>{{ $purchase->purchase_date->format('M d, Y') }}</td>
                        <td>{{ Str::limit($purchase->description, 50) }}</td>
                        <td>{{ $purchase->vendor ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($purchase->payment_method) }}</span>
                        </td>
                        <td class="text-end">₦{{ number_format($purchase->amount, 2) }}</td>
                        <td>{{ $purchase->user->name }}</td>
                        <td>
                            <a href="{{ route('admin.owner-purchases.show', $purchase) }}" class="btn btn-sm btn-info">
                                <i class="fe fe-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No owner purchases recorded</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $purchases->links() }}
        </div>
    </div>
</div>
@endsection
















