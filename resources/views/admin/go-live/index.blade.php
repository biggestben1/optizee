@extends('layouts.admin')

@section('title', 'Ready to Go Live')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Ready to Go Live</li>
@endsection

@section('content')
<div class="alert alert-warning">
    <i class="fe fe-alert-triangle me-2"></i>
    <strong>Before going live:</strong> Use these tools to remove test orders and customers from the system.
    These actions are permanent and cannot be undone.
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row mb-4" id="data-counts">
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h2 class="mb-0 text-danger" data-count="sales">{{ number_format($counts['sales']) }}</h2>
                <p class="mb-0 text-muted">Sales &amp; Pending Orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h2 class="mb-0 text-warning" data-count="table_guests">{{ number_format($counts['table_guests']) }}</h2>
                <p class="mb-0 text-muted">Active Table Guests</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <h2 class="mb-0 text-info" data-count="customers">{{ number_format($counts['customers']) }}</h2>
                <p class="mb-0 text-muted">Customers</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Clear All Orders</h4>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted">
                    Deletes all completed sales, pending POS orders, sale line items, and table guests.
                    Use this to remove test transactions before launch.
                </p>
                <ul class="small text-muted mb-4">
                    <li>{{ number_format($counts['sales']) }} sale(s) and pending order(s)</li>
                    <li>{{ number_format($counts['sale_items']) }} sale item(s)</li>
                    <li>{{ number_format($counts['table_guests']) }} table guest(s)</li>
                </ul>
                <button type="button" class="btn btn-outline-danger mt-auto" onclick="clearData('orders')">
                    <i class="fe fe-trash-2 me-2"></i>Clear All Orders
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Clear All Customers</h4>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted">
                    Deletes all customers and their payment records. Orders must be cleared first.
                    Use this to remove test customer accounts before launch.
                </p>
                <ul class="small text-muted mb-4">
                    <li>{{ number_format($counts['customers']) }} customer(s)</li>
                    <li>{{ number_format($counts['customer_payments']) }} customer payment(s)</li>
                </ul>
                <button type="button" class="btn btn-outline-warning mt-auto" onclick="clearData('customers')">
                    <i class="fe fe-user-x me-2"></i>Clear All Customers
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card border-danger">
    <div class="card-header bg-danger-transparent">
        <h4 class="card-title mb-0 text-danger">Clear Everything &amp; Go Live</h4>
    </div>
    <div class="card-body">
        <p class="mb-3">
            Removes all test orders and customers in one step. Products, staff, rooms, and settings are kept.
        </p>
        <button type="button" class="btn btn-danger" onclick="clearData('all')">
            <i class="fe fe-zap me-2"></i>Clear All Orders &amp; Customers
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
const clearRoutes = {
    orders: @json(route('admin.go-live.clear-orders')),
    customers: @json(route('admin.go-live.clear-customers')),
    all: @json(route('admin.go-live.clear-all')),
};

const clearLabels = {
    orders: 'all orders (sales, pending orders, and table guests)',
    customers: 'all customers and their payment records',
    all: 'ALL orders and ALL customers',
};

function clearData(type) {
    const label = clearLabels[type];

    if (!confirm(`⚠️ WARNING: This will permanently delete ${label}.\n\nThis cannot be undone. Continue?`)) {
        return;
    }

    if (!confirm(`Please confirm again: delete ${label}?`)) {
        return;
    }

    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fe fe-loader me-2"></i>Clearing...';

    fetch(clearRoutes[type], {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) {
            throw new Error(data.message || 'Failed to clear data.');
        }

        alert('✅ ' + data.message);

        if (data.counts) {
            Object.entries(data.counts).forEach(([key, value]) => {
                const el = document.querySelector(`[data-count="${key}"]`);
                if (el) {
                    el.textContent = Number(value).toLocaleString();
                }
            });
        }

        window.location.reload();
    })
    .catch(error => {
        alert('❌ ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}
</script>
@endpush
