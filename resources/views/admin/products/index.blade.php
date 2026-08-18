@extends('layouts.admin')

@section('title', 'Products')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Products</li>
@endsection

@section('actions')
<a href="{{ route('admin.products.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i> Add Product
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form id="searchForm" action="" method="GET" class="d-flex gap-2 w-100">
                    <input type="text" name="search" id="searchInput" class="form-control" placeholder="Search products..." value="{{ request('search') }}" style="max-width: 250px;" autocomplete="off">
                    <select name="category" id="categorySelect" class="form-select" style="max-width: 200px;">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <select name="stock_status" id="stockSelect" class="form-select" style="max-width: 150px;">
                        <option value="">All Stock</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" style="max-width: 150px;">
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" style="max-width: 150px;">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Reset</a>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Cost</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name }}</td>
                                <td>₦{{ number_format($product->cost_price, 2) }}</td>
                                <td>₦{{ number_format($product->selling_price, 2) }}</td>
                                <td>
                                    @if($product->stock_quantity <= 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($product->isLowStock())
                                    <span class="badge bg-warning">{{ $product->stock_quantity }} (Low)</span>
                                    @else
                                    <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->is_active)
                                    <span class="badge bg-success">Active</span>
                                    @else
                                    <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-info">
                                        <i class="fe fe-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-primary">
                                        <i class="fe fe-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fe fe-trash-2"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No products found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $products->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    const categorySelect = document.getElementById('categorySelect');
    const stockSelect = document.getElementById('stockSelect');
    const searchForm = document.getElementById('searchForm');

    // Search on key press with debounce
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                searchForm.submit();
            }, 500); // Wait 500ms after user stops typing
        });

        // Also search on Enter key immediately
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                e.preventDefault();
                searchForm.submit();
            }
        });
    }

    // Auto-submit on category or stock status change
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            searchForm.submit();
        });
    }

    if (stockSelect) {
        stockSelect.addEventListener('change', function() {
            searchForm.submit();
        });
    }
})();
</script>
@endpush



