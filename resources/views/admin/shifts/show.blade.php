@extends('layouts.admin')

@section('title', 'Shift Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.shifts.index') }}">Shifts</a></li>
<li class="breadcrumb-item active" aria-current="page">Shift Details</li>
@endsection

@section('actions')
@if($shift->isOpen() && ($shift->user_id == auth()->id() || auth()->user()->canVoidSales()))
<button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#closeShiftModal">
    <i class="fe fe-stop-circle me-2"></i> Close Shift
</button>
@endif
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Shift Information</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <td class="fw-bold">Staff</td>
                        <td>{{ $shift->user->name }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Status</td>
                        <td>
                            @if($shift->isOpen())
                            <span class="badge bg-success">Open</span>
                            @else
                            <span class="badge bg-secondary">Closed</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Opened At</td>
                        <td>{{ $shift->opened_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Closed At</td>
                        <td>{{ $shift->closed_at ? $shift->closed_at->format('M d, Y H:i') : '-' }}</td>
                    </tr>
                    @if($shift->closedBy)
                    <tr>
                        <td class="fw-bold">Closed By</td>
                        <td>{{ $shift->closedBy->name }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="fw-bold">Opening Cash</td>
                        <td>₦{{ number_format($shift->opening_cash, 2) }}</td>
                    </tr>
                    @if(!$shift->isOpen())
                    <tr>
                        <td class="fw-bold">Expected Cash</td>
                        <td>₦{{ number_format($shift->expected_cash, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Closing Cash</td>
                        <td>₦{{ number_format($shift->closing_cash, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Difference</td>
                        <td>
                            @if($shift->cash_difference >= 0)
                            <span class="text-success">+₦{{ number_format($shift->cash_difference, 2) }}</span>
                            @else
                            <span class="text-danger">-₦{{ number_format(abs($shift->cash_difference), 2) }}</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card bg-primary-gradient">
                    <div class="card-body text-white">
                        <h4>₦{{ number_format($summary['total_sales'], 2) }}</h4>
                        <p class="mb-0">Total Sales</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card bg-success-gradient">
                    <div class="card-body text-white">
                        <h4>{{ $summary['total_transactions'] }}</h4>
                        <p class="mb-0">Transactions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>₦{{ number_format($summary['cash_sales'], 2) }}</h5>
                        <small class="text-muted">Cash</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>₦{{ number_format($summary['transfer_sales'], 2) }}</h5>
                        <small class="text-muted">Transfer</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>₦{{ number_format($summary['pos_sales'], 2) }}</h5>
                        <small class="text-muted">POS</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>₦{{ number_format($summary['credit_sales'], 2) }}</h5>
                        <small class="text-muted">Credit</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sales in this Shift</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Time</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shift->sales as $sale)
                            <tr>
                                <td><a href="{{ route('admin.pos.show', $sale) }}">{{ $sale->invoice_number }}</a></td>
                                <td>{{ $sale->created_at->format('H:i') }}</td>
                                <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
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
                                <td colspan="6" class="text-center">No sales in this shift.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Close Shift Modal -->
@if($shift->isOpen())
<div class="modal fade" id="closeShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.shifts.close', $shift) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Close Shift</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Opening Cash: <strong>₦{{ number_format($shift->opening_cash, 2) }}</strong></p>
                    <p>Cash Sales: <strong>₦{{ number_format($summary['cash_sales'], 2) }}</strong></p>
                    <p>Customer Payments: <strong>₦{{ number_format($summary['customer_payments'], 2) }}</strong></p>
                    <p class="text-primary">Expected Cash: <strong>₦{{ number_format($shift->opening_cash + $summary['cash_sales'] + $summary['customer_payments'], 2) }}</strong></p>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Closing Cash (Count your drawer)</label>
                        <input type="number" name="closing_cash" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Close Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection











