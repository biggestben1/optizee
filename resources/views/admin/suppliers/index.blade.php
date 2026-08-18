@extends('layouts.admin')

@section('title', 'Suppliers')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Suppliers</li>
@endsection

@section('actions')
<a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i> Add Supplier
</a>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card bg-danger-gradient">
            <div class="card-body">
                <div class="d-flex">
                    <div class="text-white">
                        <h2 class="mb-0">₦{{ number_format($totalDebt, 2) }}</h2>
                        <p class="mb-0">Total Owed to Suppliers</p>
                    </div>
                    <div class="ms-auto">
                        <i class="fe fe-truck text-white" style="font-size: 40px; opacity: 0.5;"></i>
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
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Search suppliers..." value="{{ request('search') }}" style="max-width: 250px;">
                    <label class="d-flex align-items-center">
                        <input type="checkbox" name="owing" value="1" {{ request('owing') ? 'checked' : '' }} class="me-2">
                        With Outstanding Balance
                    </label>
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">Reset</a>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Products Supplied</th>
                                <th>Balance Owed</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $supplier)
                            <tr>
                                <td>
                                    <strong>{{ $supplier->name }}</strong>
                                    @if($supplier->email)
                                    <br><small class="text-muted">{{ $supplier->email }}</small>
                                    @endif
                                </td>
                                <td>{{ $supplier->phone }}</td>
                                <td>{{ Str::limit($supplier->products_supplied, 30) ?? '-' }}</td>
                                <td>
                                    @if($supplier->balance_owed > 0)
                                    <span class="text-danger fw-bold">₦{{ number_format($supplier->balance_owed, 2) }}</span>
                                    @else
                                    <span class="text-success">₦0.00</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->is_active)
                                    <span class="badge bg-success">Active</span>
                                    @else
                                    <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.suppliers.show', $supplier) }}" class="btn btn-sm btn-info" title="View Details">
                                            <i class="fe fe-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.suppliers.orders', $supplier) }}" class="btn btn-sm btn-warning" title="View Orders">
                                            <i class="fe fe-list"></i>
                                        </a>
                                        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        @if($supplier->balance_owed > 0)
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $supplier->id }}" title="Make Payment">
                                            <i class="fe fe-dollar-sign"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Payment Modal -->
                            @if($supplier->balance_owed > 0)
                            <div class="modal fade" id="paymentModal{{ $supplier->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.suppliers.make-payment', $supplier) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Make Payment - {{ $supplier->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Outstanding Balance: <strong class="text-danger">₦{{ number_format($supplier->balance_owed, 2) }}</strong></p>
                                                <div class="mb-3">
                                                    <label class="form-label">Amount</label>
                                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" max="{{ $supplier->balance_owed }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Payment Method</label>
                                                    <select name="payment_method" class="form-select" required>
                                                        <option value="cash">Cash</option>
                                                        <option value="transfer">Bank Transfer</option>
                                                        <option value="cheque">Cheque</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Notes</label>
                                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">Make Payment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No suppliers found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $suppliers->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection











