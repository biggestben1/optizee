@extends('layouts.admin')

@section('title', 'Daily Report')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active" aria-current="page">Daily Report</li>
@endsection

@section('content')
<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Select Date</label>
                <input type="date" name="date" class="form-control" value="{{ $date->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fe fe-filter me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.reports.export.daily', ['date' => $date->format('Y-m-d')]) }}" class="btn btn-success">
                    <i class="fe fe-download me-1"></i> Download Excel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-primary-transparent">
            <div class="card-body text-center">
                <h3 class="text-primary mb-1">₦{{ number_format($summary['total_sales'], 2) }}</h3>
                <p class="text-muted mb-0">Total Sales</p>
                @php
                    $salesDiff = $summary['total_sales'] - $prevSummary['total_sales'];
                    $salesPercent = $prevSummary['total_sales'] > 0 ? ($salesDiff / $prevSummary['total_sales']) * 100 : 100;
                @endphp
                <small class="{{ $salesDiff >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                    <i class="fe fe-arrow-{{ $salesDiff >= 0 ? 'up' : 'down' }}"></i>
                    {{ number_format(abs($salesPercent), 1) }}%
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary-transparent">
            <div class="card-body text-center">
                <h3 class="text-secondary mb-1">₦{{ number_format($summary['total_subtotal'] ?? 0, 2) }}</h3>
                <p class="text-muted mb-0">Subtotal</p>
                <small class="text-muted">Previous: ₦{{ number_format($prevSummary['total_sales'], 0) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-danger-transparent">
            <div class="card-body text-center">
                <h3 class="text-danger mb-1">₦{{ number_format($summary['total_vat'] ?? 0, 2) }}</h3>
                <p class="text-muted mb-0">VAT (7.5%)</p>
                <small class="text-muted">&nbsp;</small>
            </div>
        </div>
    </div>
    @if(!$isCashier)
    <div class="col-md-2">
        <div class="card bg-success-transparent">
            <div class="card-body text-center">
                <h3 class="text-success mb-1">₦{{ number_format($summary['total_profit'], 2) }}</h3>
                <p class="text-muted mb-0">Total Profit</p>
                @php
                    $profitDiff = $summary['total_profit'] - $prevSummary['total_profit'];
                    $profitPercent = $prevSummary['total_profit'] > 0 ? ($profitDiff / $prevSummary['total_profit']) * 100 : 100;
                @endphp
                <small class="{{ $profitDiff >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                    <i class="fe fe-arrow-{{ $profitDiff >= 0 ? 'up' : 'down' }}"></i>
                    {{ number_format(abs($profitPercent), 1) }}%
                </small>
            </div>
        </div>
    </div>
    @endif
    <div class="col-md-2">
        <div class="card bg-info-transparent">
            <div class="card-body text-center">
                <h3 class="text-info mb-1">{{ $summary['transactions'] }}</h3>
                <p class="text-muted mb-0">Transactions</p>
                @php
                    $transDiff = $summary['transactions'] - $prevSummary['transactions'];
                @endphp
                <small class="{{ $transDiff >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                    {{ $transDiff >= 0 ? '+' : '' }}{{ $transDiff }} vs yesterday
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning-transparent">
            <div class="card-body text-center">
                <h3 class="text-warning mb-1">₦{{ number_format($summary['credit'], 2) }}</h3>
                <p class="text-muted mb-0">Credit Sales</p>
                <small class="text-muted">&nbsp;</small>
            </div>
        </div>
    </div>
</div>

<!-- Payment Methods -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Sales Breakdown</h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <strong>₦{{ number_format($summary['total_subtotal'] ?? 0, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>VAT (7.5%)</span>
                    <strong class="text-danger">₦{{ number_format($summary['total_vat'] ?? 0, 2) }}</strong>
                </div>
                @if(($summary['total_discount'] ?? 0) > 0)
                <div class="d-flex justify-content-between mb-2">
                    <span>Discount</span>
                    <strong class="text-danger">-₦{{ number_format($summary['total_discount'] ?? 0, 2) }}</strong>
                </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span>Cash</span>
                    <strong>₦{{ number_format($summary['cash'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Bank Transfer</span>
                    <strong>₦{{ number_format($summary['transfer'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Bar n Kitchen</span>
                    <strong>₦{{ number_format($summary['pos'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Credit</span>
                    <strong>₦{{ number_format($summary['credit'], 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
    @if(!$isCashier)
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Sales by Staff</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th class="text-center">Transactions</th>
                                <th class="text-end">Total Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffSales as $staff)
                            <tr>
                                <td>{{ $staff['user'] }}</td>
                                <td class="text-center">{{ $staff['count'] }}</td>
                                <td class="text-end">₦{{ number_format($staff['total'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No sales recorded</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Hourly Sales -->
<div class="card mb-4">
    <div class="card-header">
        <h4 class="card-title mb-0">Hourly Sales Breakdown</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        @for($h = 0; $h < 24; $h++)
                        <th class="text-center" style="min-width: 60px;">{{ sprintf('%02d', $h) }}:00</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @for($h = 0; $h < 24; $h++)
                        <td class="text-center">
                            @if(isset($hourlyData[sprintf('%02d', $h)]))
                            ₦{{ number_format($hourlyData[sprintf('%02d', $h)], 0) }}
                            @else
                            -
                            @endif
                        </td>
                        @endfor
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pending Orders -->
@if($pendingOrders->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-warning-transparent">
        <h4 class="card-title mb-0">
            <i class="fe fe-clock me-2"></i>Pending Orders - {{ $date->format('M d, Y') }}
            <span class="badge bg-warning ms-2">{{ $pendingOrders->count() }}</span>
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Time</th>
                        @if(!$isCashier)
                        <th>Cashier</th>
                        @endif
                        <th>Customer/Guest</th>
                        <th>Table</th>
                        <th>Items</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingOrders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.pos.index', ['table_id' => $order->table_id, 'guest_id' => $order->table_guest_id]) }}">
                                {{ $order->invoice_number }}
                            </a>
                        </td>
                        <td>{{ $order->created_at->format('H:i:s') }}</td>
                        @if(!$isCashier)
                        <td>{{ $order->user->name }}</td>
                        @endif
                        <td>
                            @if($order->tableGuest)
                                {{ $order->tableGuest->guest_name }}
                            @elseif($order->customer)
                                {{ $order->customer->name }}
                            @else
                                Walk-in
                            @endif
                        </td>
                        <td>
                            @if($order->table)
                                {{ $order->table->number }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $order->items->sum('quantity') }} items</td>
                        <td class="text-end">₦{{ number_format($order->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Purchase Requisitions -->
@if($purchaseRequisitions->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-info-transparent">
        <h4 class="card-title mb-0">
            <i class="fe fe-file-text me-2"></i>Purchase Requisitions - {{ $date->format('M d, Y') }}
            <span class="badge bg-info ms-2">{{ $purchaseRequisitions->count() }}</span>
        </h4>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="text-center">
                    <h5 class="mb-0 text-info">₦{{ number_format($prSummary['total_amount'], 2) }}</h5>
                    <small class="text-muted">Total Amount</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="text-center">
                    <h5 class="mb-0 text-warning">{{ $prSummary['pending'] }}</h5>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="text-center">
                    <h5 class="mb-0 text-success">{{ $prSummary['approved'] }}</h5>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="text-center">
                    <h5 class="mb-0 text-primary">{{ $prSummary['completed'] }}</h5>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h5 class="mb-0 text-danger">{{ $prSummary['rejected'] }}</h5>
                    <small class="text-muted">Rejected</small>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>PR Number</th>
                        <th>Requested By</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-end">Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseRequisitions as $pr)
                    <tr>
                        <td><strong>{{ $pr->pr_number }}</strong></td>
                        <td>{{ $pr->requestedBy ? $pr->requestedBy->name : 'N/A' }}</td>
                        <td>{{ Str::limit($pr->description, 50) }}</td>
                        <td>
                            @if($pr->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                            @elseif($pr->status === 'approved')
                            <span class="badge bg-success">Approved</span>
                            @elseif($pr->status === 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                            @elseif($pr->status === 'completed')
                            <span class="badge bg-info">Completed</span>
                            @endif
                        </td>
                        <td class="text-end">₦{{ number_format($pr->amount, 2) }}</td>
                        <td>
                            <a href="{{ route('admin.purchase-requisitions.show', $pr) }}" class="btn btn-sm btn-primary">
                                <i class="fe fe-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- All Sales -->
<div class="card">
    <div class="card-header">
        <h4 class="card-title mb-0">All Transactions - {{ $date->format('M d, Y') }}</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Time</th>
                        <th>Cashier</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Payment</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">VAT</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>
                            <a href="{{ route('admin.pos.show', $sale) }}">{{ $sale->invoice_number }}</a>
                        </td>
                        <td>{{ $sale->created_at->format('H:i:s') }}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                        <td>{{ $sale->items->sum('quantity') }} items</td>
                        <td>
                            @if($sale->is_credit_sale)
                            <span class="badge bg-warning">Credit</span>
                            @else
                            <span class="badge bg-success">{{ ucfirst($sale->payment_method) }}</span>
                            @endif
                        </td>
                        <td class="text-end">₦{{ number_format($sale->subtotal ?? 0, 2) }}</td>
                        <td class="text-end">₦{{ number_format($sale->tax ?? 0, 2) }}</td>
                        <td class="text-end">₦{{ number_format($sale->total, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No sales recorded for this date</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.datatable').DataTable({
        order: [[1, 'desc']],
        pageLength: 25
    });
});
</script>
@endpush

