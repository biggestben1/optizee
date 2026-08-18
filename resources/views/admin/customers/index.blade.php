@extends('layouts.admin')

@section('title', 'Customers')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Customers</li>
@endsection

@section('actions')
<a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i> Add Customer
</a>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card bg-warning-gradient">
            <div class="card-body">
                <div class="d-flex">
                    <div class="text-white">
                        <h2 class="mb-0">₦{{ number_format($totalDebt, 2) }}</h2>
                        <p class="mb-0">Total Customer Debt</p>
                    </div>
                    <div class="ms-auto">
                        <i class="fe fe-alert-triangle text-white" style="font-size: 40px; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form action="" method="GET" class="d-flex gap-2 w-100">
                    <input type="text" name="search" class="form-control" placeholder="Search customers..." value="{{ request('search') }}" style="max-width: 250px;">
                    <select name="status" class="form-select" style="max-width: 200px;">
                        <option value="">All Customers</option>
                        <option value="credit" {{ request('status') == 'credit' ? 'selected' : '' }}>Credit Enabled</option>
                        <option value="owing" {{ request('status') == 'owing' ? 'selected' : '' }}>With Outstanding Balance</option>
                    </select>
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Reset</a>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Credit Limit</th>
                                <th>Balance Owed</th>
                                <th>Available</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <strong>{{ $customer->name }}</strong>
                                    @if($customer->email)
                                    <br><small class="text-muted">{{ $customer->email }}</small>
                                    @endif
                                </td>
                                <td>{{ $customer->phone }}</td>
                                <td>₦{{ number_format($customer->credit_limit, 2) }}</td>
                                <td>
                                    @if($customer->credit_balance > 0)
                                    <span class="text-danger fw-bold">₦{{ number_format($customer->credit_balance, 2) }}</span>
                                    @else
                                    <span class="text-success">₦0.00</span>
                                    @endif
                                </td>
                                <td>₦{{ number_format($customer->getAvailableCredit(), 2) }}</td>
                                <td>
                                    @if($customer->credit_enabled)
                                    <span class="badge bg-success">Credit Enabled</span>
                                    @else
                                    <span class="badge bg-secondary">Cash Only</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-info">
                                        <i class="fe fe-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.customers.statement', $customer) }}" class="btn btn-sm btn-secondary">
                                        <i class="fe fe-file-text"></i>
                                    </a>
                                    <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-primary">
                                        <i class="fe fe-edit"></i>
                                    </a>
                                    @if($customer->credit_balance > 0)
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $customer->id }}">
                                        <i class="fe fe-dollar-sign"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Payment Modal -->
                            @if($customer->credit_balance > 0)
                            <div class="modal fade" id="paymentModal{{ $customer->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.customers.receive-payment', $customer) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Receive Payment - {{ $customer->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Outstanding Balance: <strong class="text-danger">₦{{ number_format($customer->credit_balance, 2) }}</strong></p>
                                                <div class="mb-3">
                                                    <label class="form-label">Amount</label>
                                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" max="{{ $customer->credit_balance }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Payment Method</label>
                                                    <select name="payment_method" class="form-select" required>
                                                        <option value="cash">Cash</option>
                                                        <option value="transfer">Bank Transfer</option>
                                                        <option value="pos">POS</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Notes</label>
                                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">Receive Payment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No customers found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $customers->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection











