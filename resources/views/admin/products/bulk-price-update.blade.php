@extends('layouts.admin')

@section('title', 'Bulk Price Update')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
<li class="breadcrumb-item active" aria-current="page">Bulk Price Update</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Bulk Price Update</h3>
                <div class="card-options">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i> Back to Products
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('errors'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach(session('errors') as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('admin.products.update-bulk-prices') }}" method="POST" id="bulkPriceForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Filter by Category</label>
                        <select id="categoryFilter" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search products..." autocomplete="off">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 25%;">Product Name</th>
                                    <th style="width: 15%;">Category</th>
                                    <th style="width: 15%;">Current Price</th>
                                    <th style="width: 20%;">New Selling Price</th>
                                    <th style="width: 20%;">Cost Price</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                                @foreach($products as $index => $product)
                                <tr data-category="{{ $product->category_id }}" data-name="{{ strtolower($product->name) }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                    </td>
                                    <td>{{ $product->category->name }}</td>
                                    <td>
                                        <span class="badge bg-info">₦{{ number_format($product->selling_price, 2) }}</span>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">₦</span>
                                            <input type="text" 
                                                   name="prices[{{ $product->id }}]" 
                                                   class="form-control price-input" 
                                                   value="{{ number_format($product->selling_price, 0, '.', ',') }}" 
                                                   placeholder="2,000"
                                                   data-product-id="{{ $product->id }}">
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">₦{{ number_format($product->cost_price, 2) }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <span id="productCount">{{ $products->count() }}</span> product(s) found
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary me-2" onclick="resetPrices()">Reset All</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-2"></i> Update All Prices
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categoryFilter = document.getElementById('categoryFilter');
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('productsTableBody');
        const priceInputs = document.querySelectorAll('.price-input');
        const productCount = document.getElementById('productCount');

        // Format price inputs with commas
        priceInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/,/g, '');
                if (value && !isNaN(value)) {
                    e.target.value = parseFloat(value).toLocaleString('en-US');
                }
            });
        });

        // Remove commas before form submission
        document.getElementById('bulkPriceForm').addEventListener('submit', function(e) {
            priceInputs.forEach(input => {
                let value = input.value.replace(/,/g, '');
                input.value = value;
            });
        });

        // Filter by category
        categoryFilter.addEventListener('change', function() {
            filterProducts();
        });

        // Search products
        searchInput.addEventListener('input', function() {
            filterProducts();
        });

        function filterProducts() {
            const categoryId = categoryFilter.value;
            const searchTerm = searchInput.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const rowCategory = row.getAttribute('data-category');
                const rowName = row.getAttribute('data-name');
                
                const categoryMatch = !categoryId || rowCategory === categoryId;
                const searchMatch = !searchTerm || rowName.includes(searchTerm);

                if (categoryMatch && searchMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            productCount.textContent = visibleCount;
        }

        // Reset all prices to current values
        window.resetPrices = function() {
            if (confirm('Are you sure you want to reset all prices to their current values?')) {
                priceInputs.forEach(input => {
                    const productId = input.getAttribute('data-product-id');
                    // Get current price from the badge in the same row
                    const row = input.closest('tr');
                    const currentPriceBadge = row.querySelector('.badge');
                    if (currentPriceBadge) {
                        const currentPrice = currentPriceBadge.textContent.replace('₦', '').replace(/,/g, '').trim();
                        input.value = parseFloat(currentPrice).toLocaleString('en-US');
                    }
                });
            }
        };
    });
</script>
@endpush
@endsection

