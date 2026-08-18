@extends('layouts.admin')

@section('title', 'Supplier Details - ' . $supplier->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.suppliers.index') }}">Suppliers</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ $supplier->name }}</li>
@endsection

@section('actions')
<a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
    <i class="fe fe-arrow-left me-2"></i> Back to Suppliers
</a>
<a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-primary">
    <i class="fe fe-edit me-2"></i> Edit Supplier
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title mb-3">Supplier Information</h3>
                <div class="mb-3">
                    <strong>Name</strong>
                    <p class="mb-0">{{ $supplier->name }}</p>
                </div>
                <div class="mb-3">
                    <strong>Phone</strong>
                    <p class="mb-0">{{ $supplier->phone }}</p>
                </div>
                <div class="mb-3">
                    <strong>Email</strong>
                    <p class="mb-0">{{ $supplier->email ?? '-' }}</p>
                </div>
                <div class="mb-3">
                    <strong>Address</strong>
                    <p class="mb-0">{{ $supplier->address ?? '-' }}</p>
                </div>
                <div class="mb-3">
                    <strong>Products Supplied</strong>
                    <p class="mb-0">{{ $supplier->products_supplied ?? '-' }}</p>
                </div>
                <div class="mb-3">
                    <strong>Balance Owed</strong>
                    <p class="mb-0 text-{{ $supplier->balance_owed > 0 ? 'danger' : 'success' }} fw-bold">
                        ₦{{ number_format($supplier->balance_owed, 2) }}
                    </p>
                </div>
                <div class="mb-3">
                    <strong>Status</strong>
                    <p class="mb-0">
                        @if($supplier->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </p>
                </div>
                <div class="mb-3">
                    <strong>Created By</strong>
                    <p class="mb-0">{{ $supplier->createdBy?->name ?? 'System' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Recent Supplies</h4>
                        @if($supplier->supplies->isEmpty())
                            <p class="text-muted">No recent supplies found.</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($supplier->supplies->take(5) as $supply)
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>₦{{ number_format($supply->total_amount, 2) }}</strong>
                                                <div class="small text-muted">{{ $supply->supply_date?->format('M d, Y') ?? '-' }}</div>
                                            </div>
                                            <span class="badge bg-primary">{{ ucfirst($supply->payment_type) }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Recent Payments</h4>
                        @if($supplier->payments->isEmpty())
                            <p class="text-muted">No recent payments found.</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($supplier->payments->take(5) as $payment)
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>₦{{ number_format($payment->amount, 2) }}</strong>
                                                <div class="small text-muted">{{ $payment->payment_date?->format('M d, Y') ?? '-' }}</div>
                                            </div>
                                            <span class="badge bg-success">{{ ucfirst($payment->payment_method) }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Recent Purchase Orders</h3>
                        <div class="card-options">
                            <a href="{{ route('admin.suppliers.orders', $supplier) }}" class="btn btn-warning btn-sm">
                                <i class="fe fe-list me-1"></i> View All Orders
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($supplier->purchaseInvoices->isEmpty())
                            <p class="text-muted">No purchase orders found for this supplier.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom mb-0">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($supplier->purchaseInvoices as $invoice)
                                            <tr>
                                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                                <td>{{ $invoice->invoice_date?->format('M d, Y') ?? '-' }}</td>
                                                <td>₦{{ number_format($invoice->total_amount, 2) }}</td>
                                                <td>
                                                    <a href="{{ route('admin.purchase-invoices.checklist', $invoice) }}" class="btn btn-sm btn-primary">
                                                        <i class="fe fe-check-square me-1"></i> Checklist
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Supplier Activity</h3>
                <div class="card-options">
                    <a href="{{ route('admin.suppliers.orders', $supplier) }}" class="btn btn-warning btn-sm">
                        <i class="fe fe-list me-1"></i> View Purchase Orders
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <div class="border p-3 rounded">
                            <h5>Total Supplies</h5>
                            <p class="mb-0">{{ $supplier->supplies->count() }}</p>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <div class="border p-3 rounded">
                            <h5>Total Payments</h5>
                            <p class="mb-0">{{ $supplier->payments->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
