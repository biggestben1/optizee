@extends('layouts.admin')

@section('title', 'Dashboard')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
<!-- ROW-1 -->
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xl-12">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <a href="{{ route('admin.reports.daily') }}" class="card overflow-hidden dashboard-card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="mt-2">
                                <h6 class="">Today's Sales</h6>
                                <h2 class="mb-0 number-font">₦{{ number_format($todaySales, 2) }}</h2>
                            </div>
                            <div class="ms-auto">
                                <div class="chart-wrapper mt-1">
                                    <i class="fe fe-shopping-cart text-primary" style="font-size: 40px;"></i>
                                </div>
                            </div>
                        </div>
                        <span class="text-muted fs-12"><span class="text-secondary"><i class="fe fe-activity"></i> {{ $todayTransactions }}</span> Transactions</span>
                    </div>
                </a>
            </div>
            @if(!$isCashier)
            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <a href="{{ route('admin.reports.profit') }}" class="card overflow-hidden dashboard-card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="mt-2">
                                <h6 class="">Today's Profit</h6>
                                <h2 class="mb-0 number-font text-success">₦{{ number_format($todayProfit, 2) }}</h2>
                            </div>
                            <div class="ms-auto">
                                <div class="chart-wrapper mt-1">
                                    <i class="fe fe-trending-up text-success" style="font-size: 40px;"></i>
                                </div>
                            </div>
                        </div>
                        <span class="text-muted fs-12"><span class="text-success"><i class="fe fe-arrow-up-circle"></i> Net</span> earnings today</span>
                    </div>
                </a>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <a href="{{ route('admin.reports.profit') }}" class="card overflow-hidden dashboard-card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="mt-2">
                                <h6 class="">Today's Cost (COGS)</h6>
                                <h2 class="mb-0 number-font text-info">₦{{ number_format($todayCost, 2) }}</h2>
                            </div>
                            <div class="ms-auto">
                                <div class="chart-wrapper mt-1">
                                    <i class="fe fe-package text-info" style="font-size: 40px;"></i>
                                </div>
                            </div>
                        </div>
                        <span class="text-muted fs-12"><span class="text-info"><i class="fe fe-layers"></i> Cost</span> of items sold</span>
                    </div>
                </a>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <a href="{{ route('admin.expenses.index', ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}" class="card overflow-hidden dashboard-card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="mt-2">
                                <h6 class="">Today's Expenses</h6>
                                <h2 class="mb-0 number-font text-danger">₦{{ number_format($todayExpenses, 2) }}</h2>
                            </div>
                            <div class="ms-auto">
                                <div class="chart-wrapper mt-1">
                                    <i class="fe fe-minus-circle text-danger" style="font-size: 40px;"></i>
                                </div>
                            </div>
                        </div>
                        <span class="text-muted fs-12"><span class="text-danger"><i class="fe fe-dollar-sign"></i> Other</span> expenses today</span>
                    </div>
                </a>
            </div>
            @endif
        </div>
        <div class="row">
            @if(!$isCashier)
            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <a href="{{ route('admin.customers.index', ['status' => 'owing']) }}" class="card overflow-hidden dashboard-card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="mt-2">
                                <h6 class="">Customer Debt</h6>
                                <h2 class="mb-0 number-font text-warning">₦{{ number_format($totalCustomerDebt, 2) }}</h2>
                            </div>
                            <div class="ms-auto">
                                <div class="chart-wrapper mt-1">
                                    <i class="fe fe-users text-warning" style="font-size: 40px;"></i>
                                </div>
                            </div>
                        </div>
                        <span class="text-muted fs-12"><span class="text-warning"><i class="fe fe-alert-triangle"></i> Outstanding</span> credit balance</span>
                    </div>
                </a>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <a href="{{ route('admin.suppliers.index', ['owing' => 1]) }}" class="card overflow-hidden dashboard-card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="mt-2">
                                <h6 class="">Supplier Debt</h6>
                                <h2 class="mb-0 number-font text-danger">₦{{ number_format($totalSupplierDebt, 2) }}</h2>
                            </div>
                            <div class="ms-auto">
                                <div class="chart-wrapper mt-1">
                                    <i class="fe fe-truck text-danger" style="font-size: 40px;"></i>
                                </div>
                            </div>
                        </div>
                        <span class="text-muted fs-12"><span class="text-danger"><i class="fe fe-arrow-down-circle"></i> Owed</span> to suppliers</span>
                    </div>
                </a>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <a href="{{ route('admin.products.index', ['stock_status' => 'low']) }}" class="card overflow-hidden dashboard-card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="mt-2">
                                <h6 class="">Low Stock Items</h6>
                                <h2 class="mb-0 number-font text-primary">{{ $lowStockProducts }}</h2>
                            </div>
                            <div class="ms-auto">
                                <div class="chart-wrapper mt-1">
                                    <i class="fe fe-box text-primary" style="font-size: 40px;"></i>
                                </div>
                            </div>
                        </div>
                        <span class="text-muted fs-12"><span class="text-primary"><i class="fe fe-alert-circle"></i> Items</span> below reorder level</span>
                    </div>
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
<!-- /ROW-1 -->

<!-- ROW-2 -->
<div class="row">
    <div class="col-12 col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                        <a href="{{ route('admin.pos.index') }}" class="btn btn-primary btn-lg w-100">
                            <i class="fe fe-shopping-cart me-2"></i> New Sale
                        </a>
                    </div>
                    @if(!$isCashier)
                    @php $shift = auth()->user()->getOpenShift(); @endphp
                    @if(!$shift)
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                        <a href="{{ route('admin.shifts.create') }}" class="btn btn-success btn-lg w-100">
                            <i class="fe fe-play-circle me-2"></i> Open Shift
                        </a>
                    </div>
                    @else
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                        <a href="{{ route('admin.shifts.current') }}" class="btn btn-warning btn-lg w-100">
                            <i class="fe fe-clock me-2"></i> View Shift
                        </a>
                    </div>
                    @endif
                    @endif
                    @if(!$isCashier)
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-info btn-lg w-100">
                            <i class="fe fe-users me-2"></i> Customers
                        </a>
                    </div>
                    @endif
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                        <a href="{{ route('admin.reports.daily') }}" class="btn btn-secondary btn-lg w-100">
                            <i class="fe fe-bar-chart-2 me-2"></i> Daily Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /ROW-2 -->

<!-- ROW-3 -->
<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Recent Sales</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Cashier</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                            <tr>
                                <td><a href="{{ route('admin.pos.show', $sale) }}">{{ $sale->invoice_number }}</a></td>
                                <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                <td>₦{{ number_format($sale->total, 2) }}</td>
                                <td>
                                    @if($sale->is_credit_sale)
                                    <span class="badge bg-warning">Credit</span>
                                    @else
                                    <span class="badge bg-success">{{ ucfirst($sale->payment_method) }}</span>
                                    @endif
                                </td>
                                <td>{{ $sale->user->name }}</td>
                                <td>{{ $sale->created_at->format('H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No sales today</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        @if($isCashier && $myAssignments && $myAssignments->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">My Assignments</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($myAssignments as $assignment)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $assignment->section->name }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ ucfirst($assignment->shift_type) }} Shift
                                    @if($assignment->shift_start && $assignment->shift_end)
                                        ({{ date('g:i A', strtotime($assignment->shift_start)) }} - {{ date('g:i A', strtotime($assignment->shift_end)) }})
                                    @endif
                                </small>
                                @if($assignment->assignedBy)
                                <br>
                                <small class="text-muted">Assigned by: {{ $assignment->assignedBy->name }}</small>
                                @endif
                            </div>
                            <span class="badge bg-success">Active</span>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @if(!$isCashier)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Low Stock Alert</h3>
            </div>
            <div class="card-body">
                @if($lowStockProducts > 0)
                <div class="alert alert-warning">
                    <i class="fe fe-alert-triangle me-2"></i>
                    <strong>{{ $lowStockProducts }}</strong> products are running low on stock.
                    <a href="{{ route('admin.products.index', ['stock_status' => 'low']) }}" class="alert-link">View all</a>
                </div>
                @else
                <div class="alert alert-success">
                    <i class="fe fe-check-circle me-2"></i>
                    All products are well stocked!
                </div>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Top Products Today</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @forelse($topProducts->take(5) as $product)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $product->name }}
                        <span class="badge bg-primary rounded-pill">{{ $product->total_sold ?? 0 }} sold</span>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted">No sales today</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- /ROW-3 -->
@endsection
