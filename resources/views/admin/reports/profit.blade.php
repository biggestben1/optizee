@extends('layouts.admin')

@section('title', 'Profit or Loss Report')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active" aria-current="page">Profit or Loss</li>
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
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fe fe-filter me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.reports.export.profit', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" class="btn btn-success">
                    <i class="fe fe-download me-1"></i> Download Excel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary-transparent">
            <div class="card-body text-center">
                <h3 class="text-primary mb-1">₦{{ number_format($totalRevenue, 2) }}</h3>
                <p class="text-muted mb-0">Total Revenue</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger-transparent">
            <div class="card-body text-center">
                <h3 class="text-danger mb-1">₦{{ number_format($totalDirectCost, 2) }}</h3>
                <p class="text-muted mb-0">Total Direct Costs</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success-transparent">
            <div class="card-body text-center">
                <h3 class="text-success mb-1">₦{{ number_format($grossProfit, 2) }}</h3>
                <p class="text-muted mb-0">Gross Profit</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info-transparent">
            <div class="card-body text-center">
                <h3 class="text-info mb-1">{{ number_format($grossMargin, 1) }}%</h3>
                <p class="text-muted mb-0">Gross Margin</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success-transparent">
                <h4 class="card-title mb-0">Profit or Loss Statement</h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered mb-0">
                    <tr>
                        <td class="fw-bold">Total Sales Revenue</td>
                        <td class="text-end fw-bold">₦{{ number_format($totalRevenue, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bg-light fw-bold">Direct Costs (COGS)</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Product Purchase Costs</td>
                        <td class="text-end text-danger">- ₦{{ number_format($productCost, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Direct Operating Expenses</td>
                        <td class="text-end text-danger">- ₦{{ number_format($directExpenses, 2) }}</td>
                    </tr>
                    <tr class="table-success fw-bold">
                        <td>GROSS PROFIT</td>
                        <td class="text-end">₦{{ number_format($grossProfit, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bg-light fw-bold">Indirect Expenses</td>
                    </tr>
                    <tr>
                        <td class="ps-4">General Operating Expenses</td>
                        <td class="text-end text-danger">- ₦{{ number_format($indirectExpenses, 2) }}</td>
                    </tr>
                    <tr class="{{ $netProfit >= 0 ? 'table-primary' : 'table-danger' }} fw-bold">
                        <td>NET {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }}</td>
                        <td class="text-end">₦{{ number_format($netProfit, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info-transparent">
                <h4 class="card-title mb-0">Analysis</h4>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5>Gross Margin</h5>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-success" style="width: {{ $grossMargin }}%">
                            {{ number_format($grossMargin, 1) }}%
                        </div>
                    </div>
                </div>
                <div>
                    <h5>Net Profit Margin</h5>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-primary" style="width: {{ $netMargin > 0 ? $netMargin : 0 }}%">
                            {{ number_format($netMargin, 1) }}%
                        </div>
                    </div>
                </div>
                <div class="mt-4 p-3 bg-light rounded">
                    <p class="mb-1"><i class="fe fe-info me-2 text-info"></i> <strong>Gross Profit</strong> is your sales minus direct costs to produce/buy those goods.</p>
                    <p class="mb-0"><i class="fe fe-info me-2 text-info"></i> <strong>Net Profit</strong> is what remains after paying all other business expenses.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Profit -->
<div class="card">
    <div class="card-header">
        <h4 class="card-title mb-0">Daily Performance</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Direct Costs</th>
                        <th class="text-end">Gross Profit</th>
                        <th class="text-end">Indirect Exp.</th>
                        <th class="text-end">Net Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyProfit->sortKeysDesc() as $date => $data)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($date)->format('M d, Y (l)') }}</td>
                        <td class="text-end">₦{{ number_format($data['revenue'], 2) }}</td>
                        <td class="text-end text-danger">₦{{ number_format($data['cost'], 2) }}</td>
                        <td class="text-end text-success fw-bold">₦{{ number_format($data['gross_profit'], 2) }}</td>
                        <td class="text-end text-warning">₦{{ number_format($data['indirect_expenses'], 2) }}</td>
                        <td class="text-end fw-bold {{ $data['net_profit'] >= 0 ? 'text-primary' : 'text-danger' }}">
                            ₦{{ number_format($data['net_profit'], 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No sales data for this period</td>
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
        order: [[0, 'desc']],
        pageLength: 25
    });
});
</script>
@endpush
