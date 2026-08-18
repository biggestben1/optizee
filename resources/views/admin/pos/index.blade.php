@extends('layouts.admin')

@section('title', 'Point of Sale')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">POS</li>
@endsection

@push('styles')
<style>
    .product-card {
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
        border-radius: 8px;
        height: 100%;
    }
    .product-card:hover {
        border-color: #5e72e4;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(94, 114, 228, 0.15);
    }
    .product-card.out-of-stock {
        opacity: 0.5;
        pointer-events: none;
        background-color: #f8f9fa;
    }
    .product-grid {
        max-height: calc(100vh - 350px);
        overflow-y: auto;
        padding-right: 10px;
    }
    .product-grid::-webkit-scrollbar {
        width: 8px;
    }
    .product-grid::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .product-grid::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    .product-grid::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    .category-btn {
        margin: 3px;
        border-radius: 20px;
        padding: 6px 16px;
        font-size: 13px;
        transition: all 0.2s;
    }
    .category-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .category-btn.active {
        box-shadow: 0 2px 8px rgba(94, 114, 228, 0.3);
    }
    .cart-item {
        border-bottom: 1px solid #e9ecef;
        padding: 12px 0;
        transition: background-color 0.2s;
    }
    .cart-item:hover {
        background-color: #f8f9fa;
        border-radius: 4px;
        padding-left: 8px;
        padding-right: 8px;
    }
    .cart-item:last-child {
        border-bottom: none;
    }
    .category-btn {
        margin: 5px;
    }
    .category-btn.active {
        background-color: #5e72e4 !important;
        border-color: #5e72e4 !important;
    }
    #cart-items {
        max-height: 400px;
        overflow-y: auto;
    }
    .product-grid {
        max-height: 600px;
        overflow-y: auto;
    }
    .nav-tabs-custom {
        border-bottom: 1px solid #dee2e6;
        padding: 0 15px;
    }
    .nav-tabs-custom .nav-link {
        padding: 8px 12px;
        font-size: 0.875rem;
        border: none;
        border-bottom: 2px solid transparent;
        color: #6c757d;
        cursor: pointer;
        background: none;
    }
    .nav-tabs-custom .nav-link:hover {
        color: #5e72e4;
        border-bottom-color: #5e72e4;
    }
    .nav-tabs-custom .nav-link.active {
        color: #5e72e4;
        border-bottom-color: #5e72e4;
        font-weight: 600;
        background: none;
    }
    .guest-tab-badge {
        font-size: 0.75rem;
        margin-left: 4px;
        padding: 2px 6px;
        border-radius: 10px;
        background: #5e72e4;
        color: white;
    }
    .guest-tab-close {
        margin-left: 6px;
        padding: 0 4px;
        cursor: pointer;
        opacity: 0.6;
        font-size: 0.875rem;
    }
    .guest-tab-close:hover {
        opacity: 1;
        color: #dc3545;
    }
    .pending-order-item {
        border-left: 3px solid #ffc107;
        padding: 10px;
        margin-bottom: 8px;
        background: #fff9e6;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .pending-order-item:hover {
        background: #fff3cd;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .pending-order-item .order-date {
        font-size: 0.75rem;
        color: #856404;
    }
    .bg-warning-transparent {
        background-color: rgba(255, 193, 7, 0.1);
    }
    .bg-success-transparent {
        background-color: rgba(40, 167, 69, 0.1);
    }
    .bg-primary-transparent {
        background-color: rgba(94, 114, 228, 0.1);
    }
    .kitchen-order-item {
        background: #f8fff9;
        border: 1px solid #28a745;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .kitchen-order-item:hover {
        background: #e8f5e9;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.25);
        transform: translateY(-1px);
    }
    .cart-item {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 10px;
        border: 1px solid #e9ecef;
        transition: all 0.2s;
    }
    .cart-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateY(-1px);
        background: #ffffff;
    }
    .cart-item h6 {
        color: #2d3748;
        font-weight: 600;
    }
    #cart-items {
        max-height: calc(100vh - 350px);
        min-height: 400px;
        overflow-y: auto;
        padding: 10px;
    }
    
    .cart-item {
        font-size: 15px;
        padding: 15px !important;
    }
    
    .cart-item h6 {
        font-size: 16px;
        font-weight: 600;
    }
    #cart-items::-webkit-scrollbar {
        width: 6px;
    }
    #cart-items::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    #cart-items::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 3px;
    }
    #cart-items::-webkit-scrollbar-thumb:hover {
        background: #999;
    }
    .product-grid {
        max-height: calc(100vh - 350px);
    }
    .product-grid::-webkit-scrollbar {
        width: 8px;
    }
    .product-grid::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .product-grid::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    .product-grid::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    .category-btn {
        border-radius: 20px;
        padding: 6px 16px;
        font-size: 13px;
        transition: all 0.2s;
    }
    .category-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .category-btn.active {
        box-shadow: 0 2px 8px rgba(94, 114, 228, 0.3);
    }
    .card {
        border-radius: 8px;
        border: 1px solid #e9ecef;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .product-grid {
            max-height: calc(100vh - 200px) !important;
        }
        #cart-items {
            max-height: calc(100vh - 250px) !important;
            min-height: 200px !important;
        }
        .category-btn {
            font-size: 12px;
            padding: 4px 12px;
            margin: 2px;
        }
        .product-card {
            margin-bottom: 10px;
        }
        .form-select-lg {
            font-size: 16px !important; /* Prevents zoom on iOS */
        }
        .table-select, .guest-select {
            font-size: 16px !important;
        }
        .card-body {
            padding: 15px !important;
        }
        .btn {
            padding: 8px 12px;
            font-size: 14px;
        }
        .btn-lg {
            padding: 10px 16px;
            font-size: 16px;
        }
        h4, h5, h6 {
            font-size: 1.1rem;
        }
        .cart-item {
            padding: 10px !important;
            font-size: 14px !important;
        }
        .cart-item h6 {
            font-size: 15px !important;
        }
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s;
        }
        .sidebar.show {
            transform: translateX(0);
        }
        .main-content {
            width: 100% !important;
            margin-left: 0 !important;
        }
    }
    
    @media (max-width: 480px) {
        .product-grid {
            max-height: calc(100vh - 180px) !important;
        }
        #cart-items {
            max-height: calc(100vh - 220px) !important;
            min-height: 150px !important;
        }
        .category-btn {
            font-size: 11px;
            padding: 3px 10px;
            margin: 1px;
        }
        .btn {
            padding: 6px 10px;
            font-size: 13px;
        }
        .card-body {
            padding: 10px !important;
        }
    }
    .card-header {
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
    }
    .product-card {
        border-radius: 8px;
        height: 100%;
    }
</style>
@endpush

@section('content')

@if(auth()->user()->is_admin || (auth()->user()->role && (auth()->user()->isSupervisor() || auth()->user()->isManager())))
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info d-flex justify-content-between align-items-center mb-0">
            <div>
                <i class="fe fe-eye me-2"></i><strong>Supervisor View:</strong> Monitor all pending orders in real-time
            </div>
            <a href="{{ route('admin.pos.supervisor') }}" class="btn btn-primary btn-sm">
                <i class="fe fe-eye me-1"></i>Open Supervisor Dashboard
            </a>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- Products Section -->
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Products</h3>
                <div class="ms-auto">
                    <input type="text" id="product-search" class="form-control" placeholder="Search products..." style="width: 250px;">
                </div>
            </div>
            <div class="card-body">
                <!-- Categories -->
                <div class="mb-3 d-flex flex-wrap align-items-center">
                    <button class="btn btn-outline-primary category-btn active" data-category="all">All</button>
                    <button class="btn btn-outline-success category-btn" data-category="kitchen" data-kitchen-filter="true">
                        <i class="fe fe-utensils me-1"></i>Kitchen / Food
                    </button>
                    @foreach($categories as $category)
                    <button class="btn btn-outline-primary category-btn" data-category="{{ $category->id }}">{{ $category->name }}</button>
                    @endforeach
                </div>
                
                <!-- Products Grid -->
                <div class="row product-grid g-3" id="products-container">
                    @php
                        $hasProducts = false;
                        foreach($categories as $category) {
                            if($category->products->count() > 0) {
                                $hasProducts = true;
                                break;
                            }
                        }
                    @endphp
                    @if($hasProducts)
                        @foreach($categories as $category)
                            @foreach($category->products as $product)
                            <div class="col-md-4 col-lg-3 product-item" data-category="{{ $category->id }}" style="display: block;">
                                <div class="card product-card h-100 {{ $product->stock_quantity <= 0 ? 'out-of-stock' : '' }}" 
                                     data-product-id="{{ $product->id }}"
                                     data-product-name="{{ $product->name }}"
                                     data-product-price="{{ $product->selling_price }}"
                                     data-product-stock="{{ $product->stock_quantity }}"
                                     onclick="handleProductClick(this)">
                                    <div class="card-body text-center p-3 d-flex flex-column justify-content-center">
                                        <h6 class="mb-2 fw-bold">{{ $product->name }}</h6>
                                        <p class="text-primary mb-2 fw-bold fs-5">₦{{ number_format($product->selling_price, 2) }}</p>
                                        <small class="text-muted">Stock: {{ $product->stock_quantity }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @endforeach
                    @else
                        <div class="col-12">
                            <div class="alert alert-info text-center py-5">
                                <i class="fe fe-info me-2"></i>No products available. Please add products first.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Section -->
    <div class="col-12 col-lg-5">
        <!-- Kitchen Ready Orders Section -->
        <div class="card border-success mb-3 shadow-sm">
            <div class="card-header bg-success-transparent d-flex justify-content-between align-items-center py-2">
                <h6 class="card-title mb-0 text-success fw-bold">
                    <i class="fe fe-check-circle me-2"></i>Kitchen Ready
                </h6>
                <button class="btn btn-sm btn-outline-success btn-sm" onclick="refreshKitchenOrders()" title="Refresh kitchen orders">
                    <i class="fe fe-refresh-cw"></i>
                </button>
            </div>
            <div class="card-body p-2" style="max-height: 150px; overflow-y: auto;" id="kitchen-orders-container">
                @if($readyKitchenOrders->count() > 0)
                    @foreach($readyKitchenOrders as $order)
                    <div class="kitchen-order-item mb-2 p-2 border rounded" data-order-id="{{ $order->id }}">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <strong class="text-success">{{ $order->invoice_number }}</strong>
                                @if($order->table)
                                <br><small class="text-muted">Table: {{ $order->table->number }}</small>
                                @endif
                                @if($order->tableGuest)
                                <br><small class="text-muted">Guest: {{ $order->tableGuest->guest_name }}</small>
                                @endif
                            </div>
                            <button class="btn btn-sm btn-success" onclick="addKitchenOrderToCart({{ $order->id }})" title="Add all items to cart">
                                <i class="fe fe-plus"></i> Add
                            </button>
                        </div>
                        <div class="small text-muted">
                            @foreach($order->items->take(3) as $item)
                            <div>{{ $item->product_name }} × {{ $item->quantity }}</div>
                            @endforeach
                            @if($order->items->count() > 3)
                            <div>+ {{ $order->items->count() - 3 }} more</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-center text-muted mb-0">No ready orders from kitchen</p>
                @endif
            </div>
        </div>
        
        <!-- Main Cart Card -->
        <div class="card shadow-sm" style="min-height: 600px;">
            <div class="card-header bg-primary-transparent d-flex flex-wrap align-items-center gap-2 py-3">
                <h4 class="card-title mb-0 flex-grow-1 fw-bold" id="cart-header">
                    <i class="fe fe-shopping-cart me-2"></i>Active Orders
                </h4>
                <span id="auto-save-indicator" class="badge bg-success" style="display: none;">
                    <i class="fe fe-check me-1"></i>Saved
                </span>
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-outline-danger" onclick="clearCart()" title="Clear current cart">
                        <i class="fe fe-trash-2"></i>
                    </button>
                    <button class="btn btn-outline-warning" onclick="clearAllPendingOrders()" title="Clear all pending orders">
                        <i class="fe fe-database"></i>
                    </button>
                    <a href="{{ route('admin.kitchen.print-all') }}" class="btn btn-outline-success" target="_blank" title="Print all kitchen orders">
                        <i class="fe fe-printer"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Active Guest Orders Tabs -->
                <div id="active-orders-tabs" class="border-bottom" style="display: none;">
                    <ul class="nav nav-tabs nav-tabs-custom" id="guest-orders-tabs" role="tablist">
                        <!-- Tabs will be dynamically added here -->
                    </ul>
                </div>
                
                <!-- Cart Items Display (Always Visible) -->
                <div class="p-4" style="min-height: 400px;">
                    <div id="cart-items" style="min-height: 350px;">
                        <p class="text-center text-muted py-5" id="empty-cart-message">Select a guest to start taking orders</p>
                    </div>
                </div>
                
                <!-- Tab Content (Hidden by default, shown when tabs exist) -->
                <div class="tab-content p-3" id="guest-orders-content" style="display: none;">
                    <!-- Tab panes will be added here dynamically -->
                </div>

                <hr>


                <!-- Table Selection Section -->
                <div class="card border-primary mb-3" id="table-section">
                    <div class="card-header bg-primary-transparent">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="fe fe-grid me-2"></i>Table & Guest Selection
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">Select Table</label>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="showQuickCreateTableModal()">
                                    <i class="fe fe-plus me-1"></i>Quick Create Table
                                </button>
                            </div>
                            <select id="table-select" class="form-select form-select-lg" onchange="loadTableGuests()">
                                <option value="">-- Select Table --</option>
                                @foreach($tables as $table)
                                <option value="{{ $table->id }}" 
                                        {{ $selectedTable && $selectedTable->id == $table->id ? 'selected' : '' }}
                                        data-status="{{ $table->status }}">
                                    {{ $table->number }} - {{ $table->name ?? 'Table ' . $table->number }}
                                    @if($table->status === 'occupied')
                                    ({{ $table->activeGuests->count() }} guest(s))
                                    @elseif($table->status === 'available')
                                    (Available)
                                    @endif
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">
                                <i class="fe fe-info me-1"></i>Select a table or create a new one to start taking orders
                            </small>
                        </div>

                        <!-- Table Info Display -->
                        <div id="table-info" style="display: none;" class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong id="table-name-display"></strong>
                                <span class="badge bg-success" id="table-status-badge">Occupied</span>
                            </div>
                            <small class="text-muted" id="table-capacity-display"></small>
                        </div>

                        <!-- Guests List -->
                        <div id="guests-section" style="display: none;">
                            <label class="form-label fw-bold mb-2">
                                <i class="fe fe-users me-1"></i>Select Guest for This Order
                                <span class="badge bg-warning ms-2" id="guest-required-badge" style="display: none;">Required</span>
                            </label>
                            
                            <!-- Selected Guest Indicator -->
                            <div id="selected-guest-indicator" class="alert alert-success mb-2" style="display: none;">
                                <i class="fe fe-check-circle me-2"></i>
                                <strong>Ordering for:</strong> <span id="selected-guest-name"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="clearGuestSelection()">
                                    <i class="fe fe-x"></i> Clear
                                </button>
                            </div>
                            
                            <!-- Important Notice -->
                            <div class="alert alert-info mb-2" id="guest-selection-notice">
                                <small>
                                    <i class="fe fe-info me-1"></i>
                                    <strong>For separate bills:</strong> Select a guest before adding items. Each guest will have their own bill.
                                </small>
                            </div>
                            
                            <!-- Pending Orders Display -->
                            <div id="pending-orders-section" class="mb-3" style="display: none;">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning-transparent py-2">
                                        <h6 class="mb-0 text-warning">
                                            <i class="fe fe-clock me-2"></i>Pending Orders
                                            <span class="badge bg-warning text-dark ms-2" id="pending-orders-count">0</span>
                                        </h6>
                                    </div>
                                    <div class="card-body p-2" id="pending-orders-list">
                                        <!-- Pending orders will be loaded here -->
                                    </div>
                                </div>
                            </div>
                            
                            <div id="guests-list" class="mb-2">
<!-- Guests will be loaded here -->
                            </div>
                            <select id="guest-select" class="form-select" style="display: none;">
                                <option value="">Select a Guest</option>
                            </select>
                            <small class="text-muted d-block mb-2">
                                <i class="fe fe-info me-1"></i>
                                <strong>Click on a guest card to select them.</strong> Each guest will have a separate bill.
                            </small>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-guest-btn" style="display: none;" onclick="showAddGuestModal()">
                                <i class="fe fe-user-plus me-1"></i>Add New Guest to Table
                            </button>
                        </div>

                        <!-- No Table Selected Message -->
                        <div id="no-table-message" class="text-center text-muted py-3">
                            <i class="fe fe-grid" style="font-size: 32px;"></i>
                            <p class="mb-0 mt-2">Select a table to start taking orders</p>
                        </div>
                    </div>
                </div>

                <!-- Customer Selection (Walk-in vs Credit) -->
                <div class="px-3 mb-4">
                    <label class="form-label fw-bold mb-2 text-primary"><i class="fe fe-user me-1"></i>Customer Category</label>
                    <div class="d-flex gap-3 mb-3 p-2 bg-light rounded shadow-sm">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="customer_category" id="category-walkin" value="walkin" checked onchange="toggleCustomerCategory()">
                            <label class="form-check-label fw-semibold" for="category-walkin">Walk-in Customer</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="customer_category" id="category-credit" value="credit" onchange="toggleCustomerCategory()">
                            <label class="form-check-label fw-semibold" for="category-credit">Credit Account</label>
                        </div>
                    </div>
                    
                    <div id="credit-customer-select-container" style="display: none;" class="mt-2 p-2 border rounded border-primary bg-light-primary">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold mb-0 text-primary">Select Credit Customer</label>
                            <button type="button" id="manage-customer-btn" class="btn btn-sm btn-outline-primary py-0" style="display: inline-block;" onclick="showManageCustomerModal()" disabled>
                                <i class="fe fe-settings me-1"></i>Active Credit
                            </button>
                        </div>
                        <select id="customer-select" class="form-select select2">
                            <option value="">-- Choose Customer --</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" 
                                    data-credit-limit="{{ $customer->credit_limit }}"
                                    data-credit-balance="{{ $customer->credit_balance }}"
                                    data-credit-enabled="{{ $customer->credit_enabled ? 'true' : 'false' }}">
                                {{ $customer->name }} 
                                @if($customer->credit_enabled)
                                    (Credit: ₦{{ number_format($customer->getAvailableCredit(), 2) }})
                                @else
                                    (Cash Only)
                                @endif
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-2">Required for credit sales. If a customer is selected, payment method defaults to Credit.</small>
                    </div>
                </div>

                <!-- Quick Guide -->
                <div class="alert alert-info mb-3" id="quick-guide">
                    <h6 class="alert-heading"><i class="fe fe-info me-2"></i>How to Take Orders for Different Customers at a Table:</h6>
                    <ol class="mb-0 small">
                        <li><strong>Create/Select Table:</strong> Click "Quick Create Table" or select from dropdown</li>
                        <li><strong>Add Guests:</strong> Click "Add New Guest" button and enter guest names</li>
                        <li><strong>Link Customers (Optional):</strong> Link guests to customer accounts for credit sales</li>
                        <li><strong>Select Guest:</strong> Click on a guest card to select them for the order</li>
                        <li><strong>Add Items:</strong> Browse products and add to cart</li>
                        <li><strong>Complete Sale:</strong> Process payment for that guest</li>
                        <li><strong>Repeat:</strong> Select another guest and repeat for their orders</li>
                    </ol>
                    <p class="mb-0 mt-2"><strong>Tip:</strong> Each guest must be selected separately to create individual bills.</p>
                </div>

                <!-- Table/Guest Info Display in Cart -->
                <div id="order-info-display" class="alert alert-info mb-4" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="fe fe-grid me-1"></i>Table:</strong> <span id="order-table-name"></span><br>
                            <strong><i class="fe fe-user me-1"></i>Guest:</strong> <span id="order-guest-name"></span>
                            <br><small class="text-muted"><i class="fe fe-info me-1"></i>Items will be assigned to this guest</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearTableSelection()">
                            <i class="fe fe-x"></i> Clear
                        </button>
                    </div>
                </div>

                <div class="px-3 mb-4">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-semibold">Subtotal:</span>
                        <span id="subtotal" class="fw-semibold">₦0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-semibold">Discount:</span>
                        <input type="number" id="discount" class="form-control form-control-sm" style="width: 120px; display: inline-block;" value="0" min="0" onchange="updateTotals()" oninput="updateTotals()">
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="vat-toggle" onchange="updateTotals()">
                            <label class="form-check-label fw-semibold" for="vat-toggle">Apply VAT (7.5%)</label>
                        </div>
                        <span id="vat-amount" class="fw-semibold">₦0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-4 pt-3 border-top">
                        <span class="fw-bold fs-5">Total:</span>
                        <span class="fw-bold fs-5 text-primary" id="total">₦0.00</span>
                    </div>
                </div>

                <div class="px-3 mb-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Method</label>
                        <select id="payment-method" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="transfer">Bank Transfer</option>
                            <option value="pos">POS</option>
                            <option value="credit">Credit (Customer Account)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="amount-paid-group">
                        <label class="form-label fw-semibold">Amount Received</label>
                        <input type="text" id="amount-paid" class="form-control form-control-lg" value="" placeholder="Enter amount received (click to auto-fill total)" onkeyup="formatAmountInput(this); calculateChange()" oninput="formatAmountInput(this); calculateChange()" onclick="autoFillAmount()">
                        <div class="mt-2">
                            <small class="text-muted">Entered: <span id="amount-paid-display" class="fw-bold">₦0.00</span></small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-3 pt-2 border-top" id="change-group">
                        <span class="fw-bold">Change:</span>
                        <span id="change" class="text-success fw-bold fs-5">₦0.00</span>
                    </div>
                </div>

                <button class="btn btn-outline-info btn-lg w-100 mb-2" id="print-order-btn" onclick="printOrderPreview()" disabled>
                    <i class="fe fe-printer me-2"></i> Print Order Preview
                </button>

                <button type="button" class="btn btn-primary btn-lg w-100" id="checkout-btn" onclick="checkout()">
                    <i class="fe fe-check-circle me-2"></i> Complete Sale
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sale Complete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="receipt-content">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="close-order-btn" onclick="closeGuestOrder()" style="display: none;">
                    <i class="fe fe-check-circle me-2"></i> Close Order
                </button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="fe fe-printer me-2"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Create Table Modal -->
<div class="modal fade" id="quickCreateTableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Create Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Table Number <span class="text-danger">*</span></label>
                    <input type="text" id="quick-table-number" class="form-control" placeholder="e.g., T1, T2, VIP-1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Table Name (Optional)</label>
                    <input type="text" id="quick-table-name" class="form-control" placeholder="e.g., Window Table">
                </div>
                <div class="mb-3">
                    <label class="form-label">Capacity</label>
                    <input type="number" id="quick-table-capacity" class="form-control" value="4" min="1" max="50">
                </div>
                <div class="mb-3">
                    <label class="form-label">Location (Optional)</label>
                    <input type="text" id="quick-table-location" class="form-control" placeholder="e.g., Indoor, Outdoor">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="quickCreateTable()">Create & Select</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Guest Modal -->
<div class="modal fade" id="addGuestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Guest to Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Guest Name <span class="text-danger">*</span></label>
                    <input type="text" id="guest-name-input" class="form-control" placeholder="e.g., Table 1 Seat 1" required>
                    <small class="text-muted d-block mt-2">
                        <i class="fe fe-info me-1"></i>
                        <strong>Auto-format:</strong> Leave blank or edit the pre-filled "Table X Seat Y" format
                    </small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Link to Customer Account (Optional)</label>
                    <select id="guest-customer-select" class="form-select">
                        <option value="">No Customer Account</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Link this guest to a customer account for credit sales</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitAddGuest()">Add Guest</button>
            </div>
        </div>
    </div>
</div>

<!-- Manage Customer Modal -->
<div class="modal fade" id="manageCustomerModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manage-customer-title">Manage Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="manage-customer-id">
                <div class="mb-3">
                    <label class="form-label">Credit Limit</label>
                    <div class="input-group">
                        <span class="input-group-text">₦</span>
                        <input type="text" class="form-control" id="manage-credit-limit" placeholder="0.00" oninput="formatAmountInput(this)">
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="manage-credit-enabled">
                        <label class="form-check-label" for="manage-credit-enabled">Active Credit Purchases</label>
                    </div>
                </div>
                <div id="manage-customer-msg" class="alert d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="save-customer-btn" onclick="saveCustomerCredit()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let cart = [];
let currentGuestId = null; // Track currently selected guest ID
let guestData = {}; // Store guest data including customer_id
let activeGuestOrders = {}; // Store multiple guest orders: {guestId: {cart: [], guestName: '', tableId: '', customerId: null}}

function handleProductClick(element) {
    try {
        // Check if table is selected - if so, ensure a guest is selected
        const tableId = document.getElementById('table-select').value;
        let guestId = currentGuestId || document.getElementById('guest-select').value;
        
        if (tableId && !guestId) {
            // Try to auto-select first guest if available
            const guestSelect = document.getElementById('guest-select');
            const guestsList = document.getElementById('guests-list');
            
            // Check if there are guest cards
            const guestCards = guestsList ? guestsList.querySelectorAll('.guest-card') : [];
            
            if (guestCards.length > 0) {
                // Auto-select the first guest card
                const firstGuestCard = guestCards[0];
                const firstGuestId = firstGuestCard.getAttribute('data-guest-id');
                if (firstGuestId) {
                    firstGuestCard.click();
                    guestId = parseInt(firstGuestId);
                    currentGuestId = guestId;
                    // Wait a bit for guest to be activated, then continue
                    setTimeout(() => {
                        continueProductAdd(element);
                    }, 300);
                    return;
                }
            } else if (guestSelect && guestSelect.options.length > 1) {
                // Try to select from dropdown
                const firstGuestOption = guestSelect.options[1];
                if (firstGuestOption && firstGuestOption.value) {
                    guestSelect.value = firstGuestOption.value;
                    guestId = parseInt(firstGuestOption.value);
                    currentGuestId = guestId;
                    // Trigger change event
                    guestSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    setTimeout(() => {
                        continueProductAdd(element);
                    }, 300);
                    return;
                }
            }
            
            // If still no guest, auto-create one
            if (tableId && !guestId) {
                // Auto-create a default guest
                const defaultGuestName = 'Guest ' + new Date().getTime().toString().slice(-4);
                addGuestToTable(tableId, defaultGuestName, null, true).then((data) => {
                    // After guest is created, select it and add the product
                    if (data.guest && data.guest.id) {
                        setTimeout(() => {
                            const guestCard = document.querySelector(`[data-guest-id="${data.guest.id}"]`);
                            if (guestCard) {
                                guestCard.click();
                                setTimeout(() => {
                                    continueProductAdd(element);
                                }, 500);
                            } else {
                                continueProductAdd(element);
                            }
                        }, 500);
                    } else {
                        continueProductAdd(element);
                    }
                }).catch(() => {
                    alert('Please add a guest to this table first. Click "Add New Guest" to create one.');
                });
                return;
            }
        }
        
        continueProductAdd(element);
    } catch (error) {
        console.error('Error handling product click:', error);
        alert('An error occurred during product selection: ' + (error.message || 'Please try again.'));
    }
}

function continueProductAdd(element) {
    try {
        // Check if product is out of stock
        if (element.classList.contains('out-of-stock')) {
            alert('This product is out of stock!');
            return;
        }
        
        // Get product data from data attributes
        const productId = element.getAttribute('data-product-id');
        const productName = element.getAttribute('data-product-name');
        const productPrice = element.getAttribute('data-product-price');
        const productStock = element.getAttribute('data-product-stock');
        
        if (!productId || !productName || !productPrice) {
            console.error('Missing product data:', { productId, productName, productPrice });
            alert('Error: Product data is missing. Please refresh the page and try again.');
            return;
        }
        
        const product = {
            id: parseInt(productId),
            name: productName,
            selling_price: parseFloat(productPrice),
            stock_quantity: parseInt(productStock) || 0
        };
        
        if (isNaN(product.id) || isNaN(product.selling_price)) {
            console.error('Invalid product data:', product);
            alert('Error: Invalid product data. Please refresh the page and try again.');
            return;
        }
        
        addToCart(product);
    } catch (error) {
        console.error('Error handling product click:', error);
        alert('An error occurred during product processing: ' + (error.message || 'Please try again.'));
    }
}

function addToCart(product) {
    try {
        console.log('addToCart called - currentGuestId:', currentGuestId, 'product:', product.name);
        
        if (!product || !product.id) {
            console.error('Invalid product data:', product);
            alert('Error: Invalid product data. Please try again.');
            return;
        }
        
        // Ensure cart is initialized
        if (!Array.isArray(cart)) {
            cart = [];
        }
        
        // If no currentGuestId but we have a table selected, try to get guest
        if (!currentGuestId) {
            const tableId = document.getElementById('table-select').value;
            const guestSelect = document.getElementById('guest-select');
            const guestId = guestSelect ? guestSelect.value : null;
            
            if (tableId && guestId) {
                console.log('Setting currentGuestId from dropdown:', guestId);
                currentGuestId = parseInt(guestId);
                // Ensure activeGuestOrders exists
                if (!activeGuestOrders[currentGuestId]) {
                    const guestName = guestSelect.options[guestSelect.selectedIndex]?.text || 'Guest';
                    activeGuestOrders[currentGuestId] = {
                        cart: JSON.parse(JSON.stringify(cart)),
                        guestName: guestName,
                        tableId: tableId,
                        customerId: null,
                        discount: 0
                    };
                }
            }
        }
        
        const existingItem = cart.find(item => item.product_id === product.id);
        
        if (existingItem) {
            if (existingItem.quantity < product.stock_quantity) {
                existingItem.quantity++;
            } else {
                alert('Not enough stock available! Available: ' + product.stock_quantity);
                return;
            }
        } else {
            if (product.stock_quantity <= 0) {
                alert('This product is out of stock!');
                return;
            }
            
            cart.push({
                product_id: product.id,
                name: product.name,
                unit_price: parseFloat(product.selling_price),
                quantity: 1,
                max_stock: product.stock_quantity,
                discount: 0
            });
        }
        
        renderCart();
        
        // Update active guest order - CRITICAL: Always update when adding items
        if (currentGuestId) {
            // Ensure guest order exists
            if (!activeGuestOrders[currentGuestId]) {
                const tableId = document.getElementById('table-select').value;
                const guestSelect = document.getElementById('guest-select');
                const guestName = guestSelect ? guestSelect.options[guestSelect.selectedIndex]?.text : 'Guest';
                activeGuestOrders[currentGuestId] = {
                    cart: [],
                    guestName: guestName,
                    tableId: tableId,
                    customerId: null,
                    discount: 0
                };
                createGuestTab(currentGuestId, guestName);
            }
            
            try {
                // Update the cart in activeGuestOrders
                activeGuestOrders[currentGuestId].cart = JSON.parse(JSON.stringify(cart));
                if (typeof updateGuestTabBadge === 'function') {
                    updateGuestTabBadge(currentGuestId);
                }
                console.log('Updated guest order for:', currentGuestId, 'Items:', cart.length);
            } catch (e) {
                console.error('Error updating guest order:', e);
            }
        }
        
        // Auto-save immediately to database (no debounce) - ensures orders persist after refresh
        if (currentGuestId) {
            const tableId = document.getElementById('table-select').value || (activeGuestOrders[currentGuestId] ? activeGuestOrders[currentGuestId].tableId : null);
            if (tableId && currentGuestId) {
                // Save immediately, even if cart is empty (to clear pending order if needed)
                savePendingOrder(tableId, currentGuestId, false).then(() => {
                    console.log('Cart auto-saved for guest:', currentGuestId, 'Items:', cart.length);
                }).catch(error => {
                    console.error('Error auto-saving cart:', error);
                });
            } else {
                console.warn('Cannot auto-save: tableId =', tableId, 'guestId =', currentGuestId);
            }
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        console.error('Error details:', error.stack);
        alert('An error occurred while adding product to cart. Please try again.\n\nError: ' + error.message);
    }
}

// Auto-save pending order
function autoSavePendingOrder() {
    const tableId = document.getElementById('table-select').value;
    const guestId = currentGuestId || document.getElementById('guest-select').value || document.getElementById('guest-select').getAttribute('data-selected-guest-id');
    
    // Only auto-save if table and guest are selected and cart has items
    if (tableId && guestId && cart.length > 0) {
        // Debounce: save after 1 second of no changes
        clearTimeout(window.autoSaveTimeout);
        window.autoSaveTimeout = setTimeout(() => {
            savePendingOrder(tableId, guestId);
        }, 1000);
    }
}

// Load and display pending orders for a table
function loadPendingOrdersForTable(tableId) {
    if (!tableId) {
        const pendingSection = document.getElementById('pending-orders-section');
        if (pendingSection) {
            pendingSection.style.display = 'none';
        }
        return;
    }
    
    fetch(`{{ route("admin.pos.get-pending") }}?table_id=${tableId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        const pendingSection = document.getElementById('pending-orders-section');
        const pendingList = document.getElementById('pending-orders-list');
        const pendingCount = document.getElementById('pending-orders-count');
        
        if (!pendingSection || !pendingList || !pendingCount) return;
        
        if (data.success && data.orders && data.orders.length > 0) {
            let html = '';
            data.orders.forEach(order => {
                const orderDate = new Date(order.created_at).toLocaleString();
                const guestName = order.guest_name || 'All Guests';
                html += `
                    <div class="pending-order-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong>${guestName}</strong>
                                <br><small class="order-date">${orderDate}</small>
                            </div>
                            <div class="text-end">
                                <strong class="text-primary">₦${formatCurrency(order.total)}</strong>
                                <br><small class="text-muted">${order.items_count} item(s)</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary flex-fill" onclick="loadPendingOrderToCart(${order.id}, ${order.table_guest_id || 'null'})">
                                <i class="fe fe-edit me-1"></i> Load & Edit
                            </button>
                            <button class="btn btn-sm btn-outline-info flex-fill" onclick="printPendingOrder(${order.id})">
                                <i class="fe fe-printer me-1"></i> Print
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePendingOrder(${order.id})" title="Delete this pending order">
                                <i class="fe fe-trash-2"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            
            pendingList.innerHTML = html;
            pendingCount.textContent = data.orders.length;
            pendingSection.style.display = 'block';
        } else {
            pendingSection.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error loading pending orders:', error);
        const pendingSection = document.getElementById('pending-orders-section');
        if (pendingSection) {
            pendingSection.style.display = 'none';
        }
    });
}

// Load a pending order into the cart for editing
function loadPendingOrderToCart(orderId, guestId) {
    if (!guestId || guestId === 'null') {
        alert('Please select the guest first before loading this order.');
        return;
    }
    
    fetch(`{{ route("admin.pos.get-pending") }}?sale_id=${orderId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.items && data.items.length > 0) {
            // Ensure all numeric values are properly converted
            const cartItems = data.items.map(item => ({
                product_id: item.product_id,
                name: item.name,
                unit_price: parseFloat(item.unit_price) || 0,
                quantity: parseInt(item.quantity) || 0,
                discount: parseFloat(item.discount) || 0,
                max_stock: 9999
            }));
            
            // Load items into cart
            cart = JSON.parse(JSON.stringify(cartItems));
            
            // Set discount
            if (data.sale && data.sale.discount) {
                const discountInput = document.getElementById('discount');
                if (discountInput) {
                    discountInput.value = data.sale.discount;
                }
            }
            
            // Set customer
            if (data.sale && data.sale.customer_id) {
                const customerSelect = document.getElementById('customer-select');
                if (customerSelect) {
                    customerSelect.value = data.sale.customer_id;
                }
            }
            
            // CRITICAL: Set currentGuestId BEFORE updating activeGuestOrders
            currentGuestId = guestId;
            
            // Update or create active guest order
            if (!activeGuestOrders[guestId]) {
                activeGuestOrders[guestId] = {
                    cart: JSON.parse(JSON.stringify(cartItems)),
                    guestName: data.sale?.guest_name || 'Guest',
                    tableId: data.sale?.table_id || document.getElementById('table-select').value,
                    customerId: data.sale?.customer_id || null,
                    discount: data.sale?.discount || 0
                };
            } else {
                activeGuestOrders[guestId].cart = JSON.parse(JSON.stringify(cartItems));
                activeGuestOrders[guestId].discount = data.sale?.discount || 0;
                activeGuestOrders[guestId].customerId = data.sale?.customer_id || null;
            }
            
            // Set guest select value
            const guestSelect = document.getElementById('guest-select');
            if (guestSelect) {
                guestSelect.value = guestId;
                guestSelect.setAttribute('data-selected-guest-id', guestId);
            }
            
            // Highlight the guest card if it exists
            const guestCard = document.querySelector(`[data-guest-id="${guestId}"]`);
            if (guestCard) {
                // Remove active class from all cards
                document.querySelectorAll('.guest-card').forEach(card => {
                    card.classList.remove('border-primary', 'bg-primary-transparent');
                });
                guestCard.classList.add('border-primary', 'bg-primary-transparent');
            }
            
            // Show selected guest indicator
            const indicator = document.getElementById('selected-guest-indicator');
            const guestNameSpan = document.getElementById('selected-guest-name');
            if (indicator && guestNameSpan) {
                guestNameSpan.textContent = data.sale?.guest_name || 'Guest';
                indicator.style.display = 'block';
            }
            
            // Update cart header
            updateCartHeader();
            
            // Remove empty message
            const emptyMsg = document.getElementById('empty-cart-message');
            if (emptyMsg) {
                emptyMsg.remove();
            }
            
            // Render cart and update totals
            renderCart();
            updateTotals();
            
            // Switch to this guest (this will also ensure everything is properly set up)
            switchToGuest(guestId, data.sale?.guest_name || 'Guest');
            
            console.log('Order loaded - currentGuestId:', currentGuestId, 'cart.length:', cart.length);
            
            alert('Order loaded successfully! You can now edit or complete the sale.');
        } else {
            alert('Order loaded but no items found.');
        }
    })
    .catch(error => {
        console.error('Error loading order:', error);
        alert('Error loading order. Please try again.');
    });
}

// Print a pending order
function printPendingOrder(orderId) {
    fetch(`{{ route("admin.pos.get-pending") }}?sale_id=${orderId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.sale) {
            printPendingOrderReceipt(data.sale, data.items);
        } else {
            alert('Order not found.');
        }
    })
    .catch(error => {
        console.error('Error loading order for print:', error);
        alert('Error loading order. Please try again.');
    });
}

// Delete a single pending order
function deletePendingOrder(orderId) {
    if (!confirm('Are you sure you want to delete this pending order? This cannot be undone.')) {
        return;
    }
    
    fetch(`{{ route("admin.pos.delete-pending", ":id") }}`.replace(':id', orderId), {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload pending orders for the current table
            const tableId = document.getElementById('table-select').value;
            if (tableId) {
                loadPendingOrdersForTable(tableId);
            }
            
            // If this was the current guest's order, clear the cart
            const guestSelect = document.getElementById('guest-select');
            if (guestSelect && guestSelect.value) {
                const currentGuestId = guestSelect.value;
                // Check if this order belonged to current guest
                // If so, clear their cart and activeGuestOrders
                if (activeGuestOrders[currentGuestId]) {
                    activeGuestOrders[currentGuestId].cart = [];
                    if (currentGuestId === document.getElementById('guest-select').value) {
                        cart = [];
                        renderCart();
                        updateTotals();
                    }
                    updateGuestTabBadge(currentGuestId);
                }
            }
        } else {
            alert('Error: ' + (data.message || 'Failed to delete pending order'));
        }
    })
    .catch(error => {
        console.error('Error deleting pending order:', error);
        alert('An error occurred while deleting the pending order.');
    });
}

// Print pending order receipt
function printPendingOrderReceipt(sale, items) {
    const subtotal = sale.subtotal || 0;
    const discount = sale.discount || 0;
    const vat = sale.tax || 0;
    const total = sale.total || 0;
    
    const printWindow = window.open('', '_blank');
    
    // Check if popup was blocked
    if (!printWindow || printWindow.closed || typeof printWindow.closed === 'undefined') {
        // Popup blocked - create a print-friendly div in current window instead
        const printContent = `
            <div id="print-receipt-content" style="display: none;">
                <div style="font-family: Arial, sans-serif; padding: 20px; max-width: 400px; margin: 0 auto;">
                    <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px;">
                        <img src="{{ asset('logo.jpg') }}" alt="Optizee Hotel and Suites" style="max-width: 150px; max-height: 80px; margin-bottom: 15px;">
                        <h2>PENDING ORDER</h2>
                        <p><strong>Invoice:</strong> ${sale.invoice_number || sale.id}</p>
                        <span style="background: #ffc107; color: #000; padding: 5px 10px; border-radius: 4px; display: inline-block; margin: 10px 0;">PENDING PAYMENT</span>
                    </div>
                    <div style="margin: 10px 0;">
                        <p><strong>Date:</strong> ${sale.created_at ? new Date(sale.created_at).toLocaleString() : new Date().toLocaleString()}</p>
                        ${sale.guest_name ? `<p><strong>Guest:</strong> ${sale.guest_name}</p>` : ''}
                        ${sale.customer_name ? `<p><strong>Customer:</strong> ${sale.customer_name}</p>` : ''}
                    </div>
                    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                        <thead>
                            <tr>
                                <th style="padding: 8px; text-align: left; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Item</th>
                                <th style="padding: 8px; text-align: left; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Qty</th>
                                <th style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Price</th>
                                <th style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd; background-color: #f2f2f2;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${items.map(item => `
                                <tr>
                                    <td style="padding: 8px; text-align: left; border-bottom: 1px solid #ddd;">${item.name}</td>
                                    <td style="padding: 8px; text-align: left; border-bottom: 1px solid #ddd;">${item.quantity}</td>
                                    <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">₦${formatCurrency(item.unit_price)}</td>
                                    <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">₦${formatCurrency((item.unit_price * item.quantity) - (item.discount || 0))}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    <div style="text-align: right; margin-top: 20px;">
                        <p>Subtotal: ₦${formatCurrency(subtotal)}</p>
                        ${discount > 0 ? `<p>Discount: ₦${formatCurrency(discount)}</p>` : ''}
                        <p style="font-size: 18px; font-weight: bold; color: #5e72e4;">Total: ₦${formatCurrency(total)}</p>
                    </div>
                    <div style="text-align: center; margin-top: 30px; color: #856404; padding: 15px; background: #fff9e6; border-radius: 4px;">
                        <p><strong>This is a pending order.</strong></p>
                        <p>Payment not yet received.</p>
                    </div>
                    <div style="text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd;">
                        <p style="margin-bottom: 5px;"><strong>Bank Transfer Details:</strong></p>
                        <p style="margin-bottom: 2px;"><strong>Bank:</strong> MONIEPOINT</p>
                        <p style="margin-bottom: 2px;"><strong>Account Number:</strong> 5686138899</p>
                        <p style="margin-bottom: 0;"><strong>Account Name:</strong> SUNNY AKHAMIORKHOR</p>
                    </div>
                </div>
            </div>
        `;
        
        // Create and show print content
        let printDiv = document.getElementById('print-receipt-content');
        if (!printDiv) {
            printDiv = document.createElement('div');
            printDiv.id = 'print-receipt-content';
            document.body.appendChild(printDiv);
        }
        printDiv.innerHTML = printContent;
        printDiv.style.display = 'block';
        
        // Print
        window.print();
        
        // Remove after printing
        setTimeout(() => {
            printDiv.style.display = 'none';
        }, 1000);
        
        return;
    }
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Pending Order - ${sale.invoice_number}</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; max-width: 400px; margin: 0 auto; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                .header img { max-width: 150px; max-height: 80px; height: auto; margin-bottom: 15px; }
                .info { margin: 10px 0; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background-color: #f2f2f2; }
                .total { font-size: 18px; font-weight: bold; color: #5e72e4; }
                .pending-badge { background: #ffc107; color: #000; padding: 5px 10px; border-radius: 4px; display: inline-block; margin: 10px 0; }
                .text-right { text-align: right; }
                @media print { body { margin: 0; padding: 10px; } }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="{{ asset('logo.jpg') }}" alt="Optizee Hotel and Suites" style="max-width: 150px; max-height: 80px; margin-bottom: 15px;">
                <h2>PENDING ORDER</h2>
                <p><strong>Invoice:</strong> ${sale.invoice_number}</p>
                <span class="pending-badge">PENDING PAYMENT</span>
            </div>
            <div class="info">
                <p><strong>Date:</strong> ${new Date(sale.created_at).toLocaleString()}</p>
                ${sale.guest_name ? `<p><strong>Guest:</strong> ${sale.guest_name}</p>` : ''}
                ${sale.customer_name ? `<p><strong>Customer:</strong> ${sale.customer_name}</p>` : ''}
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${items.map(item => `
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.quantity}</td>
                            <td class="text-right">₦${formatCurrency(item.unit_price)}</td>
                            <td class="text-right">₦${formatCurrency((item.unit_price * item.quantity) - (item.discount || 0))}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
            <div style="text-align: right; margin-top: 20px;">
                <p>Subtotal: ₦${formatCurrency(subtotal)}</p>
                ${discount > 0 ? `<p>Discount: ₦${formatCurrency(discount)}</p>` : ''}
                <p class="total">Total: ₦${formatCurrency(total)}</p>
            </div>
            <div style="text-align: center; margin-top: 30px; color: #856404; padding: 15px; background: #fff9e6; border-radius: 4px;">
                <p><strong>This is a pending order.</strong></p>
                <p>Payment not yet received.</p>
            </div>
            <div style="text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd;">
                <p style="margin-bottom: 5px;"><strong>Bank Transfer Details:</strong></p>
                <p style="margin-bottom: 2px;"><strong>Bank:</strong> MONIEPOINT</p>
                <p style="margin-bottom: 2px;"><strong>Account Number:</strong> 5686138899</p>
                <p style="margin-bottom: 0;"><strong>Account Name:</strong> SUNNY AKHAMIORKHOR</p>
            </div>
        </body>
        </html>
    `);
    // Check if window is still available before closing document
    if (printWindow && printWindow.document) {
        printWindow.document.close();
        
        // Wait for content to load before printing
        printWindow.onload = function() {
            setTimeout(function() {
                if (printWindow && !printWindow.closed) {
                    printWindow.print();
                }
            }, 250);
        };
        
        // Fallback if onload doesn't fire
        setTimeout(function() {
            if (printWindow && !printWindow.closed && printWindow.document && printWindow.document.readyState === 'complete') {
                printWindow.print();
            }
        }, 500);
    } else {
        // Popup was blocked, show alert
        alert('Popup was blocked. Please allow popups for this site and try again, or use the Print button in the cart.');
    }
}

// Get customer ID from guest data
function getGuestCustomerId(guestId) {
    if (!guestId) return null;
    // First check activeGuestOrders
    if (activeGuestOrders && activeGuestOrders[guestId] && activeGuestOrders[guestId].customerId) {
        return activeGuestOrders[guestId].customerId;
    }
    // Then check guestData
    if (guestData && guestData[guestId] && guestData[guestId].customer_id) {
        return guestData[guestId].customer_id;
    }
    return null;
}

// Save pending order to database
function savePendingOrder(tableId, guestId, showIndicator = true) {
    // Always save if table and guest are selected, even if cart is empty (to clear pending order)
    if (!tableId || !guestId) {
        console.log('Skipping save - tableId:', tableId, 'guestId:', guestId);
        return Promise.resolve();
    }
    
    // If cart is empty, we still save to clear/update the pending order
    if (cart.length === 0) {
        console.log('Cart is empty, but saving to update pending order for guest:', guestId);
    }
    
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    // Get customer from guest record (stored in guestData) or from dropdown
    const customerId = getGuestCustomerId(guestId) || document.getElementById('customer-select').value || null;
    
    // If customer is selected in dropdown but not in guest, update guest
    if (customerId && !getGuestCustomerId(guestId)) {
        if (typeof updateGuestCustomer === 'function') {
            updateGuestCustomer(guestId, customerId);
        }
    }
    
    // If cart is empty, delete the pending order instead of saving
    if (cart.length === 0) {
        console.log('Cart is empty, deleting pending order for guest:', guestId);
        // Try to find and delete the pending order for this guest
        return fetch(`{{ route("admin.pos.get-pending") }}?table_id=${tableId}&table_guest_id=${guestId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.sale && data.sale.id) {
                // Delete the pending order
                return fetch(`{{ route("admin.pos.delete-pending", ":id") }}`.replace(':id', data.sale.id), {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(deleteData => {
                    if (deleteData.success) {
                        console.log('Pending order deleted for empty cart');
                        // Reload pending orders list
                        loadPendingOrdersForTable(tableId);
                    }
                    return deleteData;
                });
            }
            return Promise.resolve();
        })
        .catch(error => {
            console.error('Error deleting pending order for empty cart:', error);
            return Promise.resolve();
        });
    }
    
    const requestData = {
        items: cart.map(item => ({
            product_id: parseInt(item.product_id),
            quantity: parseInt(item.quantity),
            unit_price: parseFloat(item.unit_price),
            discount: parseFloat(item.discount || 0)
        })),
        table_id: parseInt(tableId),
        table_guest_id: parseInt(guestId),
        customer_id: customerId ? parseInt(customerId) : null,
        discount: discount
    };
    
    console.log('Saving pending order for guest:', guestId, 'Items:', cart.length, requestData);
    
    return fetch('{{ route("admin.pos.save-pending") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('✅ Order auto-saved successfully for guest:', guestId, 'Sale ID:', data.sale_id, 'Items:', cart.length);
            // Show save indicator
            if (showIndicator) {
                const indicator = document.getElementById('auto-save-indicator');
                if (indicator) {
                    indicator.style.display = 'inline-block';
                    setTimeout(() => {
                        indicator.style.display = 'none';
                    }, 2000);
                }
            }
            return data;
        } else {
            console.error('❌ Save failed:', data.message || 'Unknown error');
            throw new Error(data.message || 'Save failed');
        }
    })
    .catch(error => {
        console.error('❌ Error auto-saving order:', error);
        // Don't show alert for auto-save failures (too annoying)
        // But log it for debugging
        return Promise.reject(error);
    });
}

// Save current guest's cart to activeGuestOrders
function saveCurrentGuestCart() {
    if (currentGuestId) {
        const tableId = document.getElementById('table-select').value;
        const customerId = document.getElementById('customer-select').value || null;
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        
        // Get guest name if not already set
        let guestName = '';
        if (activeGuestOrders[currentGuestId]) {
            guestName = activeGuestOrders[currentGuestId].guestName;
        } else {
            const guestSelect = document.getElementById('guest-select');
            if (guestSelect) {
                const option = guestSelect.options[guestSelect.selectedIndex];
                guestName = option ? option.text : 'Guest';
            }
        }
        
        if (!activeGuestOrders[currentGuestId]) {
            activeGuestOrders[currentGuestId] = {
                cart: [],
                guestName: guestName,
                tableId: tableId,
                customerId: customerId,
                discount: discount
            };
            // Create tab if it doesn't exist
            if (guestName) {
                createGuestTab(currentGuestId, guestName);
            }
        }
        
        // Always update the cart, discount, and customer
        activeGuestOrders[currentGuestId].cart = JSON.parse(JSON.stringify(cart));
        activeGuestOrders[currentGuestId].discount = discount;
        activeGuestOrders[currentGuestId].customerId = customerId;
        if (guestName && !activeGuestOrders[currentGuestId].guestName) {
            activeGuestOrders[currentGuestId].guestName = guestName;
        }
        
        // Update tab badge
        if (typeof updateGuestTabBadge === 'function') {
            updateGuestTabBadge(currentGuestId);
        }
        
        console.log('Saved cart for guest:', currentGuestId, guestName, 'Items:', cart.length);
        
        // Auto-save to database
        if (tableId && currentGuestId && cart.length > 0) {
            savePendingOrder(tableId, currentGuestId, false);
        }
    }
}

// Activate or create a guest order
function activateGuestOrder(guestId, guestName, tableId) {
    console.log('Activating guest order:', guestId, guestName, tableId);
    
    // Save current cart before switching
    saveCurrentGuestCart();
    
    // CRITICAL: Clear the cart immediately when switching to a new guest
    // This ensures we start fresh and don't carry over items from previous guest
    cart = [];
    renderCart(); // Update UI immediately to show empty cart
    
    // If this guest doesn't have an active order, create one
    if (!activeGuestOrders[guestId]) {
        console.log('Creating new guest order for:', guestName);
        activeGuestOrders[guestId] = {
            cart: [],
            guestName: guestName,
            tableId: tableId,
            customerId: null,
            discount: 0
        };
        
        // Create tab
        createGuestTab(guestId, guestName);
        
        // Load pending order from database only if cart is empty
        loadPendingOrderForGuest(tableId, guestId).then(() => {
            console.log('Loaded pending order, cart length:', cart.length);
            // After loading, update the activeGuestOrders with loaded cart
            if (cart.length > 0) {
                activeGuestOrders[guestId].cart = JSON.parse(JSON.stringify(cart));
                if (typeof updateGuestTabBadge === 'function') {
                    updateGuestTabBadge(guestId);
                }
            }
            // Switch to this guest
            switchToGuest(guestId, guestName);
        }).catch((error) => {
            console.error('Error loading pending order:', error);
            // Even if loading fails, switch to the guest
            switchToGuest(guestId, guestName);
        });
    } else {
        console.log('Guest order already exists, switching to it');
        // Guest order exists, check if cart is already populated
        const hasCartItems = activeGuestOrders[guestId].cart && activeGuestOrders[guestId].cart.length > 0;
        
        // CRITICAL: Clear cart before loading guest's order to prevent mixing items
        cart = [];
        renderCart(); // Update UI immediately to show empty cart
        
        if (!hasCartItems) {
            // Cart is empty, try to load from database
            loadPendingOrderForGuest(tableId, guestId).then(() => {
                if (cart.length > 0) {
                    activeGuestOrders[guestId].cart = JSON.parse(JSON.stringify(cart));
                }
                switchToGuest(guestId, guestName);
            }).catch(() => {
                switchToGuest(guestId, guestName);
            });
        } else {
            // Cart already has items, just switch to it
            createGuestTab(guestId, guestName); // Ensure tab exists
            switchToGuest(guestId, guestName);
        }
    }
}

// Simple switch to guest (without tab complexity)
function switchToGuest(guestId, guestName) {
    // Ensure this guest has an active order entry
    if (!activeGuestOrders[guestId]) {
        const tableId = document.getElementById('table-select').value;
        activeGuestOrders[guestId] = {
            cart: [],
            guestName: guestName,
            tableId: tableId,
            customerId: null,
            discount: 0
        };
        // Create tab for this guest
        createGuestTab(guestId, guestName);
    }
    
    // CRITICAL: Clear cart first, then load this guest's cart
    // This prevents mixing items from different guests
    cart = [];
    
    // Load this guest's cart
    const order = activeGuestOrders[guestId];
    cart = JSON.parse(JSON.stringify(order.cart || []));
    currentGuestId = guestId;
    
    // Ensure table-select is updated to this guest's table
    if (order.tableId && document.getElementById('table-select').value != order.tableId) {
        document.getElementById('table-select').value = order.tableId;
    }
    
    // If cart is empty, render immediately to show empty state
    if (cart.length === 0) {
        renderCart();
        updateTotals();
    }
    
    // Set discount
    const discountInput = document.getElementById('discount');
    if (discountInput) {
        discountInput.value = order.discount || 0;
    }
    
    // Set customer
    const customerSelect = document.getElementById('customer-select');
    if (customerSelect) {
        let customerIdToSet = order.customerId;
        if (!customerIdToSet) {
            customerIdToSet = getGuestCustomerId(guestId);
        }
        
        if (customerIdToSet) {
            // Set radio to credit and show select
            document.getElementById('category-credit').checked = true;
            document.getElementById('credit-customer-select-container').style.display = 'block';
            
            // Set select value
            customerSelect.value = customerIdToSet;
            $(customerSelect).val(customerIdToSet).trigger('change.select2');
        } else {
            // Set radio to walk-in and hide select
            document.getElementById('category-walkin').checked = true;
            document.getElementById('credit-customer-select-container').style.display = 'none';
            
            // Clear select value
            customerSelect.value = '';
            $(customerSelect).val('').trigger('change.select2');
        }
    }
    
    // CRITICAL: Always render cart after switching guests
    renderCart();
    updateTotals();
    updateCartHeader();
    
    // Update guest select
    const guestSelect = document.getElementById('guest-select');
    if (guestSelect) {
        guestSelect.value = guestId;
        guestSelect.setAttribute('data-selected-guest-id', guestId);
    }
    
    // Render cart and update totals
    renderCart();
    updateTotals();
    
    // Update guest card highlighting
    document.querySelectorAll('.guest-card').forEach(card => {
        card.classList.remove('border-primary', 'bg-primary-transparent');
    });
    const clickedCard = document.querySelector(`[data-guest-id="${guestId}"]`);
    if (clickedCard) {
        clickedCard.classList.add('border-primary', 'bg-primary-transparent');
    }
    
    // Show selected guest indicator
    const indicator = document.getElementById('selected-guest-indicator');
    const guestNameSpan = document.getElementById('selected-guest-name');
    if (indicator && guestNameSpan) {
        guestNameSpan.textContent = guestName;
        indicator.style.display = 'block';
    }
    
    // Render cart - THIS IS CRITICAL
    renderCart();
    updateTotals();
    
    // Update UI
    updateCartHeader();
    updateOrderInfoDisplay();
    
    // Activate tab if it exists
    const tabLink = document.getElementById(`tab-${guestId}`);
    if (tabLink) {
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        tabLink.classList.add('active');
    }
    
    console.log('Switched to guest:', guestName, 'Cart items:', cart.length);
}

// Create a tab for a guest order
function createGuestTab(guestId, guestName) {
    const tabsContainer = document.getElementById('guest-orders-tabs');
    if (!tabsContainer) return;
    
    // Check if tab already exists
    if (document.getElementById(`tab-${guestId}`)) {
        return;
    }
    
    // Create tab button
    const tabButton = document.createElement('li');
    tabButton.className = 'nav-item';
    tabButton.innerHTML = `
        <a class="nav-link" id="tab-${guestId}" data-bs-toggle="tab" 
           href="#content-${guestId}" role="tab" onclick="switchToGuestTab('${guestId}'); return false;">
            ${guestName}
            <span class="guest-tab-badge" id="badge-${guestId}">0</span>
            <span class="guest-tab-close" onclick="closeGuestOrder('${guestId}', event); return false;">×</span>
        </a>
    `;
    tabsContainer.appendChild(tabButton);
    
    // Show tabs container
    const tabsSection = document.getElementById('active-orders-tabs');
    if (tabsSection) {
        tabsSection.style.display = 'block';
    }
    
    // Update badge
    updateGuestTabBadge(guestId);
}

// Switch to a guest's tab
function switchToGuestTab(guestId) {
    // Save current cart
    saveCurrentGuestCart();
    
    // CRITICAL: Clear cart first to prevent mixing items from different guests
    cart = [];
    
    // Load this guest's cart
    if (activeGuestOrders[guestId]) {
        const order = activeGuestOrders[guestId];
        cart = JSON.parse(JSON.stringify(order.cart || []));
        currentGuestId = guestId;
        
        // If cart is empty, render immediately to show empty state
        if (cart.length === 0) {
            renderCart();
            updateTotals();
        }
        
        // Set discount
        const discountInput = document.getElementById('discount');
        if (discountInput) {
            discountInput.value = order.discount || 0;
        }
        
        // Set customer
        const customerSelect = document.getElementById('customer-select');
        if (customerSelect && order.customerId) {
            customerSelect.value = order.customerId;
        }
        
        // Update guest select
        const guestSelect = document.getElementById('guest-select');
        if (guestSelect) {
            guestSelect.value = guestId;
            guestSelect.setAttribute('data-selected-guest-id', guestId);
        }
        
        // Activate tab visually
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
        
        const tabLink = document.getElementById(`tab-${guestId}`);
        if (tabLink) {
            tabLink.classList.add('active');
        }
        
        // Render cart
        renderCart();
        updateTotals();
        
        // Update UI
        updateCartHeader();
        updateOrderInfoDisplay();
        
        console.log('Switched to guest:', activeGuestOrders[guestId].guestName);
    }
}

// Update tab badge with item count
function updateGuestTabBadge(guestId) {
    const badge = document.getElementById(`badge-${guestId}`);
    if (badge && activeGuestOrders[guestId]) {
        const itemCount = activeGuestOrders[guestId].cart.reduce((sum, item) => sum + item.quantity, 0);
        badge.textContent = itemCount;
        badge.style.display = itemCount > 0 ? 'inline-block' : 'none';
    }
}

// Close a guest order
function closeGuestOrder(guestId, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    
    if (confirm(`Close order for ${activeGuestOrders[guestId]?.guestName}? The cart will be saved but the tab will be removed.`)) {
        // Save before closing
        if (currentGuestId === guestId) {
            saveCurrentGuestCart();
        }
        
        // Remove from active orders
        delete activeGuestOrders[guestId];
        
        // Remove tab
        const tabButton = document.getElementById(`tab-${guestId}`)?.closest('li');
        if (tabButton) tabButton.remove();
        
        // If this was the active tab, switch to another or clear
        if (currentGuestId === guestId) {
            const remainingGuests = Object.keys(activeGuestOrders);
            if (remainingGuests.length > 0) {
                switchToGuestTab(remainingGuests[0]);
            } else {
                cart = [];
                currentGuestId = null;
                renderCart();
                const tabsSection = document.getElementById('active-orders-tabs');
                if (tabsSection) {
                    tabsSection.style.display = 'none';
                }
            }
        }
        
        // Hide tabs if no active orders
        if (Object.keys(activeGuestOrders).length === 0) {
            const tabsSection = document.getElementById('active-orders-tabs');
            if (tabsSection) {
                tabsSection.style.display = 'none';
            }
        }
    }
}

// Load pending order for a guest (returns Promise)
function loadPendingOrderForGuest(tableId, guestId) {
    return new Promise((resolve, reject) => {
        if (!tableId || !guestId) {
            resolve();
            return;
        }
        
        // CRITICAL: Clear cart immediately when loading a new guest's order
        // This prevents previous guest's items from being carried over
        cart = [];
        currentGuestId = guestId;
        
        // Update UI immediately to show empty cart
        const container = document.getElementById('cart-items');
        if (container) {
            container.innerHTML = '<p class="text-center text-muted py-5" id="empty-cart-message">Loading guest order...</p>';
        }
        
        // First, get guest data to get customer_id
        fetch(`/admin/tables/${tableId}/guests`)
            .then(response => response.json())
            .then(guestsData => {
                const guest = guestsData.guests.find(g => g.id == guestId);
                if (guest) {
                    // Store guest data including customer
                    guestData[guestId] = {
                        customer_id: guest.customer?.id || null
                    };
                    
                    // Set customer in dropdown if guest has one
                    if (guest.customer?.id) {
                        const customerSelect = document.getElementById('customer-select');
                        if (customerSelect) {
                            customerSelect.value = guest.customer.id;
                        }
                    }
                }
                
                // Now load pending order
                return fetch(`{{ route("admin.pos.get-pending") }}?table_id=${tableId}&table_guest_id=${guestId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.items && data.items.length > 0) {
                    // Load items into cart
                    cart = data.items.map(item => ({
                        product_id: item.product_id,
                        name: item.name,
                        unit_price: item.unit_price,
                        quantity: item.quantity,
                        discount: item.discount || 0,
                        max_stock: 9999
                    }));
                    
                    // Update activeGuestOrders with loaded cart
                    if (activeGuestOrders[guestId]) {
                        activeGuestOrders[guestId].cart = JSON.parse(JSON.stringify(cart));
                    }
                    
                    // Set discount if available
                    if (data.sale && data.sale.discount) {
                        const discountInput = document.getElementById('discount');
                        if (discountInput) {
                            discountInput.value = data.sale.discount;
                        }
                        if (activeGuestOrders[guestId]) {
                            activeGuestOrders[guestId].discount = data.sale.discount;
                        }
                    }
                    
                    // Set customer if available
                    if (data.sale && data.sale.customer_id) {
                        const customerSelect = document.getElementById('customer-select');
                        if (customerSelect) {
                            customerSelect.value = data.sale.customer_id;
                        }
                        if (activeGuestOrders[guestId]) {
                            activeGuestOrders[guestId].customerId = data.sale.customer_id;
                        }
                    }
                    
                    // CRITICAL: Set currentGuestId BEFORE rendering so new items can be added
                    currentGuestId = guestId;
                    
                    // Ensure activeGuestOrders is updated
                    if (!activeGuestOrders[guestId]) {
                        activeGuestOrders[guestId] = {
                            cart: JSON.parse(JSON.stringify(cart)),
                            guestName: '',
                            tableId: tableId,
                            customerId: data.sale?.customer_id || null,
                            discount: data.sale?.discount || 0
                        };
                    } else {
                        activeGuestOrders[guestId].cart = JSON.parse(JSON.stringify(cart));
                    }
                    
                    renderCart();
                    updateTotals();
                    console.log('Loaded pending order for guest:', guestId, 'Items:', cart.length, 'currentGuestId set to:', currentGuestId);
                } else {
                    // No pending order, start fresh
                    currentGuestId = guestId;
                    cart = [];
                    // Ensure activeGuestOrders has this guest
                    if (!activeGuestOrders[guestId]) {
                        activeGuestOrders[guestId] = {
                            cart: [],
                            guestName: '',
                            tableId: tableId,
                            customerId: null,
                            discount: 0
                        };
                    } else {
                        activeGuestOrders[guestId].cart = [];
                    }
                    // CRITICAL: Render empty cart immediately
                    renderCart();
                    updateTotals();
                    console.log('No pending order found for guest:', guestId, 'Starting fresh. currentGuestId:', currentGuestId);
                }
                resolve();
            })
            .catch(error => {
                console.error('Error loading pending order:', error);
                resolve(); // Resolve anyway so tab can be created
            });
    });
}


function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
    
    // Update active guest order - ensure it exists
    if (currentGuestId) {
        if (!activeGuestOrders[currentGuestId]) {
            const tableId = document.getElementById('table-select').value;
            const guestSelect = document.getElementById('guest-select');
            const guestName = guestSelect ? guestSelect.options[guestSelect.selectedIndex]?.text : 'Guest';
            activeGuestOrders[currentGuestId] = {
                cart: [],
                guestName: guestName,
                tableId: tableId,
                customerId: null,
                discount: 0
            };
        }
        activeGuestOrders[currentGuestId].cart = JSON.parse(JSON.stringify(cart));
        if (typeof updateGuestTabBadge === 'function') {
            updateGuestTabBadge(currentGuestId);
        }
    }
    
    // Auto-save immediately to database (no debounce for removals)
    if (currentGuestId) {
        const tableId = document.getElementById('table-select').value;
        if (tableId && currentGuestId) {
            savePendingOrder(tableId, currentGuestId, false).then(() => {
                console.log('Cart auto-saved after removal for guest:', currentGuestId);
            }).catch(error => {
                console.error('Error auto-saving after removal:', error);
            });
        }
    }
}

function updateQuantity(index, change) {
    const item = cart[index];
    const newQty = item.quantity + change;
    
    if (newQty > 0 && newQty <= item.max_stock) {
        item.quantity = newQty;
        renderCart();
        
        // Update active guest order - ensure it exists
        if (currentGuestId) {
            if (!activeGuestOrders[currentGuestId]) {
                const tableId = document.getElementById('table-select').value;
                const guestSelect = document.getElementById('guest-select');
                const guestName = guestSelect ? guestSelect.options[guestSelect.selectedIndex]?.text : 'Guest';
                activeGuestOrders[currentGuestId] = {
                    cart: [],
                    guestName: guestName,
                    tableId: tableId,
                    customerId: null,
                    discount: 0
                };
            }
            activeGuestOrders[currentGuestId].cart = JSON.parse(JSON.stringify(cart));
            if (typeof updateGuestTabBadge === 'function') {
                updateGuestTabBadge(currentGuestId);
            }
        }
        
        // Auto-save immediately to database
        if (currentGuestId) {
            const tableId = document.getElementById('table-select').value;
            if (tableId && currentGuestId) {
                savePendingOrder(tableId, currentGuestId, false);
            }
        }
    } else if (newQty <= 0) {
        removeFromCart(index);
    } else {
        alert('Not enough stock available!');
    }
}

function clearCart() {
    cart = [];
    renderCart();
    updateCartHeader();
    const printOrderBtn = document.getElementById('print-order-btn');
    if (printOrderBtn) printOrderBtn.disabled = true;
    
    // Update active guest order
    if (currentGuestId && activeGuestOrders && activeGuestOrders[currentGuestId]) {
        activeGuestOrders[currentGuestId].cart = [];
        if (typeof updateGuestTabBadge === 'function') {
            updateGuestTabBadge(currentGuestId);
        }
    }
}

// Clear all pending orders from database
function clearAllPendingOrders() {
    if (!confirm('Are you sure you want to clear ALL pending orders from the database? This cannot be undone.')) {
        return;
    }
    
    fetch('{{ route("admin.pos.clear-pending") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Successfully cleared ' + data.count + ' pending orders from the database.');
            // Clear all active guest orders
            activeGuestOrders = {};
            cart = [];
            currentGuestId = null;
            renderCart();
            
            // Remove all tabs
            const tabsContainer = document.getElementById('guest-orders-tabs');
            if (tabsContainer) {
                tabsContainer.innerHTML = '';
            }
            const tabsSection = document.getElementById('active-orders-tabs');
            if (tabsSection) {
                tabsSection.style.display = 'none';
            }
        } else {
            alert('Error: ' + (data.message || 'Failed to clear pending orders'));
        }
    })
    .catch(error => {
        console.error('Error clearing pending orders:', error);
        alert('An error occurred while clearing pending orders.');
    });
}

function renderCart() {
    const container = document.getElementById('cart-items');
    const emptyMessage = document.getElementById('empty-cart-message');
    const printOrderBtn = document.getElementById('print-order-btn');
    
    if (!container) {
        console.error('Cart container not found!');
        return;
    }
    
    console.log('renderCart called - cart.length:', cart.length, 'cart:', cart);
    
    if (cart.length === 0) {
        // Show appropriate message based on whether a guest is selected
        let emptyMessage = 'Cart is empty';
        if (!currentGuestId) {
            emptyMessage = 'Select a guest to start taking orders';
        }
        container.innerHTML = `<p class="text-center text-muted py-5" id="empty-cart-message">${emptyMessage}</p>`;
        if (printOrderBtn) printOrderBtn.disabled = true;
        updateTotals();
        return;
    }
    
    // Remove empty message if it exists
    if (emptyMessage) {
        emptyMessage.remove();
    }
    
    // Enable print order button if cart has items
    if (printOrderBtn) printOrderBtn.disabled = false;
    
    let html = '';
    cart.forEach((item, index) => {
        const itemTotal = (item.unit_price * item.quantity) - item.discount;
        html += `
            <div class="cart-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold">${item.name}</h6>
                        <small class="text-muted d-block">₦${formatCurrency(item.unit_price)} × ${item.quantity}</small>
                        ${item.discount > 0 ? `<small class="text-success d-block">Discount: ₦${formatCurrency(item.discount)}</small>` : ''}
                    </div>
                    <div class="text-end">
                        <span class="fw-bold text-primary fs-6">₦${formatCurrency(itemTotal)}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, -1)" title="Decrease quantity">
                            <i class="fe fe-minus"></i>
                        </button>
                        <span class="mx-3 fw-bold">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, 1)" title="Increase quantity">
                            <i class="fe fe-plus"></i>
                        </button>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${index})" title="Remove item">
                        <i class="fe fe-trash-2"></i> Remove
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    updateTotals();
    
    // Update tab badge if guest order is active
    if (currentGuestId) {
        updateGuestTabBadge(currentGuestId);
    }
}

// Helper function to format numbers with commas
function formatCurrency(amount) {
    // Convert to number if it's a string
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    // Handle NaN, null, or undefined
    if (isNaN(num) || num === null || num === undefined) {
        return '0.00';
    }
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Format amount input with commas (e.g., 10427.50 -> 10,427.50)
function formatAmountInput(input) {
    // Get the cursor position
    const cursorPos = input.selectionStart;
    const oldValue = input.value;
    
    // Remove all non-digit characters except decimal point
    let value = oldValue.replace(/[^\d.]/g, '');
    
    // Ensure only one decimal point
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Limit to 2 decimal places
    if (parts.length === 2 && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    
    // Format with commas
    if (value) {
        const numParts = value.split('.');
        const integerPart = numParts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        value = numParts.length > 1 ? integerPart + '.' + numParts[1] : integerPart;
    }
    
    input.value = value;
    
    // Restore cursor position (adjust for added commas)
    const newCursorPos = cursorPos + (value.length - oldValue.length);
    input.setSelectionRange(newCursorPos, newCursorPos);
}

function updateTotals() {
    const subtotal = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity) - (item.discount || 0), 0);
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    
    let vatAmount = 0;
    if (document.getElementById('vat-toggle').checked) {
        vatAmount = (subtotal - discount) * 0.075;
    }
    
    const total = subtotal - discount + vatAmount;
    
    document.getElementById('subtotal').textContent = '₦' + formatCurrency(subtotal);
    document.getElementById('vat-amount').textContent = '₦' + formatCurrency(vatAmount);
    document.getElementById('total').textContent = '₦' + formatCurrency(total);
    
    calculateChange();
}

function autoFillAmount() {
    const amountPaidInput = document.getElementById('amount-paid');
    // Only auto-fill if the field is empty
    if (!amountPaidInput.value || amountPaidInput.value.trim() === '') {
        const subtotal = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity) - (item.discount || 0), 0);
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        let vatAmount = 0;
        if (document.getElementById('vat-toggle').checked) {
            vatAmount = (subtotal - discount) * 0.075;
        }
        const total = subtotal - discount + vatAmount;
        // Format with commas
        const formattedTotal = formatCurrency(total);
        amountPaidInput.value = formattedTotal;
        calculateChange();
    }
}

function calculateChange() {
    const subtotal = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity) - (item.discount || 0), 0);
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    
    let vatAmount = 0;
    if (document.getElementById('vat-toggle').checked) {
        vatAmount = (subtotal - discount) * 0.075;
    }
    
    const total = subtotal - discount + vatAmount;
    // Remove commas from amount paid before parsing
    const amountPaidValue = document.getElementById('amount-paid').value.replace(/,/g, '');
    const amountPaid = parseFloat(amountPaidValue) || 0;
    const change = amountPaid - total;
    
    // Update amount received display if it exists
    const amountPaidDisplay = document.getElementById('amount-paid-display');
    if (amountPaidDisplay) {
        amountPaidDisplay.textContent = '₦' + formatCurrency(amountPaid);
    }
    
    document.getElementById('change').textContent = '₦' + formatCurrency(Math.max(0, change));
}

// Payment method change handler
document.getElementById('payment-method').addEventListener('change', function() {
    try {
        const isCredit = this.value === 'credit';
        const amountPaidGroup = document.getElementById('amount-paid-group');
        const changeGroup = document.getElementById('change-group');
        
        if (amountPaidGroup) amountPaidGroup.style.display = isCredit ? 'none' : 'block';
        if (changeGroup) changeGroup.style.display = isCredit ? 'none' : 'flex';
        
        if (isCredit) {
            autoFillAmount();
        }
    } catch (e) {
        console.error('Error in payment-method change listener:', e);
    }
});

function onCreditCustomerChanged(customerId) {
    const guestId = currentGuestId || document.getElementById('guest-select')?.value;
    const manageBtn = document.getElementById('manage-customer-btn');

    // Always show/hide "Active Credit" button based on customer selection
    if (manageBtn) {
        manageBtn.style.display = 'inline-block';
        manageBtn.disabled = !customerId;
        if (customerId) {
            manageBtn.setAttribute('data-customer-id', customerId);
        } else {
            manageBtn.removeAttribute('data-customer-id');
        }
    }

    if (customerId) {
        const paymentMethod = document.getElementById('payment-method');
        if (paymentMethod && (paymentMethod.value === 'cash' || !paymentMethod.value)) {
            paymentMethod.value = 'credit';
            paymentMethod.dispatchEvent(new Event('change'));
        }
    } else {
        const paymentMethod = document.getElementById('payment-method');
        if (paymentMethod && paymentMethod.value === 'credit') {
            paymentMethod.value = 'cash';
            paymentMethod.dispatchEvent(new Event('change'));
        }
    }

    if (guestId && customerId) {
        updateGuestCustomer(guestId, customerId);
    }
}

function initCreditCustomerSelectHandlers() {
    const customerSelect = document.getElementById('customer-select');
    if (!customerSelect) return;

    // Avoid double-binding if init runs twice
    if (customerSelect.dataset.creditHandlersBound === '1') return;
    customerSelect.dataset.creditHandlersBound = '1';

    customerSelect.addEventListener('change', function() {
        try {
            onCreditCustomerChanged(this.value);
        } catch (error) {
            console.error('Error in customer-select change listener:', error);
            alert('An error occurred while updating customer selection: ' + error.message);
        }
    });

    // Select2 sometimes doesn't trigger native change consistently
    try {
        if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
            $(customerSelect).on('select2:select select2:clear', function() {
                onCreditCustomerChanged(customerSelect.value);
            });
        }
    } catch (e) {
        console.warn('Select2 event binding failed:', e);
    }

    // Sync initial state (button enable/disable) with current selection
    onCreditCustomerChanged(customerSelect.value);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCreditCustomerSelectHandlers);
} else {
    initCreditCustomerSelectHandlers();
}

// Update guest's customer
function updateGuestCustomer(guestId, customerId) {
    // Show/hide manage-customer-btn based on customer selection
    const manageBtn = document.getElementById('manage-customer-btn');
    if (manageBtn) {
        manageBtn.style.display = customerId ? 'inline-block' : 'none';
        if (customerId) {
            manageBtn.setAttribute('data-customer-id', customerId);
        }
    }

    fetch(`/admin/tables/guests/${guestId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            customer_id: customerId ? parseInt(customerId) : null
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => { 
                let msg = response.statusText;
                try {
                    const json = JSON.parse(text);
                    msg = json.message || json.error || msg;
                } catch(e) {}
                throw new Error(msg);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.guest) {
            console.log('Guest customer updated:', customerId, 'New name:', data.guest.guest_name);
            
            if (activeGuestOrders[guestId]) {
                activeGuestOrders[guestId].guestName = data.guest.guest_name;
                activeGuestOrders[guestId].customerId = customerId;
                
                const tab = document.querySelector(`.guest-tab[data-guest-id="${guestId}"] .guest-tab-name`);
                if (tab) tab.textContent = data.guest.guest_name;
            }
            
            updateReceiptModalCustomer(customerId);
            
            // Update guest select dropdown option
            const guestOption = document.querySelector(`#guest-select option[value="${guestId}"]`);
            if (guestOption) {
                guestOption.textContent = data.guest.guest_name;
            }
            
            // Update guest card if it exists (not always present depend on current table)
            const guestCard = document.querySelector(`.guest-card[data-guest-id="${guestId}"] h5`);
            if (guestCard) {
                guestCard.textContent = data.guest.guest_name;
            }
            
            // Re-render cart to update customer info in header
            if (guestId === currentGuestId) {
                updateCartHeader();
            }
        }
    })
    .catch(error => {
        console.error('Error updating guest customer:', error);
    });
}

function showManageCustomerModal() {
    const manageBtn = document.getElementById('manage-customer-btn');
    const customerId = manageBtn?.getAttribute('data-customer-id') || document.getElementById('customer-select')?.value;
    const select = document.getElementById('customer-select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (!customerId) {
        alert('Please select a customer first.');
        return;
    }
    
    // Set basic info
    document.getElementById('manage-customer-id').value = customerId;
    document.getElementById('manage-customer-title').textContent = "Active Credit for " + selectedOption.text.split(' (')[0];
    
    // Check credit status from text
    const isCreditEnabled = selectedOption.text.includes('Credit Available');
    document.getElementById('manage-credit-enabled').checked = isCreditEnabled;
    
    // Extract current limit/balance if possible (or just leave 0)
    document.getElementById('manage-credit-limit').value = "500,000.00"; // Default suggest high limit
    
    const modal = new bootstrap.Modal(document.getElementById('manageCustomerModal'));
    modal.show();
}

function saveCustomerCredit() {
    const customerId = document.getElementById('manage-customer-id').value;
    const creditLimit = document.getElementById('manage-credit-limit').value.replace(/,/g, '');
    const creditEnabled = document.getElementById('manage-credit-enabled').checked ? 1 : 0;
    const btn = document.getElementById('save-customer-btn');
    const msg = document.getElementById('manage-customer-msg');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    
    // Get full customer data for the update (backend update requires phone, email etc currently)
    // Wait, let's check what fields are required. name, phone.
    // This is a limitation of the current update method.
    // I should check if I can just send the credit fields.
    // But wait! I modified the controller.
    
    fetch(`/admin/customers/${customerId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            credit_limit: creditLimit,
            credit_enabled: creditEnabled,
            // We need to pass name and phone because they are required in validation
            // This is hacky, but I'll fetch them from the select option if I can.
            // A better way is to make them optional in the controller if it's an AJAX credit update.
            name: document.getElementById('manage-customer-title').textContent.replace("Active Credit for ", ""),
            phone: "0000000000", // Fallback, will investigate if I can do better
            _method: 'PUT'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            msg.textContent = "Customer credit activated! Refreshing list...";
            msg.className = "alert alert-success mt-3";
            msg.classList.remove('d-none');
            
            // Reload page to refresh customer dropdown with new status
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            msg.textContent = (data.errors ? Object.values(data.errors)[0][0] : data.message) || "Failed to update customer.";
            msg.className = "alert alert-danger mt-3";
            msg.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = 'Save Changes';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        msg.textContent = "An error occurred. Check console.";
        msg.className = "alert alert-danger mt-3";
        msg.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = 'Save Changes';
    });
}

// Category filter
document.querySelectorAll('.category-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const category = this.dataset.category;
        const isKitchenFilter = this.dataset.kitchenFilter === 'true';
        
        // Kitchen category IDs from backend
        const kitchenCategoryIds = @json($kitchenCategoryIds ?? []);
        
        document.querySelectorAll('.product-item').forEach(item => {
            if (category === 'all') {
                item.style.display = 'block';
            } else if (isKitchenFilter) {
                // For kitchen filter, show products from kitchen categories
                const categoryId = parseInt(item.dataset.category);
                item.style.display = kitchenCategoryIds.includes(categoryId) ? 'block' : 'none';
            } else {
                // Regular category filter
                item.style.display = item.dataset.category === category ? 'block' : 'none';
            }
        });
    });
});

// Product search
const productSearchEl = document.getElementById('product-search');
if (productSearchEl) {
    productSearchEl.addEventListener('input', function() {
        const search = this.value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(item => {
            const productCard = item.querySelector('.product-card');
            const productName = productCard ? productCard.querySelector('h6')?.textContent.toLowerCase() : '';
            item.style.display = productName.includes(search) ? 'block' : 'none';
        });
    });
}

function loadTableGuests() {
    const tableId = document.getElementById('table-select').value;
    const guestsSection = document.getElementById('guests-section');
    const guestsList = document.getElementById('guests-list');
    const guestSelect = document.getElementById('guest-select');
    const tableInfo = document.getElementById('table-info');
    const noTableMessage = document.getElementById('no-table-message');
    const addGuestBtn = document.getElementById('add-guest-btn');
    
    if (!tableId) {
        guestsSection.style.display = 'none';
        tableInfo.style.display = 'none';
        noTableMessage.style.display = 'block';
        document.getElementById('quick-guide').style.display = 'block';
        guestSelect.value = '';
        return;
    }
    
            noTableMessage.style.display = 'none';
            document.getElementById('quick-guide').style.display = 'none';
            
            // Fetch table details and guests
    fetch(`/admin/tables/${tableId}/guests`)
        .then(response => response.json())
        .then(data => {
            // Show table info
            const tableOption = document.querySelector(`#table-select option[value="${tableId}"]`);
            const tableText = tableOption ? tableOption.textContent : '';
            document.getElementById('table-name-display').textContent = tableText.split(' - ')[0];
            if (data.table) {
                document.getElementById('table-capacity-display').textContent = 
                    `Capacity: ${data.table.capacity} seats | Location: ${data.table.location || 'N/A'}`;
                document.getElementById('table-status-badge').textContent = 
                    data.table.status === 'occupied' ? 'Occupied' : 'Available';
                document.getElementById('table-status-badge').className = 
                    data.table.status === 'occupied' ? 'badge bg-success' : 'badge bg-secondary';
            }
            tableInfo.style.display = 'block';
            
            // Update order info display
            updateOrderInfoDisplay();
            
            // Load pending orders for this table
            loadPendingOrdersForTable(tableId);
            
            // Clear previous guests
            guestsList.innerHTML = '';
            guestSelect.innerHTML = '<option value="">Select a Guest</option>';
            
            // Show guest required badge
            const guestRequiredBadge = document.getElementById('guest-required-badge');
            if (guestRequiredBadge) {
                guestRequiredBadge.style.display = 'inline-block';
            }
            
            if (data.guests && data.guests.length > 0) {
                // Create guest cards/buttons
                data.guests.forEach(guest => {
                    // Add to dropdown
                    const option = document.createElement('option');
                    option.value = guest.id;
                    option.textContent = guest.guest_name;
                    if (guest.customer) {
                        option.textContent += ` (${guest.customer.name})`;
                    }
                    guestSelect.appendChild(option);
                    
                    // Create guest card button
                    const guestCard = document.createElement('div');
                    guestCard.className = 'guest-card mb-2 p-2 border rounded cursor-pointer';
                    guestCard.setAttribute('data-guest-id', guest.id);
                    guestCard.style.cursor = 'pointer';
                    guestCard.style.transition = 'all 0.2s';
                    guestCard.onmouseenter = function() { this.style.backgroundColor = '#f0f0f0'; };
                    guestCard.onmouseleave = function() { this.style.backgroundColor = ''; };
                    guestCard.onclick = function() {
                        const tableId = document.getElementById('table-select').value;
                        const selectedGuestId = guest.id;
                        const selectedGuestName = guest.guest_name;
                        
                        // Save current guest's cart before switching
                        saveCurrentGuestCart();
                        
                        // Add or activate this guest's order (this will load pending order from DB)
                        activateGuestOrder(selectedGuestId, selectedGuestName, tableId);
                    };
                    
                    // Check if this guest has a pending order and show indicator
                    if (guest.id) {
                        fetch(`{{ route("admin.pos.get-pending") }}?table_id=${tableId}&table_guest_id=${guest.id}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.items && data.items.length > 0) {
                                // Add pending order badge to guest card
                                const badge = document.createElement('span');
                                badge.className = 'badge bg-warning text-dark ms-2';
                                badge.textContent = `${data.items.length} item(s) - ₦${formatCurrency(data.sale?.total || 0)}`;
                                badge.title = 'Pending order - Click to load';
                                guestCard.querySelector('.d-flex').appendChild(badge);
                            }
                        })
                        .catch(error => {
                            console.error('Error checking pending order for guest:', error);
                        });
                    }
                    
                    guestCard.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${guest.guest_name}</strong>
                                ${guest.customer ? `<br><small class="text-muted">${guest.customer.name}</small>` : ''}
                            </div>
                            <i class="fe fe-user text-primary"></i>
                        </div>
                    `;
                    
                    guestsList.appendChild(guestCard);
                });
                
                guestsSection.style.display = 'block';
                addGuestBtn.style.display = 'block';
            } else {
                // No guests - show option to add
                guestsList.innerHTML = `
                    <div class="alert alert-info mb-2">
                        <i class="fe fe-info me-2"></i>
                        This table has no guests. Click below to add guests first.
                    </div>
                `;
                guestsSection.style.display = 'block';
                addGuestBtn.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading guests:', error);
            guestsSection.style.display = 'none';
        });
}

function showAddGuestModal() {
    const tableId = document.getElementById('table-select').value;
    if (!tableId) {
        alert('Please select a table first');
        return;
    }
    
    // Get table number and existing guests count
    const tableOption = document.querySelector(`#table-select option[value="${tableId}"]`);
    const tableText = tableOption ? tableOption.textContent : '';
    const tableNumber = tableText.split(' - ')[0].trim();
    
    // Get the existing guests list
    const guestsList = document.getElementById('guests-list');
    const existingGuestCount = guestsList ? guestsList.querySelectorAll('.guest-card').length : 0;
    const nextSeatNumber = existingGuestCount + 1;
    
    // Auto-generate guest name
    const autoGeneratedName = `Table ${tableNumber} Seat ${nextSeatNumber}`;
    
    // Set the auto-generated name in the input field
    const guestNameInput = document.getElementById('guest-name-input');
    guestNameInput.value = autoGeneratedName;
    guestNameInput.placeholder = autoGeneratedName;
    
    // Clear customer select
    document.getElementById('guest-customer-select').value = '';
    
    // Show modal for adding guest
    const modal = document.getElementById('addGuestModal');
    if (modal) {
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        // Auto-focus and select the guest name input for easy editing
        setTimeout(() => {
            guestNameInput.focus();
            guestNameInput.select();
        }, 300);
    }
}

function addGuestToTable(tableId, guestName, customerId) {
    if (!tableId) {
        alert('Please select a table first');
        return Promise.reject(new Error('Missing tableId'));
    }
    
    return fetch(`/admin/tables/${tableId}/add-guest`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            guest_name: guestName,
            customer_id: customerId
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Clear form
            document.getElementById('guest-name-input').value = '';
            document.getElementById('guest-customer-select').value = '';
            
            // Close modal if open
            const modal = bootstrap.Modal.getInstance(document.getElementById('addGuestModal'));
            if (modal) modal.hide();
            
            // Reload guests
            loadTableGuests();
            return data;
        } else {
            alert(data.error || data.message || 'Failed to add guest');
            return Promise.reject(new Error(data.error || data.message || 'Failed to add guest'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMsg = error.message || error.error || 'Failed to add guest. Please try again.';
        alert(errorMsg);
        return Promise.reject(error);
    });
}

function showQuickCreateTableModal() {
    const modal = document.getElementById('quickCreateTableModal');
    if (modal) {
        new bootstrap.Modal(modal).show();
    }
}

function quickCreateTable() {
    const tableNumber = document.getElementById('quick-table-number').value.trim();
    const tableCapacity = document.getElementById('quick-table-capacity').value || 4;
    
    if (!tableNumber) {
        alert('Please enter a table number');
        return;
    }
    
    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fe fe-loader me-1"></i> Creating...';
    
    fetch('/admin/tables', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            number: tableNumber,
            capacity: parseInt(tableCapacity),
            location: document.getElementById('quick-table-location').value.trim() || null,
            name: document.getElementById('quick-table-name').value.trim() || null,
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('quickCreateTableModal'));
            if (modal) modal.hide();
            
            // Clear form
            document.getElementById('quick-table-number').value = '';
            document.getElementById('quick-table-name').value = '';
            document.getElementById('quick-table-capacity').value = '4';
            document.getElementById('quick-table-location').value = '';
            
            // Update table dropdown and select the new table
            const tableSelect = document.getElementById('table-select');
            const newOption = document.createElement('option');
            newOption.value = data.table.id;
            newOption.textContent = `${data.table.number} - ${data.table.name || 'Table ' + data.table.number} (Available)`;
            newOption.selected = true;
            tableSelect.appendChild(newOption);
            
            // Load guests (will show "no guests" message and add guest button)
            loadTableGuests();
            
            // Show message to add guests
            setTimeout(() => {
                if (confirm('Table created! Would you like to add guests now?')) {
                    showAddGuestModal();
                }
            }, 500);
        } else {
            alert(data.error || data.message || 'Failed to create table');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMsg = error.message || error.error || 'Failed to create table. Please try again.';
        alert(errorMsg);
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function checkout() {
    console.log('Checkout function called');
    console.log('Cart length:', cart.length);
    
    if (cart.length === 0) {
        alert('Cart is empty!');
        return;
    }
    
    // If user selected "Credit Account" radio, force credit checkout
    const creditRadio = document.getElementById('category-credit');
    const isCreditCategory = creditRadio ? creditRadio.checked : false;
    
    let paymentMethod = document.getElementById('payment-method').value;
    const customerId = document.getElementById('customer-select').value;
    
    if (isCreditCategory && paymentMethod !== 'credit') {
        paymentMethod = 'credit';
        const paymentMethodSelect = document.getElementById('payment-method');
        if (paymentMethodSelect) {
            paymentMethodSelect.value = 'credit';
            paymentMethodSelect.dispatchEvent(new Event('change'));
        }
    }
    const tableId = document.getElementById('table-select').value;
    
    // Get guest ID - check multiple sources to ensure we get it
    const guestSelect = document.getElementById('guest-select');
    let guestId = guestSelect.value;
    
    // If no value in select, try to get from data attribute
    if (!guestId && guestSelect.hasAttribute('data-selected-guest-id')) {
        guestId = guestSelect.getAttribute('data-selected-guest-id');
    }
    
    // Final fallback: check active guest card's data attribute
    if (!guestId) {
        const activeGuestCard = document.querySelector('.guest-card.border-primary');
        if (activeGuestCard) {
            const cardGuestId = activeGuestCard.getAttribute('data-guest-id');
            if (cardGuestId) {
                guestId = cardGuestId;
                // Also update the select to keep it in sync
                guestSelect.value = guestId;
            }
        }
    }
    
    console.log('Guest selection - Select value:', guestSelect.value, 'Data attribute:', guestSelect.getAttribute('data-selected-guest-id'), 'Final guestId:', guestId, 'Table ID:', tableId);
    
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    
    // Calculate total from cart (more reliable than parsing from display)
    const subtotal = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity) - (item.discount || 0), 0);
    
    let vatRate = 0;
    let vatAmount = 0;
    if (document.getElementById('vat-toggle').checked) {
        vatRate = 0.075;
        vatAmount = (subtotal - discount) * vatRate;
    }
    
    const total = subtotal - discount + vatAmount;
    
    // Get amount paid - for credit sales, it's 0, otherwise require it
    // Remove commas from amount paid before processing
    const amountPaidInput = document.getElementById('amount-paid').value.replace(/,/g, '').trim();
    let amountPaid = 0;
    
    if (paymentMethod === 'credit') {
        amountPaid = 0; // Credit sales don't require payment
    } else {
        if (!amountPaidInput || amountPaidInput === '') {
            alert('Please enter the amount received!');
            document.getElementById('amount-paid').focus();
            return;
        }
        amountPaid = parseFloat(amountPaidInput);
        if (isNaN(amountPaid) || amountPaid < 0) {
            alert('Please enter a valid amount!');
            document.getElementById('amount-paid').focus();
            return;
        }
        if (amountPaid < total) {
            alert('Amount paid (' + formatCurrency(amountPaid) + ') is less than total (' + formatCurrency(total) + ')!');
            document.getElementById('amount-paid').focus();
            return;
        }
    }
    
    if (paymentMethod === 'credit') {
        if (!customerId) {
            alert('Please select a customer for credit sales.');
            return;
        }
        
        // Final check for credit enabled status
        const selectedOption = document.querySelector(`#customer-select option[value="${customerId}"]`);
        const creditEnabledRaw = selectedOption ? (selectedOption.getAttribute('data-credit-enabled') || '') : '';
        const creditEnabled = creditEnabledRaw === 'true' || creditEnabledRaw === '1' || creditEnabledRaw === 'yes';
        
        if (!creditEnabled) {
            alert('This customer does not have credit enabled. Please enable credit for them or choose a different payment method.');
            return;
        }
    }
    
    // Require guest selection when table is selected
    if (tableId && !guestId) {
        alert('Please select a guest from the table first. Each guest must have a separate bill.');
        document.getElementById('guest-select')?.focus();
        return;
    }
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('CSRF token not found. Please refresh the page and try again.');
        return;
    }
    
    // Validate and format cart items
    const formattedItems = cart.map(item => {
        if (!item.product_id || !item.unit_price || !item.quantity) {
            throw new Error('Invalid cart item: ' + JSON.stringify(item));
        }
        return {
            product_id: parseInt(item.product_id),
            quantity: parseInt(item.quantity),
            unit_price: parseFloat(item.unit_price),
            discount: parseFloat(item.discount || 0)
        };
    });
    
    // Prepare request data
    const requestData = {
        items: formattedItems,
        customer_id: customerId || null,
        table_id: tableId || null,
        table_guest_id: guestId || null,
        payment_method: paymentMethod,
        amount_paid: amountPaid,
        discount: discount,
        vat_rate: vatRate,
        notes: ''
    };
    
    console.log('Sending checkout request:', requestData);
    
    // Disable button and show loading
    const checkoutBtn = document.getElementById('checkout-btn');
    checkoutBtn.disabled = true;
    checkoutBtn.innerHTML = '<i class="fe fe-loader me-2"></i> Processing...';
    
    fetch('{{ route("admin.pos.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        // Try to parse response as JSON
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON:', text);
                throw { error: 'Invalid response from server: ' + text.substring(0, 100) };
            }
        }).then(data => {
            if (!response.ok) {
                throw data;
            }
            return data;
        });
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.queued) {
            alert(data.message || 'Saved offline. Will sync when internet is back.');
            // Re-enable button; keep cart so user can continue working
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="fe fe-check-circle me-2"></i> Complete Sale';
            return;
        }
        
        if (data.success) {
            showReceipt(data.sale);
            
            // Save current guest selection before clearing
            const tableIdAfterSale = document.getElementById('table-select').value;
            const currentGuestId = document.getElementById('guest-select').value;
            const currentGuestData = document.getElementById('guest-select').getAttribute('data-selected-guest-id');
            const savedGuestId = currentGuestId || currentGuestData;
            
            // Clear cart and payment fields, but KEEP table and guest selection
            clearCart();
            document.getElementById('discount').value = 0;
            document.getElementById('amount-paid').value = '';
            // Clear the formatted display
            const amountPaidDisplay = document.getElementById('amount-paid-display');
            if (amountPaidDisplay) {
                amountPaidDisplay.textContent = '₦0.00';
            }
            document.getElementById('customer-select').value = '';
            document.getElementById('payment-method').value = 'cash';
            
            // Update totals to show 0.00
            updateTotals();
            calculateChange();
            
            // Reload guests if table is selected to show updated bills
            // BUT keep the current guest selected for next order
            if (tableIdAfterSale) {
                // Refresh pending orders list immediately
                loadPendingOrdersForTable(tableIdAfterSale);
                
                setTimeout(() => {
                    loadTableGuests();
                    // Restore guest selection after reload
                    if (savedGuestId) {
                        setTimeout(() => {
                            const guestSelect = document.getElementById('guest-select');
                            if (guestSelect) {
                                guestSelect.value = savedGuestId;
                                guestSelect.setAttribute('data-selected-guest-id', savedGuestId);
                                
                                // Restore active state on guest card
                                const guestCards = document.querySelectorAll('.guest-card');
                                guestCards.forEach(card => {
                                    const cardGuestId = card.getAttribute('data-guest-id');
                                    if (cardGuestId === savedGuestId) {
                                        card.classList.add('border-primary', 'bg-primary-transparent');
                                        
                                        // Update selected guest indicator
                                        const indicator = document.getElementById('selected-guest-indicator');
                                        const guestNameSpan = document.getElementById('selected-guest-name');
                                        if (indicator && guestNameSpan) {
                                            const guestName = card.querySelector('strong')?.textContent || 'Selected Guest';
                                            guestNameSpan.textContent = guestName;
                                            indicator.style.display = 'block';
                                        }
                                    }
                                });
                                
                                updateOrderInfoDisplay();
                                updateCartHeader();
                            }
                        }, 500);
                    }
                }, 1000);
            }
        } else {
            alert(data.error || data.message || 'Failed to process sale.');
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="fe fe-check-circle me-2"></i> Complete Sale';
        }
    })
    .catch(error => {
        console.error('Checkout error:', error);
        let errorMsg = 'Failed to process sale. ';
        
        if (error.error) {
            errorMsg += error.error;
        } else if (error.message) {
            errorMsg += error.message;
        } else if (typeof error === 'string') {
            errorMsg += error;
        } else {
            errorMsg += 'Please check the console for details.';
        }
        
        alert(errorMsg);
        checkoutBtn.disabled = false;
        checkoutBtn.innerHTML = '<i class="fe fe-check-circle me-2"></i> Complete Sale';
    });
}

// Store current sale data for close order functionality
let currentSaleData = null;

function showReceipt(sale) {
    currentSaleData = sale;
    
    const bankName = @json(\App\Models\Setting::getValue('bank.name', ''));
    const accountName = @json(\App\Models\Setting::getValue('bank.account_name', ''));
    const accountNumber = @json(\App\Models\Setting::getValue('bank.account_number', ''));
    
    let itemsHtml = sale.items.map(item => `
        <tr>
            <td>${item.product_name}</td>
            <td class="text-center">${item.quantity}</td>
            <td class="text-end">₦${parseFloat(item.total).toFixed(2)}</td>
        </tr>
    `).join('');
    
    // Get table and guest info if available
    const tableId = document.getElementById('table-select').value;
    const guestId = document.getElementById('guest-select').value;
    let tableInfo = '';
    let guestInfo = '';
    
    if (tableId) {
        const tableOption = document.querySelector(`#table-select option[value="${tableId}"]`);
        if (tableOption) {
            const tableName = tableOption.textContent.split(' - ')[0];
            tableInfo = `<p class="mb-0"><strong>Table:</strong> ${tableName}</p>`;
        }
    }
    
    if (guestId) {
        const guestOption = document.querySelector(`#guest-select option[value="${guestId}"]`);
        if (guestOption) {
            guestInfo = `<p class="mb-0"><strong>Guest:</strong> ${guestOption.textContent}</p>`;
        }
    }
    
    document.getElementById('receipt-content').innerHTML = `
        <div class="text-center mb-4">
            <img src="{{ asset('logo.jpg') }}" alt="Optizee Hotel and Suites" style="max-width: 150px; max-height: 80px; margin-bottom: 15px;">
            <h4>Optizee Hotel and Suites</h4>
            <p class="mb-0">Invoice: ${sale.invoice_number}</p>
            <p class="mb-0">${new Date(sale.created_at).toLocaleString()}</p>
            ${tableInfo}
            ${guestInfo}
        </div>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                ${itemsHtml}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-end"><strong>Subtotal:</strong></td>
                    <td class="text-end">₦${parseFloat(sale.subtotal).toFixed(2)}</td>
                </tr>
                ${parseFloat(sale.discount) > 0 ? `
                <tr>
                    <td colspan="2" class="text-end"><strong>Discount:</strong></td>
                    <td class="text-end">-₦${parseFloat(sale.discount).toFixed(2)}</td>
                </tr>
                ` : ''}
                <tr>
                    <td colspan="2" class="text-end"><strong>Total:</strong></td>
                    <td class="text-end"><strong>₦${parseFloat(sale.total).toFixed(2)}</strong></td>
                </tr>
                <tr>
                    <td colspan="2" class="text-end"><strong>Payment:</strong></td>
                    <td class="text-end">${sale.is_credit_sale ? 'Credit' : sale.payment_method.toUpperCase()}</td>
                </tr>
                ${!sale.is_credit_sale ? `
                <tr>
                    <td colspan="2" class="text-end"><strong>Amount Paid:</strong></td>
                    <td class="text-end">₦${parseFloat(sale.amount_paid).toFixed(2)}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-end"><strong>Change:</strong></td>
                    <td class="text-end">₦${parseFloat(sale.change).toFixed(2)}</td>
                </tr>
                ` : ''}
            </tfoot>
        </table>
        <div class="text-center mt-4">
            <p class="mb-0">Thank you for your patronage!</p>
            <p class="mb-0">Served by: ${sale.user.name}</p>
        </div>
        <div class="text-center mt-3 pt-3 border-top">
            <p class="mb-1"><strong>Bank Transfer Details:</strong></p>
            ${bankName ? `<p class="mb-0"><strong>Bank:</strong> ${bankName}</p>` : ''}
            ${accountNumber ? `<p class="mb-0"><strong>Account Number:</strong> ${accountNumber}</p>` : ''}
            ${accountName ? `<p class="mb-0"><strong>Account Name:</strong> ${accountName}</p>` : ''}
        </div>
    `;
    
    // Show/hide close order button based on whether there's a table/guest
    const closeOrderBtn = document.getElementById('close-order-btn');
    if (tableId && guestId) {
        // Guest is now automatically deactivated on the backend after sale completion.
        // We can hide this button or change it to "Release Table" if needed.
        // For now, let's hide it as the guest is already checked out.
        closeOrderBtn.style.display = 'none';
        closeOrderBtn.setAttribute('data-table-id', tableId);
        closeOrderBtn.setAttribute('data-guest-id', guestId);
    } else if (tableId) {
        // If it was a table-wide sale without a specific guest, 
        // we might still want to manually release the table.
        closeOrderBtn.style.display = 'inline-block';
        closeOrderBtn.setAttribute('data-table-id', tableId);
        closeOrderBtn.removeAttribute('data-guest-id');
    } else {
        closeOrderBtn.style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('receiptModal')).show();
}

function closeGuestOrder() {
    const closeOrderBtn = document.getElementById('close-order-btn');
    const tableId = closeOrderBtn.getAttribute('data-table-id');
    const guestId = closeOrderBtn.getAttribute('data-guest-id');
    
    if (!tableId) {
        alert('No table selected.');
        return;
    }
    
    let confirmMessage = '';
    if (guestId) {
        const guestOption = document.querySelector(`#guest-select option[value="${guestId}"]`);
        const guestName = guestOption ? guestOption.textContent : 'this guest';
        confirmMessage = `Close order for ${guestName}? This will mark the guest as having left.`;
    } else {
        confirmMessage = 'Release this table? This will mark all guests as having left and make the table available.';
    }
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Disable button during request
    closeOrderBtn.disabled = true;
    const originalText = closeOrderBtn.innerHTML;
    closeOrderBtn.innerHTML = '<i class="fe fe-loader me-2"></i> Closing...';
    
    const url = guestId 
        ? `/admin/tables/guests/${guestId}`
        : `/admin/tables/${tableId}/release`;
    
    const method = guestId ? 'DELETE' : 'POST';
    
    fetch(url, {
        method: method,
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
        // Close the receipt modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('receiptModal'));
        if (modal) modal.hide();
        
        // Show success message
        alert(guestId ? 'Guest order closed successfully.' : 'Table released successfully.');
        
        // Reload table guests or clear selection
        if (tableId) {
            // Clear table selection if table was released
            if (!guestId) {
                document.getElementById('table-select').value = '';
                clearTableSelection();
            } else {
                // Reload guests to update status
                loadTableGuests();
            }

        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMsg = error.message || error.error || 'Failed to close order. Please try again.';
        alert(errorMsg);
        closeOrderBtn.disabled = false;
        closeOrderBtn.innerHTML = originalText;
    });
}

function printOrderPreview() {
    if (cart.length === 0) {
        alert('Cart is empty!');
        return;
    }
    
    try {
        // Get current order details
        const tableId = document.getElementById('table-select').value;
        const guestId = document.getElementById('guest-select').value;
        const customerId = document.getElementById('customer-select').value;
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const subtotal = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity) - (item.discount || 0), 0);
        const total = subtotal - discount;
        
        // Get table and guest names
        let tableInfo = '';
        let guestInfo = '';
        let customerInfo = '';
        
        if (tableId) {
            const tableOption = document.querySelector(`#table-select option[value="${tableId}"]`);
            if (tableOption) {
                const tableName = tableOption.textContent.split(' - ')[0];
                tableInfo = `<p class="mb-0"><strong>Table:</strong> ${tableName}</p>`;
            }
        }
        
        if (guestId) {
            const guestOption = document.querySelector(`#guest-select option[value="${guestId}"]`);
            if (guestOption) {
                guestInfo = `<p class="mb-0"><strong>Guest:</strong> ${guestOption.textContent}</p>`;
            }
        }
        
        if (customerId) {
            const customerOption = document.querySelector(`#customer-select option[value="${customerId}"]`);
            if (customerOption) {
                customerInfo = `<p class="mb-0"><strong>Customer:</strong> ${customerOption.textContent.split(' (')[0]}</p>`;
            }
        }
        
        // Build items HTML
        let itemsHtml = '';
        cart.forEach(item => {
            const itemTotal = (item.unit_price * item.quantity) - (item.discount || 0);
            itemsHtml += `
                <tr>
                    <td>${item.name}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-end">₦${item.unit_price.toFixed(2)}</td>
                    ${item.discount > 0 ? `<td class="text-end">-₦${item.discount.toFixed(2)}</td>` : '<td class="text-end">-</td>'}
                    <td class="text-end">₦${itemTotal.toFixed(2)}</td>
                </tr>
            `;
        });
        
        // Create print content
        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Order Preview - Optizee Hotel and Suites</title>
                <meta charset="UTF-8">
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: 'Courier New', monospace; 
                        max-width: 300px; 
                        margin: 0 auto; 
                        padding: 20px;
                        font-size: 12px;
                    }
                    h4 { text-align: center; margin-bottom: 10px; }
                    .header { text-align: center; margin-bottom: 15px; }
                    .info-section { margin: 10px 0; padding: 10px; background: #f5f5f5; border-radius: 5px; }
                    p { margin: 5px 0; }
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin: 15px 0;
                    }
                    th, td { 
                        padding: 5px; 
                        text-align: left; 
                        border-bottom: 1px solid #ddd;
                        font-size: 11px;
                    }
                    th { font-weight: bold; background: #f0f0f0; }
                    .text-center { text-align: center; }
                    .text-end { text-align: right; }
                    .text-right { text-align: right; }
                    tfoot tr { font-weight: bold; }
                    .total-row { font-size: 14px; }
                    .status-badge { 
                        display: inline-block; 
                        padding: 3px 8px; 
                        background: #ffc107; 
                        color: #000; 
                        border-radius: 3px; 
                        font-size: 10px;
                        margin-top: 10px;
                    }
                    @media print {
                        body { margin: 0; padding: 10px; }
                        @page { margin: 0.5cm; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h4>Optizee Hotel and Suites</h4>
                    <p class="mb-0"><strong>ORDER PREVIEW</strong></p>
                    <p class="mb-0">${new Date().toLocaleString()}</p>
                    <span class="status-badge">PENDING PAYMENT</span>
                </div>
                
                ${tableInfo || guestInfo || customerInfo ? `
                <div class="info-section">
                    ${tableInfo}
                    ${guestInfo}
                    ${customerInfo}
                </div>
                ` : ''}
                
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Disc</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                            <td class="text-end">₦${subtotal.toFixed(2)}</td>
                        </tr>
                        ${discount > 0 ? `
                        <tr>
                            <td colspan="4" class="text-end"><strong>Discount:</strong></td>
                            <td class="text-end">-₦${discount.toFixed(2)}</td>
                        </tr>
                        ` : ''}
                        <tr class="total-row">
                            <td colspan="4" class="text-end"><strong>TOTAL:</strong></td>
                            <td class="text-end"><strong>₦${total.toFixed(2)}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="info-section">
                    <p class="mb-0"><strong>Note:</strong> This is an order preview. Payment not yet processed.</p>
                    <p class="mb-0" style="margin-top: 10px;">Thank you for your order!</p>
                </div>
            </body>
            </html>
        `;
        
        // Try to open print window
        const printWindow = window.open('', '_blank', 'width=400,height=600');
        
        if (!printWindow) {
            // If popup blocked, try alternative method
            alert('Popup blocked. Please allow popups for this site, or use Ctrl+P to print.');
            return;
        }
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Wait for content to load before printing
        printWindow.onload = function() {
            setTimeout(function() {
                printWindow.print();
            }, 250);
        };
        
        // Fallback if onload doesn't fire
        setTimeout(function() {
            if (printWindow.document.readyState === 'complete') {
                printWindow.print();
            }
        }, 500);
        
    } catch (error) {
        console.error('Print error:', error);
        alert('Error printing order preview. Please try again.');
    }
}

function printReceipt() {
    try {
        const content = document.getElementById('receipt-content').innerHTML;
        
        // Try to open print window
        const printWindow = window.open('', '_blank', 'width=400,height=600');
        
        if (!printWindow) {
            // If popup blocked, try alternative method
            alert('Popup blocked. Please allow popups for this site, or use Ctrl+P to print.');
            return;
        }
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Receipt</title>
                <meta charset="UTF-8">
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: 'Courier New', monospace; 
                        max-width: 300px; 
                        margin: 0 auto; 
                        padding: 20px;
                        font-size: 12px;
                    }
                    .logo { 
                        text-align: center; 
                        margin-bottom: 15px; 
                    }
                    .logo img { 
                        max-width: 150px; 
                        max-height: 80px; 
                        height: auto; 
                    }
                    h4 { text-align: center; margin-bottom: 10px; }
                    p { margin: 5px 0; }
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin: 15px 0;
                    }
                    th, td { 
                        padding: 5px; 
                        text-align: left; 
                        border-bottom: 1px solid #ddd;
                    }
                    th { font-weight: bold; }
                    .text-center { text-align: center; }
                    .text-end { text-align: right; }
                    tfoot tr { font-weight: bold; }
                    @media print {
                        body { margin: 0; padding: 10px; }
                        @page { margin: 0.5cm; }
                    }
                </style>
            </head>
            <body>${content}</body>
            </html>
        `);
        printWindow.document.close();
        
        // Wait for content to load before printing
        printWindow.onload = function() {
            setTimeout(function() {
                printWindow.print();
            }, 250);
        };
        
        // Fallback if onload doesn't fire
        setTimeout(function() {
            if (printWindow.document.readyState === 'complete') {
                printWindow.print();
            }
        }, 500);
        
    } catch (error) {
        console.error('Print error:', error);
        alert('Error printing receipt. Please try using Ctrl+P or right-click and select Print.');
    }
}

// Load table guests on page load if table is pre-selected
document.addEventListener('DOMContentLoaded', function() {
    // Check if sale_id is in URL (for loading a specific order)
    const urlParams = new URLSearchParams(window.location.search);
    const saleId = urlParams.get('sale_id');
    const printMode = urlParams.get('print') === 'true';
    
    if (saleId) {
        // Load the order by sale_id
        fetch(`{{ route("admin.pos.get-pending") }}?sale_id=${saleId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Order data received:', data);
            
            if (!data.success) {
                alert(data.message || 'Order not found or could not be loaded.');
                return;
            }
            
            if (data.sale) {
                const sale = data.sale;
                const items = data.items || [];
                
                console.log('Loading order:', { saleId: sale.id, tableId: sale.table_id, guestId: sale.table_guest_id, itemsCount: items.length });
                
                // If print mode, just print and return
                if (printMode) {
                    printPendingOrderReceipt(sale, items);
                    return;
                }
                
                // Show message if order is completed
                if (data.message) {
                    alert(data.message);
                }
                
                // Prepare cart items first - ensure all values are proper types
                const cartItems = items.length > 0 ? items.map(item => ({
                    product_id: item.product_id,
                    name: item.name,
                    unit_price: parseFloat(item.unit_price) || 0,
                    quantity: parseInt(item.quantity) || 0,
                    discount: parseFloat(item.discount) || 0,
                    max_stock: 9999
                })) : [];
                
                console.log('Cart items prepared:', cartItems.length);
                
                // Load the table
                const tableSelect = document.getElementById('table-select');
                if (tableSelect && sale.table_id) {
                    tableSelect.value = sale.table_id;
                    
                    // Trigger table change to load guests
                    const tableChangeEvent = new Event('change', { bubbles: true });
                    tableSelect.dispatchEvent(tableChangeEvent);
                    
                    // Wait for guests to load, then set up the cart
                    // Use a longer timeout and check multiple times if guests are loaded
                    let attempts = 0;
                    const maxAttempts = 10;
                    
                    const setupCart = () => {
                        attempts++;
                        const guestId = sale.table_guest_id;
                        const guestName = sale.guest_name || 'Guest';
                        
                        // Check if guest card exists
                        const guestCard = document.querySelector(`[data-guest-id="${guestId}"]`);
                        const guestSelect = document.getElementById('guest-select');
                        
                        if (!guestCard && (!guestSelect || !guestSelect.querySelector(`option[value="${guestId}"]`))) {
                            if (attempts < maxAttempts) {
                                console.log(`Guest not loaded yet, attempt ${attempts}/${maxAttempts}, retrying...`);
                                setTimeout(setupCart, 300);
                                return;
                            } else {
                                console.error('Guest card not found after multiple attempts');
                            }
                        }
                        
                        console.log('Setting up guest order:', { guestId, guestName, cartItemsCount: cartItems.length, attempt: attempts });
                        
                        // Pre-populate the guest order BEFORE any other operations
                        activeGuestOrders[guestId] = {
                            cart: JSON.parse(JSON.stringify(cartItems)),
                            guestName: guestName,
                            tableId: sale.table_id,
                            customerId: sale.customer_id || null,
                            discount: sale.discount || 0
                        };
                        
                        console.log('activeGuestOrders set:', activeGuestOrders[guestId]);
                        
                        // Set guest select value
                        if (guestSelect) {
                            guestSelect.value = guestId;
                            guestSelect.setAttribute('data-selected-guest-id', guestId);
                        }
                        
                        // Highlight the guest card if it exists
                        if (guestCard) {
                            // Remove active class from all cards
                            document.querySelectorAll('.guest-card').forEach(card => {
                                card.classList.remove('border-primary', 'bg-primary-transparent');
                            });
                            guestCard.classList.add('border-primary', 'bg-primary-transparent');
                        }
                        
                        // Set current guest ID
                        currentGuestId = guestId;
                        
                        // CRITICAL: Set cart directly from cartItems FIRST, before anything else
                        // Ensure all numeric values are properly converted
                        cart = cartItems.map(item => ({
                            product_id: item.product_id,
                            name: item.name,
                            unit_price: parseFloat(item.unit_price) || 0,
                            quantity: parseInt(item.quantity) || 0,
                            discount: parseFloat(item.discount) || 0,
                            max_stock: 9999
                        }));
                        console.log('STEP 1: Cart set directly to:', cart.length, 'items', JSON.stringify(cart));
                        
                        // Set current guest ID immediately
                        currentGuestId = guestId;
                        
                        // Update activeGuestOrders to ensure consistency
                        activeGuestOrders[guestId] = {
                            cart: JSON.parse(JSON.stringify(cartItems)),
                            guestName: guestName,
                            tableId: sale.table_id,
                            customerId: sale.customer_id || null,
                            discount: sale.discount || 0
                        };
                        console.log('STEP 2: activeGuestOrders updated:', activeGuestOrders[guestId]);
                        
                        // Remove empty message IMMEDIATELY
                        const emptyMsg = document.getElementById('empty-cart-message');
                        const cartContainer = document.getElementById('cart-items');
                        if (emptyMsg) {
                            emptyMsg.remove();
                        }
                        if (cartContainer && cart.length > 0) {
                            cartContainer.innerHTML = ''; // Clear container
                        }
                        
                        // Set discount
                        if (sale.discount) {
                            const discountInput = document.getElementById('discount');
                            if (discountInput) {
                                discountInput.value = sale.discount;
                            }
                        }
                        
                        // Set customer
                        if (sale.customer_id) {
                            const customerSelect = document.getElementById('customer-select');
                            if (customerSelect) {
                                customerSelect.value = sale.customer_id;
                            }
                        }
                        
                        // Show selected guest indicator
                        const indicator = document.getElementById('selected-guest-indicator');
                        const guestNameSpan = document.getElementById('selected-guest-name');
                        if (indicator && guestNameSpan) {
                            guestNameSpan.textContent = guestName;
                            indicator.style.display = 'block';
                        }
                        
                        // Update cart header
                        updateCartHeader();
                        
                        // STEP 3: Render cart IMMEDIATELY - no delays
                        console.log('STEP 3: Rendering cart NOW with', cart.length, 'items');
                        console.log('Cart contents:', JSON.stringify(cart));
                        
                        // Force render by directly manipulating DOM if needed
                        if (cart.length > 0) {
                            renderCart();
                            updateTotals();
                            
                            // Verify it rendered
                            setTimeout(() => {
                                const container = document.getElementById('cart-items');
                                if (container) {
                                    const hasItems = container.querySelector('.cart-item') !== null;
                                    const isEmpty = container.innerHTML.includes('empty') || 
                                                   container.innerHTML.includes('Select a guest');
                                    
                                    console.log('STEP 4: Cart render verification - hasItems:', hasItems, 'isEmpty:', isEmpty, 'cart.length:', cart.length);
                                    
                                    if (isEmpty || !hasItems) {
                                        console.error('Cart did not render! Forcing manual render...');
                                        // Manual render as last resort
                                        let html = '';
                                        cart.forEach((item, index) => {
                                            const itemTotal = (item.unit_price * item.quantity) - (item.discount || 0);
                                            html += `
                                                <div class="cart-item">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1 fw-bold">${item.name}</h6>
                                                            <small class="text-muted d-block">₦${formatCurrency(item.unit_price)} × ${item.quantity}</small>
                                                            ${item.discount > 0 ? `<small class="text-success d-block">Discount: ₦${formatCurrency(item.discount)}</small>` : ''}
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="fw-bold text-primary fs-6">₦${formatCurrency(itemTotal)}</span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                                        <div class="d-flex align-items-center">
                                                            <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, -1)" title="Decrease quantity">
                                                                <i class="fe fe-minus"></i>
                                                            </button>
                                                            <span class="mx-3 fw-bold">${item.quantity}</span>
                                                            <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, 1)" title="Increase quantity">
                                                                <i class="fe fe-plus"></i>
                                                            </button>
                                                        </div>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${index})" title="Remove item">
                                                            <i class="fe fe-trash-2"></i> Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            `;
                                        });
                                        container.innerHTML = html;
                                        updateTotals();
                                    }
                                }
                            }, 100);
                        } else {
                            console.error('Cart is empty! cartItems.length:', cartItems.length);
                        }
                    };
                    
                    // Start setup after initial delay
                    setTimeout(setupCart, 1500);
                } else {
                    alert('Order loaded but table information is missing.');
                }
            } else {
                alert('Order data is missing from response.');
            }
        })
        .catch(error => {
            console.error('Error loading order:', error);
            console.error('Error details:', error.message, error.stack);
            alert('Error loading order: ' + (error.message || 'Please try again.'));
        });
        return; // Don't run the normal table/guest loading code
    }
    
    @if($selectedTable)
    // Wait a bit for DOM to be ready
    setTimeout(function() {
        // Ensure table is selected in dropdown
        const tableSelect = document.getElementById('table-select');
        if (tableSelect && !tableSelect.value) {
            tableSelect.value = '{{ $selectedTable->id }}';
        }
        loadTableGuests();
        updateOrderInfoDisplay();
        @if($selectedGuest)
        setTimeout(function() {
            // Select the guest card and load their pending order
            const guestCards = document.querySelectorAll('.guest-card');
            guestCards.forEach(card => {
                if (card.textContent.includes('{{ $selectedGuest->guest_name }}')) {
                    // Click the card to activate the guest order
                    card.click();
                    // Also explicitly load the pending order for this guest
                    const tableId = {{ $selectedTable->id }};
                    const guestId = {{ $selectedGuest->id }};
                    loadPendingOrderForGuest(tableId, guestId).then(() => {
                        console.log('Loaded pending order for guest on page load');
                        renderCart();
                        updateTotals();
                    });
                }
            });
            updateOrderInfoDisplay();
        }, 1500);
        @endif
    }, 300);
    @else
    // If no table is selected but tables exist, select the first one
    setTimeout(function() {
        const tableSelect = document.getElementById('table-select');
        if (tableSelect && tableSelect.options.length > 1) {
            // Skip the first option which is "-- Select Table --"
            const firstTableOption = tableSelect.options[1];
            if (firstTableOption) {
                tableSelect.value = firstTableOption.value;
                // Load guests for the default table
                setTimeout(function() {
                    loadTableGuests();
                    updateOrderInfoDisplay();
                }, 300);
            } else {
                // Show no table message by default
                document.getElementById('no-table-message').style.display = 'block';
            }
        } else {
            // Show no table message by default
            document.getElementById('no-table-message').style.display = 'block';
        }
    }, 100);
    @endif
});

function submitAddGuest() {
    const tableId = document.getElementById('table-select').value;
    let guestName = document.getElementById('guest-name-input').value.trim();
    const customerId = document.getElementById('guest-customer-select').value;
    
    // If guest name is empty, use auto-generated name
    if (!guestName) {
        const tableOption = document.querySelector(`#table-select option[value="${tableId}"]`);
        const tableText = tableOption ? tableOption.textContent : '';
        const tableNumber = tableText.split(' - ')[0].trim();
        
        const guestsList = document.getElementById('guests-list');
        const existingGuestCount = guestsList ? guestsList.querySelectorAll('.guest-card').length : 0;
        const nextSeatNumber = existingGuestCount + 1;
        
        guestName = `Table ${tableNumber} Seat ${nextSeatNumber}`;
    }
    
    if (!tableId) {
        alert('Please select a table first');
        return;
    }
    
    addGuestToTable(tableId, guestName, customerId || null);
}

function clearGuestSelection() {
    // Clear guest selection
    document.getElementById('guest-select').value = '';
    document.getElementById('selected-guest-indicator').style.display = 'none';
    
    // Remove active class from all guest cards
    document.querySelectorAll('.guest-card').forEach(card => {
        card.classList.remove('border-primary', 'bg-primary-transparent');
    });
    
    // Update cart header
    updateCartHeader();
}

function updateCartHeader() {
    const guestSelect = document.getElementById('guest-select');
    const cartHeader = document.getElementById('cart-header');
    
    if (!cartHeader) return;
    
    // Remove existing guest badge
    const existingBadge = cartHeader.querySelector('.guest-badge');
    if (existingBadge) {
        existingBadge.remove();
    }
    
    if (guestSelect && guestSelect.value) {
        // Find the selected guest name from the guest cards
        const selectedCard = document.querySelector('.guest-card.border-primary');
        if (selectedCard) {
            const guestName = selectedCard.querySelector('strong')?.textContent || 'Selected Guest';
            const badge = document.createElement('span');
            badge.className = 'badge bg-info ms-2 guest-badge';
            badge.innerHTML = `<i class="fe fe-user me-1"></i>${guestName}`;
            cartHeader.appendChild(badge);
        }
    }
}

function updateOrderInfoDisplay() {
    const tableSelect = document.getElementById('table-select');
    const guestSelect = document.getElementById('guest-select');
    const orderInfoDisplay = document.getElementById('order-info-display');
    const orderTableName = document.getElementById('order-table-name');
    const orderGuestName = document.getElementById('order-guest-name');
    
    if (!orderInfoDisplay || !orderTableName || !orderGuestName) return;
    
    if (tableSelect && tableSelect.value) {
        const tableOption = tableSelect.options[tableSelect.selectedIndex];
        if (tableOption) {
            const tableName = tableOption.textContent.split(' - ')[0];
            orderTableName.textContent = tableName;
            
            if (guestSelect && guestSelect.value) {
                const guestOption = guestSelect.options[guestSelect.selectedIndex];
                if (guestOption) {
                    orderGuestName.textContent = guestOption.textContent;
                } else {
                    orderGuestName.textContent = 'Please Select a Guest';
                }
            } else {
                orderGuestName.textContent = 'Please Select a Guest';
            }
            
            orderInfoDisplay.style.display = 'block';
        }
    } else {
        orderInfoDisplay.style.display = 'none';
    }
}

function clearTableSelection() {
    document.getElementById('table-select').value = '';
    document.getElementById('guest-select').value = '';
    document.getElementById('selected-guest-indicator').style.display = 'none';
    document.getElementById('order-info-display').style.display = 'none';
    
    // Remove active class from guest cards
    document.querySelectorAll('.guest-card').forEach(card => {
        card.classList.remove('border-primary', 'bg-primary-transparent');
    });
    
    // Hide guests section
    document.getElementById('guests-section').style.display = 'none';
    document.getElementById('table-info').style.display = 'none';
    document.getElementById('no-table-message').style.display = 'block';
    document.getElementById('quick-guide').style.display = 'block';
    
    updateCartHeader();
}

// Add kitchen order items to cart
function addKitchenOrderToCart(orderId) {
    // Check if table and guest are selected
    const tableId = document.getElementById('table-select').value;
    const guestId = currentGuestId || document.getElementById('guest-select').value;
    
    if (!tableId || !guestId) {
        alert('Please select a table and guest first before adding kitchen items.');
        return;
    }
    
    // Fetch the kitchen order details
    fetch(`{{ route("admin.kitchen.live") }}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        // Find the order in ready orders
        const order = data.readyOrders?.find(o => o.id === orderId);
        if (!order) {
            alert('Kitchen order not found or no longer ready.');
            return;
        }
        
        // Add each item from the kitchen order to the cart
        if (order.items && order.items.length > 0) {
            order.items.forEach(item => {
                // Check if product exists in cart, if so increase quantity
                const existingItemIndex = cart.findIndex(cartItem => cartItem.product_id === item.product_id);
                
                if (existingItemIndex >= 0) {
                    // Item exists, increase quantity
                    cart[existingItemIndex].quantity += item.quantity;
                } else {
                    // Add new item to cart
                    cart.push({
                        product_id: item.product_id,
                        name: item.product_name || item.product?.name || 'Unknown',
                        unit_price: parseFloat(item.unit_price) || 0,
                        quantity: parseInt(item.quantity) || 1,
                        discount: parseFloat(item.discount) || 0,
                        max_stock: 9999
                    });
                }
            });
            
            // Update active guest order
            if (activeGuestOrders[guestId]) {
                activeGuestOrders[guestId].cart = JSON.parse(JSON.stringify(cart));
            }
            
            // Render cart and update totals
            renderCart();
            updateTotals();
            
            // Auto-save
            if (tableId && guestId) {
                savePendingOrder(tableId, guestId, false);
            }
            
            alert(`Added ${order.items.length} item(s) from kitchen order ${order.invoice_number} to cart!`);
        } else {
            alert('Kitchen order has no items.');
        }
    })
    .catch(error => {
        console.error('Error loading kitchen order:', error);
        alert('Error loading kitchen order. Please try again.');
    });
}

// Refresh kitchen orders
function refreshKitchenOrders() {
    fetch(`{{ route("admin.kitchen.live") }}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('kitchen-orders-container');
        if (!container) return;
        
        if (data.readyOrders && data.readyOrders.length > 0) {
            let html = '';
            data.readyOrders.forEach(order => {
                html += `
                    <div class="kitchen-order-item mb-2 p-2 border rounded" data-order-id="${order.id}">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <strong class="text-success">${order.invoice_number}</strong>
                                ${order.table ? `<br><small class="text-muted">Table: ${order.table.number}</small>` : ''}
                                ${order.table_guest ? `<br><small class="text-muted">Guest: ${order.table_guest.guest_name}</small>` : ''}
                            </div>
                            <button class="btn btn-sm btn-success" onclick="addKitchenOrderToCart(${order.id})" title="Add all items to cart">
                                <i class="fe fe-plus"></i> Add
                            </button>
                        </div>
                        <div class="small text-muted">
                            ${order.items.slice(0, 3).map(item => `
                                <div>${item.product_name || item.product?.name || 'Unknown'} × ${item.quantity}</div>
                            `).join('')}
                            ${order.items.length > 3 ? `<div>+ ${order.items.length - 3} more</div>` : ''}
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-center text-muted mb-0">No ready orders from kitchen</p>';
        }
    })
    .catch(error => {
        console.error('Error refreshing kitchen orders:', error);
    });
}

function toggleCustomerCategory() {
    try {
        const creditRadio = document.getElementById('category-credit');
        if (!creditRadio) return;
        
        const isCredit = creditRadio.checked;
        const container = document.getElementById('credit-customer-select-container');
        const customerSelect = document.getElementById('customer-select');
        
        if (container) container.style.display = isCredit ? 'block' : 'none';
        
        if (!isCredit) {
            // Clear selection if switching back to walk-in
            if (customerSelect) {
                $(customerSelect).val('').trigger('change');
            }
        } else {
            // If switching to credit but no customer selected, ensure select2 is open or focused
            if (customerSelect && !customerSelect.value) {
                setTimeout(() => {
                    try {
                        if (typeof $.fn.select2 !== 'undefined') {
                            $(customerSelect).select2('open');
                        }
                    } catch (e) {
                         console.warn('Select2 open failed:', e);
                    }
                }, 100);
            }
        }
    } catch (error) {
        console.error('Error in toggleCustomerCategory:', error);
        // We don't alert here to avoid annoying the user if it's a minor UI issue
    }
}

function showManageCustomerModal() {
    const customerId = document.getElementById('manage-customer-btn').getAttribute('data-customer-id');
    const select = document.getElementById('customer-select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (!customerId) return;
    
    // Set basic info
    document.getElementById('manage-customer-id').value = customerId;
    document.getElementById('manage-customer-title').textContent = "Active Credit for " + selectedOption.text.split(' (')[0];
    
    // Check credit status from text
    const isCreditEnabled = selectedOption.text.includes('Credit Available');
    document.getElementById('manage-credit-enabled').checked = isCreditEnabled;
    
    // Extract current limit
    const currentLimit = selectedOption.getAttribute('data-credit-limit') || "500000";
    document.getElementById('manage-credit-limit').value = formatCurrency(parseFloat(currentLimit));
    
    // Reset message
    const msg = document.getElementById('manage-customer-msg');
    if (msg) {
        msg.classList.add('d-none');
    }
    
    const modalElement = document.getElementById('manageCustomerModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function saveCustomerCredit() {
    const customerId = document.getElementById('manage-customer-id').value;
    const creditLimit = document.getElementById('manage-credit-limit').value.replace(/,/g, '');
    const creditEnabled = document.getElementById('manage-credit-enabled').checked ? 1 : 0;
    const btn = document.getElementById('save-customer-btn');
    const msg = document.getElementById('manage-customer-msg');
    
    if (!btn) return;
    
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    
    fetch(`/admin/customers/${customerId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            credit_limit: creditLimit,
            credit_enabled: creditEnabled,
            // Fetch name/phone from DOM as backup for validation
            name: document.getElementById('manage-customer-title').textContent.replace("Active Credit for ", ""),
            phone: "0000000000", // Required in validation but will be kept as-is if possible
            is_ajax_pos: true 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            msg.textContent = "Customer credit activated!";
            msg.className = "alert alert-success mt-3";
            msg.classList.remove('d-none');
            
            // Update current dropdown option immediately (avoid stale data-credit-enabled)
            const opt = document.querySelector(`#customer-select option[value="${customerId}"]`);
            if (opt) {
                opt.setAttribute('data-credit-enabled', creditEnabled ? 'true' : 'false');
                opt.setAttribute('data-credit-limit', creditLimit || '0');
            }
            
            // Reload page to refresh customer dropdown
            setTimeout(() => {
                location.reload();
            }, 800);
        } else {
            msg.textContent = (data.errors ? Object.values(data.errors)[0][0] : data.message) || "Failed to update customer.";
            msg.className = "alert alert-danger mt-3";
            msg.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        msg.textContent = "An error occurred. Check console.";
        msg.className = "alert alert-danger mt-3";
        msg.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

</script>
@endpush





