@extends('layouts.admin')

@section('title', $customer->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ $customer->name }}</li>
@endsection

@section('actions')
<a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary">
    <i class="fe fe-edit me-2"></i> Edit
</a>
<a href="{{ route('admin.customers.statement', $customer) }}" class="btn btn-secondary">
    <i class="fe fe-file-text me-2"></i> Statement
</a>
@if($customer->credit_balance > 0)
<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
    <i class="fe fe-dollar-sign me-2"></i> Receive Payment
</button>
@endif
<form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ addslashes($customer->name) }}? This cannot be undone.')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger">
        <i class="fe fe-trash-2 me-2"></i> Delete
    </button>
</form>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Customer Details</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <td class="fw-bold">Name</td>
                        <td>{{ $customer->name }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Phone</td>
                        <td>{{ $customer->phone }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Email</td>
                        <td>{{ $customer->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Address</td>
                        <td>{{ $customer->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Credit Limit</td>
                        <td>₦{{ number_format($customer->credit_limit, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Balance Owed</td>
                        <td class="text-danger fw-bold">₦{{ number_format($customer->credit_balance, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Available Credit</td>
                        <td class="text-success">₦{{ number_format($customer->getAvailableCredit(), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Credit Status</td>
                        <td>
                            @if($customer->credit_enabled)
                            <span class="badge bg-success">Enabled</span>
                            @else
                            <span class="badge bg-secondary">Disabled</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Transactions</h3>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#sales">Sales</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#payments">Payments</a>
                    </li>
                </ul>
                <div class="tab-content mt-3">
                    <div class="tab-pane active" id="sales">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customer->sales as $sale)
                                    <tr>
                                        <td><a href="{{ route('admin.pos.show', $sale) }}">{{ $sale->invoice_number }}</a></td>
                                        <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                        <td>₦{{ number_format($sale->total, 2) }}</td>
                                        <td>
                                            @if($sale->is_credit_sale)
                                            <span class="badge bg-warning">Credit</span>
                                            @else
                                            <span class="badge bg-success">{{ ucfirst($sale->payment_method) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sale->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                            @else
                                            <span class="badge bg-danger">{{ ucfirst($sale->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No sales recorded.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane" id="payments">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Receipt</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Received By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customer->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->receipt_number }}</td>
                                        <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                        <td class="text-success">₦{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ ucfirst($payment->payment_method) }}</td>
                                        <td>{{ $payment->user->name }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No payments recorded.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
@if($customer->credit_balance > 0)
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.customers.receive-payment', $customer) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Receive Payment</h5>
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
@endsection











