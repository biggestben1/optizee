@extends('layouts.admin')

@section('title', 'Products Report')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active" aria-current="page">Products Report</li>
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
                <a href="{{ route('admin.reports.export.products', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" class="btn btn-success">
                    <i class="fe fe-download me-1"></i> Download Excel
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- Top Selling Products -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fe fe-trending-up text-success me-2"></i>
                    Top Selling Products
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th class="text-center">Qty Sold</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $index => $product)
                            <tr>
                                <td>
                                    @if($index < 3)
                                    <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'danger') }}">
                                        {{ $index + 1 }}
                                    </span>
                                    @else
                                    {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>{{ $product->product_name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ number_format($product->total_quantity) }}</span>
                                </td>
                                <td class="text-end">₦{{ number_format($product->total_revenue, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No sales data for this period</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Alerts -->
    <div class="col-lg-4">
        <!-- Out of Stock -->
        <div class="card mb-4">
            <div class="card-header bg-danger-transparent">
                <h4 class="card-title mb-0 text-danger">
                    <i class="fe fe-alert-triangle me-2"></i>
                    Out of Stock ({{ $outOfStockProducts->count() }})
                </h4>
            </div>
            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                @forelse($outOfStockProducts as $product)
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div>
                        <strong>{{ $product->name }}</strong>
                        <small class="text-muted d-block">{{ $product->category?->name }}</small>
                    </div>
                    <span class="badge bg-danger">0</span>
                </div>
                @empty
                <p class="text-muted text-center mb-0">
                    <i class="fe fe-check-circle text-success"></i> All products in stock
                </p>
                @endforelse
            </div>
        </div>

        <!-- Low Stock -->
        <div class="card">
            <div class="card-header bg-warning-transparent">
                <h4 class="card-title mb-0 text-warning">
                    <i class="fe fe-alert-circle me-2"></i>
                    Low Stock ({{ $lowStockProducts->count() }})
                </h4>
            </div>
            <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                @forelse($lowStockProducts as $product)
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div>
                        <strong>{{ $product->name }}</strong>
                        <small class="text-muted d-block">{{ $product->category?->name }}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-warning">{{ $product->stock_quantity }}</span>
                        <small class="text-muted d-block">min: {{ $product->reorder_level }}</small>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center mb-0">
                    <i class="fe fe-check-circle text-success"></i> No low stock items
                </p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

