@extends('layouts.admin')

@section('title', 'Record Owner Purchase')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.owner-purchases.index') }}">Owner Purchases</a></li>
<li class="breadcrumb-item active" aria-current="page">Record Purchase</li>
@endsection

@push('styles')
<style>
    .product-card {
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
    }
    .product-card:hover {
        border-color: #5e72e4;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(94, 114, 228, 0.15);
    }
    .product-grid {
        max-height: 400px;
        overflow-y: auto;
    }
    .cart-item {
        border-bottom: 1px solid #e9ecef;
        padding: 10px 0;
    }
    .cart-item:last-child {
        border-bottom: none;
    }
    #cart-items {
        max-height: 300px;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title mb-0">Record Owner Purchase</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.owner-purchases.store') }}" id="purchase-form">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" 
                               value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                        @error('purchase_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="3" required>{{ old('description') }}</textarea>
                        <small class="text-muted">Brief description of what was purchased</small>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Items Description</label>
                        <textarea name="items_description" id="items_description" class="form-control @error('items_description') is-invalid @enderror" 
                                  rows="4">{{ old('items_description') }}</textarea>
                        <small class="text-muted">Will be auto-filled from cart items</small>
                        @error('items_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount (₦) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" 
                                   step="0.01" min="0.01" value="{{ old('amount') }}" required>
                            <small class="text-muted">Will be auto-calculated from cart total</small>
                            @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vendor/Store</label>
                            <input type="text" name="vendor" class="form-control @error('vendor') is-invalid @enderror" 
                                   value="{{ old('vendor') }}" placeholder="Where was this purchased?">
                            @error('vendor')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="pos" {{ old('payment_method') == 'pos' ? 'selected' : '' }}>POS</option>
                            <option value="credit" {{ old('payment_method') == 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                        @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                  rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-2"></i> Record Purchase
                        </button>
                        <a href="{{ route('admin.owner-purchases.index') }}" class="btn btn-secondary">
                            <i class="fe fe-x me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Selection -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Select Products (Optional)</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" id="product-search" class="form-control" placeholder="Search products...">
                </div>
                <div class="mb-3 d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-primary btn-sm category-filter active" data-category="all">All</button>
                    @foreach($categories as $category)
                    <button class="btn btn-outline-primary btn-sm category-filter" data-category="{{ $category->id }}">{{ $category->name }}</button>
                    @endforeach
                </div>
                <div class="product-grid" id="products-container">
                    @foreach($categories as $category)
                        @foreach($category->products as $product)
                        <div class="product-card border" data-product-id="{{ $product->id }}" 
                             data-product-name="{{ $product->name }}" 
                             data-product-price="{{ $product->selling_price }}"
                             data-category-id="{{ $category->id }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $product->name }}</strong>
                                    <br>
                                    <small class="text-muted">₦{{ number_format($product->selling_price, 2) }}</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addProductToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->selling_price }})">
                                    <i class="fe fe-plus"></i> Add
                                </button>
                            </div>
                        </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <!-- Cart -->
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Cart</h4>
            </div>
            <div class="card-body">
                <div id="cart-items">
                    <p class="text-muted text-center">No items in cart</p>
                </div>
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Total:</strong>
                        <strong id="cart-total">₦0.00</strong>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger w-100" onclick="clearCart()" id="clear-cart-btn" style="display: none;">
                        <i class="fe fe-trash me-1"></i> Clear Cart
                    </button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Note</h4>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Owner purchases are <strong>NOT included</strong> in profit/loss calculations. 
                    These are personal purchases made by the owner and tracked separately.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = [];

function addProductToCart(productId, productName, productPrice) {
    const existingItem = cart.find(item => item.product_id === productId);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            product_id: productId,
            name: productName,
            price: productPrice,
            quantity: 1
        });
    }
    
    renderCart();
    updateFormFields();
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.product_id !== productId);
    renderCart();
    updateFormFields();
}

function updateQuantity(productId, change) {
    const item = cart.find(item => item.product_id === productId);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(productId);
        } else {
            renderCart();
            updateFormFields();
        }
    }
}

function renderCart() {
    const cartItems = document.getElementById('cart-items');
    const clearBtn = document.getElementById('clear-cart-btn');
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="text-muted text-center">No items in cart</p>';
        clearBtn.style.display = 'none';
    } else {
        let html = '';
        cart.forEach(item => {
            const total = item.price * item.quantity;
            html += `
                <div class="cart-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>${item.name}</strong>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeFromCart(${item.product_id})">
                            <i class="fe fe-x"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">₦${parseFloat(item.price).toLocaleString('en-NG', {minimumFractionDigits: 2})} × </small>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.product_id}, -1)">-</button>
                            <span class="mx-2">${item.quantity}</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.product_id}, 1)">+</button>
                        </div>
                        <strong>₦${total.toLocaleString('en-NG', {minimumFractionDigits: 2})}</strong>
                    </div>
                </div>
            `;
        });
        cartItems.innerHTML = html;
        clearBtn.style.display = 'block';
    }
    
    updateCartTotal();
}

function updateCartTotal() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.getElementById('cart-total').textContent = '₦' + total.toLocaleString('en-NG', {minimumFractionDigits: 2});
}

function updateFormFields() {
    // Update items description
    const itemsDesc = cart.map(item => `${item.name} × ${item.quantity}`).join('\n');
    document.getElementById('items_description').value = itemsDesc;
    
    // Update amount
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.getElementById('amount').value = total.toFixed(2);
}

function clearCart() {
    if (confirm('Clear all items from cart?')) {
        cart = [];
        renderCart();
        updateFormFields();
    }
}

// Product search
document.getElementById('product-search').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(card => {
        const name = card.getAttribute('data-product-name').toLowerCase();
        card.style.display = name.includes(search) ? 'block' : 'none';
    });
});

// Category filter
document.querySelectorAll('.category-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.category-filter').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const categoryId = this.getAttribute('data-category');
        document.querySelectorAll('.product-card').forEach(card => {
            if (categoryId === 'all') {
                card.style.display = 'block';
            } else {
                const cardCategoryId = card.getAttribute('data-category-id');
                card.style.display = cardCategoryId === categoryId ? 'block' : 'none';
            }
        });
    });
});
</script>
@endpush
















