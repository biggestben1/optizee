@extends('layouts.admin')

@section('title', 'Hotel Inventory Value Report')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.hotel-inventory.index') }}">Hotel Inventory</a></li>
<li class="breadcrumb-item active" aria-current="page">Value Report</li>
@endsection

@section('actions')
<button onclick="window.print()" class="btn btn-primary d-print-none">
    <i class="fe fe-printer me-2"></i> Print Report
</button>
@endsection

@section('content')
<!-- Filter Section -->
<div class="row d-print-none mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.hotel-inventory.report') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control select2">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="form-label">Stock Status</label>
                            <select name="stock_status" class="form-control">
                                <option value="">All Items</option>
                                <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                                <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fe fe-filter me-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card bg-primary-transparent text-primary">
            <div class="card-body">
                <h6 class="mb-1 fw-semibold">Total Cost Value (COGS)</h6>
                <h3 class="mb-0">₦{{ number_format($totalCostValue, 2) }}</h3>
                <small class="text-muted">Value of stock at cost price</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success-transparent text-success">
            <div class="card-body">
                <h6 class="mb-1 fw-semibold">Projected Sales Value</h6>
                <h3 class="mb-0">₦{{ number_format($totalSalesValue, 2) }}</h3>
                <small class="text-muted">Total value if all items are sold</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info-transparent text-info">
            <div class="card-body">
                <h6 class="mb-1 fw-semibold">Projected Gross Profit</h6>
                <h3 class="mb-0">₦{{ number_format($projectedProfit, 2) }}</h3>
                <small class="text-muted">Expected margin on current stock</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detailed Inventory Valuation</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom">
                        <thead class="bg-light">
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th class="text-center">Stock Qty</th>
                                <th class="text-right">Unit Cost</th>
                                <th class="text-right">Total Cost</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Total Sales Value</th>
                                <th class="text-right">Projected Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            @php
                                $totalCost = $product->stock_quantity * $product->cost_price;
                                $totalSales = $product->stock_quantity * $product->selling_price;
                                $profit = $totalSales - $totalCost;
                            @endphp
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name }}</td>
                                <td class="text-center">{{ $product->stock_quantity }} {{ $product->unit }}</td>
                                <td class="text-right">₦{{ number_format($product->cost_price, 2) }}</td>
                                <td class="text-right">₦{{ number_format($totalCost, 2) }}</td>
                                <td class="text-right">₦{{ number_format($product->selling_price, 2) }}</td>
                                <td class="text-right">₦{{ number_format($totalSales, 2) }}</td>
                                <td class="text-right fw-semibold {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
                                    ₦{{ number_format($profit, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="4" class="text-right">GRAND TOTALS:</td>
                                <td class="text-right">₦{{ number_format($totalCostValue, 2) }}</td>
                                <td></td>
                                <td class="text-right">₦{{ number_format($totalSalesValue, 2) }}</td>
                                <td class="text-right">₦{{ number_format($projectedProfit, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
