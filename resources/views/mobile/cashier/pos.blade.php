@extends('mobile.layout')

@section('title', 'Cashier POS')

@section('styles')
<style>
    .ready-banner {
        display: none;
        background: linear-gradient(135deg, rgba(34,197,94,0.2), rgba(14,165,233,0.15));
        border: 1px solid rgba(34,197,94,0.35);
        border-radius: 14px;
        padding: 12px;
        margin-bottom: 12px;
    }
    .ready-banner.show { display: block; }
    .search {
        width: 100%;
        border: 1px solid var(--border);
        background: var(--bg-2);
        color: var(--text);
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 12px;
    }
    .cats {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 8px;
        margin-bottom: 12px;
        scrollbar-width: none;
    }
    .cats::-webkit-scrollbar { display: none; }
    .cat-chip {
        flex: 0 0 auto;
        border: 1px solid var(--border);
        background: var(--bg-2);
        color: var(--muted);
        border-radius: 999px;
        padding: 8px 14px;
        font-weight: 700;
        font-size: 0.85rem;
        white-space: nowrap;
    }
    .cat-chip.active {
        background: rgba(14,165,233,0.18);
        color: #7dd3fc;
        border-color: rgba(14,165,233,0.45);
    }
    .products {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        padding-bottom: 88px;
    }
    .product {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 12px;
        text-align: left;
        color: var(--text);
        min-height: 110px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .product strong { font-size: 0.92rem; line-height: 1.25; }
    .product .price { color: #7dd3fc; font-weight: 800; margin-top: 8px; }
    .product .stock { color: var(--muted); font-size: 0.75rem; }
    .cart-bar {
        position: fixed;
        left: 12px;
        right: 12px;
        bottom: calc(var(--nav-h) + var(--safe-bottom) + 10px);
        z-index: 45;
        display: flex;
        gap: 10px;
        align-items: center;
        background: linear-gradient(135deg, #0284c7, #0369a1);
        color: #fff;
        border-radius: 16px;
        padding: 12px 14px;
        box-shadow: 0 10px 30px rgba(2, 132, 199, 0.35);
    }
    .cart-bar .meta { flex: 1; }
    .cart-bar .count {
        display: inline-flex;
        min-width: 24px;
        height: 24px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(255,255,255,0.2);
        font-weight: 800;
        margin-right: 8px;
    }
    .sheet {
        position: fixed;
        inset: 0;
        z-index: 60;
        display: none;
    }
    .sheet.open { display: block; }
    .sheet-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(2, 6, 23, 0.7);
    }
    .sheet-panel {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        max-height: 88dvh;
        overflow: auto;
        background: #0f172a;
        border-radius: 20px 20px 0 0;
        border: 1px solid var(--border);
        padding: 16px 16px calc(20px + var(--safe-bottom));
    }
    .sheet-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    .cart-item {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 8px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
    }
    .qty-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .qty-row button {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: var(--bg-2);
        color: var(--text);
        font-weight: 800;
    }
    .field {
        margin-bottom: 12px;
    }
    .field label {
        display: block;
        margin-bottom: 6px;
        color: var(--muted);
        font-size: 0.82rem;
        font-weight: 700;
    }
    .field select, .field input {
        width: 100%;
        border: 1px solid var(--border);
        background: var(--bg-2);
        color: var(--text);
        border-radius: 12px;
        padding: 12px;
    }
    .totals {
        background: var(--bg-2);
        border-radius: 14px;
        padding: 12px;
        margin: 12px 0;
    }
    .totals-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
    }
    .totals-row.total {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid var(--border);
        font-size: 1.1rem;
        font-weight: 800;
    }
    .ready-list { margin-top: 8px; display: grid; gap: 8px; }
    .ready-item {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        align-items: center;
        background: rgba(15,23,42,0.55);
        border-radius: 12px;
        padding: 10px;
    }
</style>
@endsection

@section('top')
<div>
    <h1>Cashier POS</h1>
    <div class="sub">{{ auth()->user()->name }}</div>
</div>
<button type="button" class="icon-btn" onclick="openCart()" title="Cart">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/><path d="M3 3h2l2.2 11.2a2 2 0 002 1.6h8.5a2 2 0 002-1.6L21 7H7"/></svg>
</button>
@endsection

@section('content')
<div id="ready-banner" class="ready-banner {{ $readyKitchenOrders->count() ? 'show' : '' }}">
    <strong id="ready-count">{{ $readyKitchenOrders->count() }} ready order(s)</strong>
    <div class="muted" style="font-size:0.82rem;margin:4px 0 8px;">Food is ready to serve</div>
    <div class="ready-list" id="ready-list">
        @foreach($readyKitchenOrders as $order)
            <div class="ready-item" data-id="{{ $order->id }}">
                <div>
                    <strong>#{{ $order->invoice_number }}</strong>
                    <div class="muted" style="font-size:0.78rem;">
                        {{ $order->table->number ?? 'Walk-in' }}
                        @if($order->tableGuest) · {{ $order->tableGuest->guest_name }} @endif
                    </div>
                </div>
                <button class="btn btn-success" style="padding:8px 10px;" onclick="markServed({{ $order->id }})">Served</button>
            </div>
        @endforeach
    </div>
</div>

<input type="search" class="search" id="search" placeholder="Search products..." oninput="filterProducts()">

<div class="cats" id="cats">
    <button type="button" class="cat-chip active" data-id="">All</button>
    @foreach($categories as $category)
        <button type="button" class="cat-chip" data-id="{{ $category->id }}">{{ $category->name }}</button>
    @endforeach
</div>

<div class="products" id="products">
    @foreach($categories as $category)
        @foreach($category->products as $product)
            <button type="button"
                class="product"
                data-id="{{ $product->id }}"
                data-name="{{ $product->name }}"
                data-price="{{ $product->selling_price }}"
                data-stock="{{ $product->stock_quantity }}"
                data-category="{{ $category->id }}"
                onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ (float) $product->selling_price }}, {{ (int) $product->stock_quantity }})">
                <div>
                    <strong>{{ $product->name }}</strong>
                    <div class="stock">Stock: {{ $product->stock_quantity }}</div>
                </div>
                <div class="price">₦{{ number_format((float) $product->selling_price, 2) }}</div>
            </button>
        @endforeach
    @endforeach
</div>

<div class="cart-bar" id="cart-bar" style="display:none;" onclick="openCart()">
    <div class="meta">
        <span class="count" id="cart-count">0</span>
        <strong id="cart-total-label">₦0.00</strong>
    </div>
    <div>View cart</div>
</div>

<div class="sheet" id="cart-sheet">
    <div class="sheet-backdrop" onclick="closeCart()"></div>
    <div class="sheet-panel">
        <div class="sheet-head">
            <h2 style="margin:0;font-size:1.1rem;">Cart</h2>
            <button class="icon-btn" onclick="closeCart()">✕</button>
        </div>

        <div id="cart-items"></div>

        <div class="field">
            <label>Table (optional)</label>
            <select id="table-id">
                <option value="">Walk-in / No table</option>
                @foreach($tables as $table)
                    <option value="{{ $table->id }}">{{ $table->number }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label>Payment method</label>
            <select id="payment-method" onchange="toggleCredit()">
                <option value="cash">Cash</option>
                <option value="transfer">Transfer</option>
                <option value="pos">POS</option>
                <option value="credit">Credit</option>
            </select>
        </div>

        <div class="field" id="credit-field" style="display:none;">
            <label>Credit customer</label>
            <select id="customer-id">
                <option value="">-- Choose customer --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }} (₦{{ number_format($customer->getAvailableCredit(), 2) }})</option>
                @endforeach
            </select>
        </div>

        <div class="field" id="amount-field">
            <label>Amount paid</label>
            <input type="number" id="amount-paid" min="0" step="0.01" placeholder="0.00">
        </div>

        <div class="totals">
            <div class="totals-row"><span>Subtotal</span><span id="subtotal-display">₦0.00</span></div>
            <div class="totals-row total"><span>Total</span><span id="total-display">₦0.00</span></div>
        </div>

        <button class="btn btn-primary btn-block" id="checkout-btn" onclick="checkout()">Complete Sale</button>
        <button class="btn btn-block" style="margin-top:8px;" onclick="clearCart()">Clear cart</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
const cart = new Map();
let activeCategory = '';

document.querySelectorAll('.cat-chip').forEach(chip => {
    chip.addEventListener('click', () => {
        document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        activeCategory = chip.dataset.id || '';
        filterProducts();
    });
});

function money(n) {
    return '₦' + Number(n || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function filterProducts() {
    const q = (document.getElementById('search').value || '').toLowerCase().trim();
    document.querySelectorAll('.product').forEach(el => {
        const matchCat = !activeCategory || el.dataset.category === activeCategory;
        const matchQ = !q || el.dataset.name.toLowerCase().includes(q);
        el.style.display = (matchCat && matchQ) ? '' : 'none';
    });
}

function addToCart(id, name, price, stock) {
    const existing = cart.get(id);
    const qty = (existing?.qty || 0) + 1;
    if (qty > stock) {
        showToast('Not enough stock', true);
        return;
    }
    cart.set(id, { id, name, price, stock, qty });
    renderCart();
    showToast(name + ' added');
}

function changeQty(id, delta) {
    const item = cart.get(id);
    if (!item) return;
    const qty = item.qty + delta;
    if (qty <= 0) {
        cart.delete(id);
    } else if (qty > item.stock) {
        showToast('Not enough stock', true);
        return;
    } else {
        item.qty = qty;
        cart.set(id, item);
    }
    renderCart();
}

function cartTotal() {
    let total = 0;
    cart.forEach(item => total += item.price * item.qty);
    return total;
}

function renderCart() {
    const itemsEl = document.getElementById('cart-items');
    const count = [...cart.values()].reduce((s, i) => s + i.qty, 0);
    const total = cartTotal();

    document.getElementById('cart-count').textContent = count;
    document.getElementById('cart-total-label').textContent = money(total);
    document.getElementById('cart-bar').style.display = count ? 'flex' : 'none';
    document.getElementById('subtotal-display').textContent = money(total);
    document.getElementById('total-display').textContent = money(total);

    const amountPaid = document.getElementById('amount-paid');
    if (document.getElementById('payment-method').value !== 'credit') {
        amountPaid.value = total.toFixed(2);
    }

    if (!count) {
        itemsEl.innerHTML = '<p class="muted">Cart is empty.</p>';
        return;
    }

    itemsEl.innerHTML = [...cart.values()].map(item => `
        <div class="cart-item">
            <div>
                <strong>${item.name}</strong>
                <div class="muted">${money(item.price)} each</div>
            </div>
            <div class="qty-row">
                <button type="button" onclick="changeQty(${item.id}, -1)">−</button>
                <strong>${item.qty}</strong>
                <button type="button" onclick="changeQty(${item.id}, 1)">+</button>
            </div>
        </div>
    `).join('');
}

function openCart() {
    renderCart();
    document.getElementById('cart-sheet').classList.add('open');
}
function closeCart() {
    document.getElementById('cart-sheet').classList.remove('open');
}
function clearCart() {
    cart.clear();
    renderCart();
}
function toggleCredit() {
    const isCredit = document.getElementById('payment-method').value === 'credit';
    document.getElementById('credit-field').style.display = isCredit ? 'block' : 'none';
    document.getElementById('amount-field').style.display = isCredit ? 'none' : 'block';
    if (!isCredit) {
        document.getElementById('amount-paid').value = cartTotal().toFixed(2);
    }
}

async function checkout() {
    if (!cart.size) {
        showToast('Cart is empty', true);
        return;
    }

    const paymentMethod = document.getElementById('payment-method').value;
    const customerId = document.getElementById('customer-id').value;
    const tableId = document.getElementById('table-id').value;
    const total = cartTotal();
    let amountPaid = parseFloat(document.getElementById('amount-paid').value || '0');

    if (paymentMethod === 'credit' && !customerId) {
        showToast('Select a credit customer', true);
        return;
    }
    if (paymentMethod !== 'credit' && amountPaid < total) {
        showToast('Amount paid is less than total', true);
        return;
    }
    if (paymentMethod === 'credit') amountPaid = 0;

    const btn = document.getElementById('checkout-btn');
    btn.disabled = true;
    btn.textContent = 'Processing...';

    try {
        const res = await fetch(`{{ route('admin.pos.store') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                items: [...cart.values()].map(item => ({
                    product_id: item.id,
                    quantity: item.qty,
                    unit_price: item.price,
                    discount: 0,
                })),
                payment_method: paymentMethod,
                amount_paid: amountPaid,
                customer_id: customerId || null,
                table_id: tableId || null,
                discount: 0,
                vat_rate: 0,
            }),
        });

        const data = await res.json();
        if (!res.ok || data.error) {
            throw new Error(data.error || data.message || 'Sale failed');
        }

        showToast('Sale completed');
        clearCart();
        closeCart();
        if (data.sale && data.sale.id) {
            window.location.href = `{{ url('/admin/pos') }}/${data.sale.id}`;
        } else {
            setTimeout(() => location.reload(), 600);
        }
    } catch (e) {
        showToast(e.message || 'Sale failed', true);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Complete Sale';
    }
}

async function markServed(orderId) {
    try {
        const res = await fetch(`{{ url('/admin/kitchen') }}/${orderId}/served`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await res.json();
        if (!res.ok || data.error) throw new Error(data.error || 'Failed');
        document.querySelector(`.ready-item[data-id="${orderId}"]`)?.remove();
        const left = document.querySelectorAll('#ready-list .ready-item').length;
        document.getElementById('ready-count').textContent = left + ' ready order(s)';
        if (!left) document.getElementById('ready-banner').classList.remove('show');
        showToast('Marked as served');
    } catch (e) {
        showToast(e.message || 'Failed', true);
    }
}

renderCart();
</script>
@endsection
