@extends('layouts.admin')

@section('title', 'Sales Report')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active" aria-current="page">Sales Report</li>
@endsection

@section('content')
<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fe fe-filter me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.reports.export.sales', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" class="btn btn-success">
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
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary-transparent">
            <div class="card-body text-center">
                <h3 class="text-secondary mb-1">₦{{ number_format($summary['total_subtotal'] ?? 0, 2) }}</h3>
                <p class="text-muted mb-0">Subtotal</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-danger-transparent">
            <div class="card-body text-center">
                <h3 class="text-danger mb-1">₦{{ number_format($summary['total_vat'] ?? 0, 2) }}</h3>
                <p class="text-muted mb-0">VAT (7.5%)</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success-transparent">
            <div class="card-body text-center">
                <h3 class="text-success mb-1">₦{{ number_format($summary['total_profit'], 2) }}</h3>
                <p class="text-muted mb-0">Total Profit</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info-transparent">
            <div class="card-body text-center">
                <h3 class="text-info mb-1">{{ $summary['total_transactions'] }}</h3>
                <p class="text-muted mb-0">Transactions</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning-transparent">
            <div class="card-body text-center">
                <h3 class="text-warning mb-1">₦{{ number_format($summary['average_sale'], 2) }}</h3>
                <p class="text-muted mb-0">Average Sale</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Sales Breakdown</h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span><i class="fe fe-layers text-secondary me-2"></i>Subtotal</span>
                    <strong>₦{{ number_format($summary['total_subtotal'] ?? 0, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span><i class="fe fe-percent text-danger me-2"></i>VAT (7.5%)</span>
                    <strong>₦{{ number_format($summary['total_vat'] ?? 0, 2) }}</strong>
                </div>
                @if(($summary['total_discount'] ?? 0) > 0)
                <div class="d-flex justify-content-between mb-3">
                    <span><i class="fe fe-tag text-warning me-2"></i>Discount</span>
                    <strong class="text-danger">-₦{{ number_format($summary['total_discount'] ?? 0, 2) }}</strong>
                </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <span><i class="fe fe-dollar-sign text-success me-2"></i>Cash</span>
                    <strong>₦{{ number_format($summary['cash_sales'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span><i class="fe fe-credit-card text-info me-2"></i>Bank Transfer</span>
                    <strong>₦{{ number_format($summary['transfer_sales'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span><i class="fe fe-smartphone text-primary me-2"></i>Bar n Kitchen</span>
                    <strong>₦{{ number_format($summary['pos_sales'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span><i class="fe fe-clock text-warning me-2"></i>Credit</span>
                    <strong>₦{{ number_format($summary['credit_sales'], 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Daily Sales Summary</h4>
            </div>
            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                @forelse($dailySales->sortKeysDesc() as $date => $data)
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                    <span>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }} ({{ $data['count'] }} sales)</span>
                    <strong>₦{{ number_format($data['total'], 2) }}</strong>
                </div>
                @empty
                <p class="text-muted text-center mb-0">No sales data</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Purchase Requisitions -->
@if($purchaseRequisitions->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-info-transparent">
        <h4 class="card-title mb-0">
            <i class="fe fe-file-text me-2"></i>Purchase Requisitions ({{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }})
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
                        <th>Date</th>
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
                        <td>{{ $pr->pr_date->format('M d, Y') }}</td>
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">All Sales ({{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }})</h4>
        <button type="button" class="btn btn-danger btn-sm" onclick="clearAllSalesAndOrders()">
            <i class="fe fe-trash-2 me-1"></i>Clear All Sales & Pending Orders
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Date/Time</th>
                        <th>Cashier</th>
                        <th>Customer</th>
                        <th>Payment</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">VAT</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr>
                        <td>
                            <a href="{{ route('admin.pos.show', $sale) }}">{{ $sale->invoice_number }}</a>
                        </td>
                        <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
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
                        <td class="text-end text-success">₦{{ number_format($sale->getProfit(), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-light">
                        <th colspan="5" class="text-end">Total:</th>
                        <th class="text-end">₦{{ number_format($summary['total_subtotal'] ?? 0, 2) }}</th>
                        <th class="text-end">₦{{ number_format($summary['total_vat'] ?? 0, 2) }}</th>
                        <th class="text-end">₦{{ number_format($summary['total_sales'], 2) }}</th>
                        <th class="text-end text-success">₦{{ number_format($summary['total_profit'], 2) }}</th>
                    </tr>
                </tfoot>
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

function clearAllSalesAndOrders() {
    if (!confirm('⚠️ WARNING: This will delete ALL sales and pending orders from the database!\n\nThis action cannot be undone. Are you absolutely sure?')) {
        return;
    }
    
    // Double confirmation
    if (!confirm('This will permanently delete:\n- All completed sales\n- All pending orders\n- All sale items\n\nClick OK to proceed with deletion.')) {
        return;
    }
    
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fe fe-loader me-1"></i>Clearing...';
    
    fetch('{{ route("admin.reports.sales.delete-all") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('✅ Successfully cleared all sales and pending orders!\n\n' + data.message);
            // Reload the page to show updated data
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to clear sales');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error: ' + (error.message || 'Failed to clear sales. Please try again.'));
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>
@endpush

