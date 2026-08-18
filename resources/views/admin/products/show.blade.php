@extends('layouts.admin')

@section('title', $product->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
@endsection

@section('actions')
<a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
    <i class="fe fe-edit me-2"></i> Edit Product
</a>
<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
    <i class="fe fe-package me-2"></i> Adjust Stock
</button>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Product Details</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <td class="fw-bold">Category</td>
                        <td>{{ $product->category->name }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Cost Price</td>
                        <td>₦{{ number_format($product->cost_price, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Selling Price</td>
                        <td>₦{{ number_format($product->selling_price, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Profit Margin</td>
                        <td class="text-success">₦{{ number_format($product->getProfit(), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Current Stock</td>
                        <td>
                            @if($product->stock_quantity <= 0)
                            <span class="badge bg-danger">Out of Stock</span>
                            @elseif($product->isLowStock())
                            <span class="badge bg-warning">{{ $product->stock_quantity }} (Low)</span>
                            @else
                            <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Reorder Level</td>
                        <td>{{ $product->reorder_level }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Unit</td>
                        <td>{{ ucfirst($product->unit) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Status</td>
                        <td>
                            @if($product->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-danger">Inactive</span>
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
                <h3 class="card-title">Stock Movement History</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Before</th>
                                <th>After</th>
                                <th>User</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($product->stockMovements()->latest()->take(20)->get() as $movement)
                            <tr>
                                <td>{{ $movement->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($movement->type == 'in')
                                    <span class="badge bg-success">IN</span>
                                    @elseif($movement->type == 'out')
                                    <span class="badge bg-danger">OUT</span>
                                    @else
                                    <span class="badge bg-warning">{{ strtoupper($movement->type) }}</span>
                                    @endif
                                </td>
                                <td>{{ $movement->quantity }}</td>
                                <td>{{ $movement->stock_before }}</td>
                                <td>{{ $movement->stock_after }}</td>
                                <td>{{ $movement->user->name }}</td>
                                <td>{{ $movement->reason ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No stock movements recorded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.products.adjust-stock', $product) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Stock - {{ $product->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Current Stock: <strong>{{ $product->stock_quantity }}</strong></p>
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type</label>
                        <select name="type" class="form-select" required>
                            <option value="in">Stock In (Add)</option>
                            <option value="out">Stock Out (Remove)</option>
                            <option value="adjustment">Adjustment</option>
                            <option value="damaged">Damaged</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="2" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Adjust Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection











