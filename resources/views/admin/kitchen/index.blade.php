@extends('layouts.admin')

@section('title', 'Kitchen Display')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Kitchen</li>
@endsection

@push('styles')
<style>
    .order-card {
        transition: all 0.3s ease;
        border-left: 4px solid #ffc107;
    }
    .order-card.preparing {
        border-left-color: #17a2b8;
        background: linear-gradient(to right, rgba(23, 162, 184, 0.05), transparent);
    }
    .order-card.ready {
        border-left-color: #28a745;
        background: linear-gradient(to right, rgba(40, 167, 69, 0.1), transparent);
    }
    .order-item {
        padding: 8px 0;
        border-bottom: 1px dashed #e9ecef;
    }
    .order-item:last-child {
        border-bottom: none;
    }
    .qty-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #5e72e4;
        color: white;
        font-weight: bold;
        margin-right: 10px;
    }
    .time-ago {
        font-size: 12px;
        color: #8898aa;
    }
    .blink {
        animation: blink-animation 1s steps(5, start) infinite;
    }
    @keyframes blink-animation {
        to { visibility: hidden; }
    }
    .stat-card {
        border-radius: 10px;
        text-align: center;
        padding: 20px;
    }
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
    }
    .new-order-alert {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        display: none;
    }
    .orders-container {
        max-height: calc(100vh - 150px);
        overflow-y: auto;
        padding-right: 10px;
    }
    .orders-container::-webkit-scrollbar {
        width: 8px;
    }
    .orders-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .orders-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    .orders-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endpush

@section('actions')
<button type="button" class="btn btn-outline-primary" onclick="refreshOrders()">
    <i class="fe fe-refresh-cw"></i> Refresh
</button>
<button type="button" class="btn btn-outline-success" onclick="printAllPendingOrders()">
    <i class="fe fe-printer"></i> Print All Pending
</button>
<a href="{{ route('admin.kitchen.print-all') }}" class="btn btn-outline-info" target="_blank">
    <i class="fe fe-file-text"></i> Print All (Page)
</a>
@endsection

@section('content')

<!-- New Order Alert -->
<div class="alert alert-info new-order-alert" id="new-order-alert">
    <i class="fe fe-bell me-2 blink"></i> New order received!
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-warning-transparent">
            <div class="stat-number text-warning" id="stat-pending">{{ $stats['pending'] }}</div>
            <div class="text-muted">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info-transparent">
            <div class="stat-number text-info" id="stat-preparing">{{ $stats['preparing'] }}</div>
            <div class="text-muted">Preparing</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success-transparent">
            <div class="stat-number text-success" id="stat-ready">{{ $stats['ready'] }}</div>
            <div class="text-muted">Ready</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-secondary-transparent">
            <div class="stat-number text-secondary" id="stat-served">{{ $stats['served'] }}</div>
            <div class="text-muted">Served Today</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pending Orders -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fe fe-clock text-warning me-2"></i>
                    Pending Orders
                    <span class="badge bg-warning ms-2" id="pending-count">{{ $pendingOrders->count() }}</span>
                </h3>
                <button type="button" class="btn btn-sm btn-success" onclick="printAllPendingOrders()" id="print-pending-btn">
                    <i class="fe fe-printer me-1"></i> Print All
                </button>
            </div>
            <div class="card-body orders-container" id="pending-orders-container">
                @forelse($pendingOrders as $order)
                <div class="card order-card mb-3 {{ $order->kitchen_status }}" id="order-{{ $order->id }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">
                                    <span class="fw-bold">{{ $order->invoice_number }}</span>
                                    @if($order->kitchen_status === 'preparing')
                                    <span class="badge bg-info ms-2">Preparing</span>
                                    @else
                                    <span class="badge bg-warning ms-2">New</span>
                                    @endif
                                </h5>
                                <div class="time-ago">
                                    <i class="fe fe-clock me-1"></i>
                                    {{ $order->created_at->diffForHumans() }}
                                    @if($order->customer)
                                    <span class="ms-2"><i class="fe fe-user me-1"></i>{{ $order->customer->name }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-success" onclick="printSingleOrder({{ $order->id }})" title="Print Order">
                                    <i class="fe fe-printer"></i> Print
                                </button>
                                @if($order->kitchen_status === 'pending')
                                <button class="btn btn-sm btn-info" onclick="markPreparing({{ $order->id }})" title="Start Preparing">
                                    <i class="fe fe-play"></i> Start
                                </button>
                                @endif
                                <button class="btn btn-sm btn-primary" onclick="markReady({{ $order->id }})" title="Mark Ready">
                                    <i class="fe fe-check"></i> Ready
                                </button>
                            </div>
                        </div>
                        
                        <div class="order-items">
                            @foreach($order->items as $item)
                            <div class="order-item d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="qty-badge">{{ $item->quantity }}</span>
                                    <span class="fw-semibold">{{ $item->product_name }}</span>
                                </div>
                                @if($item->product && $item->product->preparation_time)
                                <span class="badge bg-info ms-2" title="Preparation time">
                                    <i class="fe fe-clock me-1"></i>{{ $item->product->preparation_time }} min
                                </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        
                        @if($order->notes)
                        <div class="mt-3 p-2 bg-light rounded">
                            <small class="text-muted"><i class="fe fe-message-square me-1"></i> {{ $order->notes }}</small>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-5" id="no-pending-orders">
                    <i class="fe fe-check-circle text-success" style="font-size: 48px;"></i>
                    <p class="mt-3 text-muted">All orders have been prepared!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Ready Orders -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-success-transparent">
                <h3 class="card-title mb-0 text-success">
                    <i class="fe fe-check-circle me-2"></i>
                    Ready for Pickup
                    <span class="badge bg-success ms-2" id="ready-count">{{ $readyOrders->count() }}</span>
                </h3>
            </div>
            <div class="card-body orders-container" id="ready-orders-container">
                @forelse($readyOrders as $order)
                <div class="card order-card ready mb-3" id="ready-order-{{ $order->id }}">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 fw-bold">{{ $order->invoice_number }}</h6>
                                <small class="text-muted">
                                    Ready {{ $order->kitchen_ready_at->diffForHumans() }}
                                    @if($order->customer)
                                    <br><i class="fe fe-user"></i> {{ $order->customer->name }}
                                    @endif
                                </small>
                            </div>
                            <button class="btn btn-sm btn-outline-success" onclick="markServed({{ $order->id }})" title="Mark as Served">
                                <i class="fe fe-check"></i> Served
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4" id="no-ready-orders">
                    <p class="text-muted mb-0">No orders ready for pickup</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let lastOrderCount = {{ $pendingOrders->count() }};
let refreshInterval;

// Start auto-refresh
function startAutoRefresh() {
    refreshInterval = setInterval(refreshOrders, 10000); // Refresh every 10 seconds
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

function refreshOrders() {
    fetch('{{ route("admin.kitchen.live") }}')
        .then(response => response.json())
        .then(data => {
            // Check for new orders
            if (data.pendingOrders.length > lastOrderCount) {
                showNewOrderAlert();
                playNotificationSound();
            }
            lastOrderCount = data.pendingOrders.length;

            // Update stats
            document.getElementById('stat-pending').textContent = data.stats.pending;
            document.getElementById('stat-preparing').textContent = data.stats.preparing;
            document.getElementById('stat-ready').textContent = data.stats.ready;
            document.getElementById('stat-served').textContent = data.stats.served;
            document.getElementById('pending-count').textContent = data.pendingOrders.length;
            document.getElementById('ready-count').textContent = data.readyOrders.length;

            // Update pending orders
            updatePendingOrders(data.pendingOrders);
            
            // Update ready orders
            updateReadyOrders(data.readyOrders);
        })
        .catch(error => console.error('Error refreshing orders:', error));
}

function updatePendingOrders(orders) {
    const container = document.getElementById('pending-orders-container');
    const printBtn = document.getElementById('print-pending-btn');
    
    // Enable/disable print button based on orders
    if (printBtn) {
        printBtn.disabled = orders.length === 0;
    }
    
    if (orders.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5" id="no-pending-orders">
                <i class="fe fe-check-circle text-success" style="font-size: 48px;"></i>
                <p class="mt-3 text-muted">All orders have been prepared!</p>
            </div>
        `;
        return;
    }

    let html = '';
    orders.forEach(order => {
        const statusBadge = order.kitchen_status === 'preparing' 
            ? '<span class="badge bg-info ms-2">Preparing</span>'
            : '<span class="badge bg-warning ms-2">New</span>';
        
        const startBtn = order.kitchen_status === 'pending'
            ? `<button class="btn btn-sm btn-info" onclick="markPreparing(${order.id})" title="Start Preparing">
                    <i class="fe fe-play"></i> Start
               </button>`
            : '';

        let itemsHtml = '';
        order.items.forEach(item => {
            const prepTime = item.product && item.product.preparation_time 
                ? `<span class="badge bg-info ms-2" title="Preparation time"><i class="fe fe-clock me-1"></i>${item.product.preparation_time} min</span>`
                : '';
            itemsHtml += `
                <div class="order-item d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span class="qty-badge">${item.quantity}</span>
                        <span class="fw-semibold">${item.product_name}</span>
                    </div>
                    ${prepTime}
                </div>
            `;
        });

        const customerInfo = order.customer 
            ? `<span class="ms-2"><i class="fe fe-user me-1"></i>${order.customer.name}</span>` 
            : '';

        const notes = order.notes
            ? `<div class="mt-3 p-2 bg-light rounded">
                    <small class="text-muted"><i class="fe fe-message-square me-1"></i> ${order.notes}</small>
               </div>`
            : '';

        html += `
            <div class="card order-card mb-3 ${order.kitchen_status}" id="order-${order.id}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">
                                <span class="fw-bold">${order.invoice_number}</span>
                                ${statusBadge}
                            </h5>
                            <div class="time-ago">
                                <i class="fe fe-clock me-1"></i>
                                ${formatTimeAgo(order.created_at)}
                                ${customerInfo}
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-success" onclick="printSingleOrder(${order.id})" title="Print Order">
                                <i class="fe fe-printer"></i> Print
                            </button>
                            ${startBtn}
                            <button class="btn btn-sm btn-primary" onclick="markReady(${order.id})" title="Mark Ready">
                                <i class="fe fe-check"></i> Ready
                            </button>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        ${itemsHtml}
                    </div>
                    ${notes}
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function updateReadyOrders(orders) {
    const container = document.getElementById('ready-orders-container');
    
    if (orders.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4" id="no-ready-orders">
                <p class="text-muted mb-0">No orders ready for pickup</p>
            </div>
        `;
        return;
    }

    let html = '';
    orders.forEach(order => {
        const customerInfo = order.customer 
            ? `<br><i class="fe fe-user"></i> ${order.customer.name}` 
            : '';

        html += `
            <div class="card order-card ready mb-3" id="ready-order-${order.id}">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold">${order.invoice_number}</h6>
                            <small class="text-muted">
                                Ready ${formatTimeAgo(order.kitchen_ready_at)}
                                ${customerInfo}
                            </small>
                        </div>
                        <button class="btn btn-sm btn-outline-success" onclick="markServed(${order.id})" title="Mark as Served">
                            <i class="fe fe-check"></i> Served
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
    return Math.floor(seconds / 86400) + ' days ago';
}

function showNewOrderAlert() {
    const alert = document.getElementById('new-order-alert');
    alert.style.display = 'block';
    setTimeout(() => {
        alert.style.display = 'none';
    }, 5000);
}

function playNotificationSound() {
    // Create a simple beep sound
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    oscillator.frequency.value = 800;
    oscillator.type = 'sine';
    gainNode.gain.value = 0.3;
    
    oscillator.start();
    setTimeout(() => oscillator.stop(), 200);
}

function markPreparing(orderId) {
    fetch(`/admin/kitchen/${orderId}/preparing`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            refreshOrders();
        } else {
            alert(data.error || 'Failed to update order status.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update order status.');
    });
}

function markReady(orderId) {
    fetch(`/admin/kitchen/${orderId}/ready`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Animate the card
            const card = document.getElementById(`order-${orderId}`);
            if (card) {
                card.style.transition = 'all 0.5s ease';
                card.style.transform = 'translateX(100%)';
                card.style.opacity = '0';
                setTimeout(() => refreshOrders(), 500);
            } else {
                refreshOrders();
            }
        } else {
            alert(data.error || 'Failed to update order status.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update order status.');
    });
}

function markServed(orderId) {
    fetch(`/admin/kitchen/${orderId}/served`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const card = document.getElementById(`ready-order-${orderId}`);
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.transform = 'scale(0.8)';
                card.style.opacity = '0';
                setTimeout(() => refreshOrders(), 300);
            } else {
                refreshOrders();
            }
        } else {
            alert(data.error || 'Failed to update order status.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update order status.');
    });
}

// Print single order directly (works like POS print) - styled
function printSingleOrder(orderId) {
    // Fetch order data
    fetch(`/admin/kitchen/${orderId}/print`, {
        method: 'GET',
        headers: {
            'Accept': 'text/html',
        }
    })
    .then(response => response.text())
    .then(html => {
        // Extract the order content from the HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Get order data from the page
        const invoiceNumber = doc.querySelector('.invoice')?.textContent || 'N/A';
        const time = doc.querySelector('.time')?.textContent || '';
        const date = doc.querySelector('.order-info div:last-child')?.textContent || '';
        const customer = doc.querySelector('.customer-name')?.textContent || '';
        const customerLabel = doc.querySelector('.customer-label')?.textContent || '';
        const items = doc.querySelectorAll('.item');
        const notes = doc.querySelector('.notes-content')?.textContent || '';
        const notesLabel = doc.querySelector('.notes-label')?.textContent || '';
        const cashier = doc.querySelector('.cashier')?.textContent || '';
        const footer = doc.querySelector('.footer')?.textContent || '';
        
        // Build items HTML
        let itemsHtml = '';
        items.forEach(item => {
            const qty = item.querySelector('.item-qty')?.textContent || '1';
            const name = item.querySelector('.item-name')?.textContent || '';
            itemsHtml += `
                <div class="item">
                    <span class="item-qty">${qty}</span>
                    <span class="item-separator">|</span>
                    <span class="item-name">${name}</span>
                </div>
            `;
        });
        
        // Create styled print content
        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Kitchen Order</title>
                <meta charset="UTF-8">
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
                        font-size: 12px;
                        line-height: 1.3;
                        padding: 0;
                        background: #fff;
                        max-width: 300px;
                        margin: 0;
                    }
                    .order { 
                        border: 2px solid #5e72e4; 
                        padding: 8px;
                        page-break-inside: avoid;
                        border-radius: 6px;
                        background: #fff;
                        margin: 0;
                    }
                    .header { 
                        text-align: center; 
                        border-bottom: 2px solid #5e72e4; 
                        padding-bottom: 6px; 
                        margin-bottom: 6px; 
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 10px;
                        border-radius: 4px 4px 0 0;
                    }
                    .header h2 { 
                        font-size: 14px; 
                        margin-bottom: 2px; 
                        color: white; 
                        font-weight: 700;
                        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
                    }
                    .order-info { 
                        margin-bottom: 6px; 
                        font-size: 10px; 
                        padding: 5px; 
                        background: #f8f9fa; 
                        border-radius: 4px; 
                    }
                    .order-info .invoice { 
                        font-size: 13px; 
                        font-weight: 700; 
                        color: #5e72e4; 
                    }
                    .order-info .time { 
                        font-size: 10px; 
                        color: #6c757d; 
                        margin-top: 3px;
                    }
                    .items { 
                        padding: 6px; 
                        margin-bottom: 6px; 
                        background: #f8f9fa; 
                        border-radius: 4px; 
                        border-top: 2px solid #5e72e4; 
                        border-bottom: 2px solid #5e72e4; 
                    }
                    .item { 
                        display: flex; 
                        padding: 4px 0; 
                        margin-bottom: 0; 
                        border-bottom: 1px solid #dee2e6; 
                    }
                    .item:last-child { 
                        border-bottom: none; 
                    }
                    .item-qty { 
                        min-width: 28px;
                        text-align: center;
                        font-size: 12px; 
                        font-weight: 700;
                        margin-right: 12px; 
                        color: #5e72e4;
                        background: #e7f3ff;
                        padding: 3px 6px;
                        border-radius: 4px;
                    }
                    .item-separator { 
                        margin: 0 12px; 
                        color: #adb5bd; 
                        font-size: 13px; 
                    }
                    .item-name { 
                        font-size: 11px; 
                        font-weight: 600; 
                        color: #282f53; 
                        flex: 1;
                    }
                    .customer { 
                        padding: 6px; 
                        margin-bottom: 6px; 
                        font-size: 9px; 
                        background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%); 
                        border-left: 3px solid #5e72e4; 
                        border-radius: 4px; 
                    }
                    .customer-label {
                        font-size: 8px;
                        text-transform: uppercase;
                        color: #6c757d;
                        font-weight: 600;
                        letter-spacing: 0.5px;
                        margin-bottom: 2px;
                    }
                    .customer-name { 
                        font-size: 11px; 
                        font-weight: 700; 
                        color: #282f53; 
                    }
                    .notes { 
                        padding: 6px; 
                        margin-bottom: 6px; 
                        font-size: 9px; 
                        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); 
                        border-left: 3px solid #f7b731; 
                        border-radius: 4px; 
                    }
                    .notes-label {
                        font-size: 8px;
                        text-transform: uppercase;
                        color: #856404;
                        margin-bottom: 2px;
                        font-weight: 600;
                        letter-spacing: 0.5px;
                    }
                    .notes-content {
                        font-size: 10px;
                        font-weight: 600;
                        color: #856404;
                    }
                    .cashier { 
                        font-size: 9px; 
                        margin-bottom: 4px; 
                        padding: 5px; 
                        background: #f8f9fa; 
                        border-radius: 4px; 
                        color: #6c757d; 
                        text-align: center;
                    }
                    .footer { 
                        font-size: 8px; 
                        margin-top: 4px; 
                        padding: 5px; 
                        background: #f8f9fa; 
                        border-radius: 4px; 
                        color: #6c757d; 
                        text-align: center;
                    }
                    @media print {
                    * { margin: 0; padding: 0; }
                    html, body { 
                        padding: 0; 
                        margin: 0; 
                        height: auto; 
                        width: auto;
                        overflow: visible;
                    }
                    body { 
                        padding: 0; 
                        margin: 0;
                        display: block;
                    }
                    .order { 
                        margin: 0; 
                        padding: 6px; 
                        border: 2px solid #5e72e4; 
                        width: auto;
                        max-width: 300px;
                    }
                    .header { margin-bottom: 4px; padding-bottom: 4px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
                    .header h2 { font-size: 12px; color: white; }
                    .order-info { margin-bottom: 4px; font-size: 9px; padding: 4px; background: #f8f9fa; }
                    .order-info .invoice { font-size: 11px; color: #5e72e4; }
                    .order-info .time { font-size: 9px; }
                    .items { padding: 4px; margin-bottom: 4px; background: #f8f9fa; }
                    .item { padding: 3px 0; border-bottom: 1px solid #dee2e6; }
                    .item-qty { min-width: 25px; font-size: 11px; margin-right: 10px; padding: 2px 5px; background: #e7f3ff; color: #5e72e4; }
                    .item-separator { margin: 0 10px; color: #adb5bd; }
                    .item-name { font-size: 10px; }
                    .customer { padding: 4px; margin-bottom: 4px; font-size: 8px; background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%); border-left: 3px solid #5e72e4; }
                    .customer-name { font-size: 10px; }
                    .notes { padding: 4px; margin-bottom: 4px; font-size: 8px; background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border-left: 3px solid #f7b731; }
                    .cashier { font-size: 8px; margin-bottom: 3px; padding: 4px; background: #f8f9fa; }
                    .footer { font-size: 7px; margin-top: 3px; padding: 4px; background: #f8f9fa; margin-bottom: 0; }
                    * { page-break-inside: avoid; page-break-after: avoid; page-break-before: avoid; }
                    @page { 
                        margin: 0; 
                        size: auto;
                        padding: 0;
                    }
                    }
                </style>
            </head>
            <body>
                <div class="order">
                    <div class="header">
                        <h2>🍳 KITCHEN ORDER</h2>
                    </div>
                    <div class="order-info">
                        <div class="invoice">${invoiceNumber}</div>
                        <div class="time">${time} - ${date}</div>
                    </div>
                    ${customer ? `<div class="customer"><div class="customer-label">${customerLabel}</div><div class="customer-name">${customer}</div></div>` : ''}
                    <div class="items">${itemsHtml}</div>
                    ${notes ? `<div class="notes"><div class="notes-label">${notesLabel}</div><div class="notes-content">${notes}</div></div>` : ''}
                    ${cashier ? `<div class="cashier">${cashier}</div>` : ''}
                    <div class="footer">${footer}</div>
                </div>
            </body>
            </html>
        `;
        
        // Create print window (like POS does)
        const printWindow = window.open('', '_blank', 'width=400,height=600');
        
        if (!printWindow) {
            alert('Popup blocked. Please allow popups to print.');
            return;
        }
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Wait for content to load before printing (like POS)
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
    })
    .catch(error => {
        console.error('Error printing order:', error);
        alert('Error loading order for printing. Please try again.');
    });
}

// Print all pending orders directly (works like POS print)
function printAllPendingOrders() {
    const pendingOrders = document.querySelectorAll('#pending-orders-container .order-card');
    const printBtn = document.getElementById('print-pending-btn');
    
    if (pendingOrders.length === 0) {
        alert('No pending orders to print.');
        return;
    }
    
    // Disable button during print
    if (printBtn) {
        printBtn.disabled = true;
        const originalText = printBtn.innerHTML;
        printBtn.innerHTML = '<i class="fe fe-loader me-1"></i> Printing...';
        
        // Re-enable after a delay
        setTimeout(() => {
            printBtn.disabled = false;
            printBtn.innerHTML = originalText;
        }, 2000);
    }
    
    // Get logo path
    const logoPath = window.location.origin + '/logo.jpg';
    
    // Create print content with all pending orders
    let printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print All Pending Orders</title>
            <meta charset="UTF-8">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
                    font-size: 12px;
                    line-height: 1.3;
                    padding: 3px;
                    background: #fff;
                }
                .order { 
                    border: 2px solid #5e72e4; 
                    margin-bottom: 8px; 
                    padding: 5px;
                    page-break-inside: avoid;
                    border-radius: 4px;
                    background: #fff;
                }
                .header { 
                    text-align: center; 
                    border-bottom: 2px solid #5e72e4; 
                    padding-bottom: 4px; 
                    margin-bottom: 4px; 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 6px;
                    border-radius: 3px 3px 0 0;
                }
                .header h2 { font-size: 12px; margin-bottom: 2px; color: white; font-weight: 700; }
                .order-info { margin-bottom: 4px; font-size: 9px; padding: 3px; background: #f8f9fa; border-radius: 3px; }
                .order-info .invoice { font-size: 11px; font-weight: 700; color: #5e72e4; }
                .order-info .time { font-size: 9px; color: #6c757d; }
                .items { padding: 4px; margin-bottom: 4px; background: #f8f9fa; border-radius: 3px; border-top: 2px solid #5e72e4; border-bottom: 2px solid #5e72e4; }
                .item { display: flex; padding: 3px 0; margin-bottom: 0; border-bottom: 1px solid #dee2e6; }
                .item:last-child { border-bottom: none; }
                .item-qty { 
                    min-width: 25px;
                    text-align: center;
                    font-size: 11px; 
                    font-weight: 700;
                    margin-right: 10px; 
                    color: #5e72e4;
                    background: #e7f3ff;
                    padding: 2px 5px;
                    border-radius: 3px;
                }
                .item-separator { margin: 0 10px; color: #adb5bd; font-size: 12px; }
                .item-name { font-size: 10px; font-weight: 600; color: #282f53; }
                .customer { padding: 4px; margin-bottom: 4px; font-size: 8px; background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%); border-left: 3px solid #5e72e4; border-radius: 3px; }
                .customer-name { font-size: 10px; font-weight: 700; color: #282f53; }
                .notes { padding: 4px; margin-bottom: 4px; font-size: 8px; background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border-left: 3px solid #f7b731; border-radius: 3px; }
                .cashier { font-size: 8px; margin-bottom: 2px; padding: 3px; background: #f8f9fa; border-radius: 3px; color: #6c757d; }
                .footer { font-size: 8px; margin-top: 3px; padding: 3px; background: #f8f9fa; border-radius: 3px; color: #6c757d; }
                @media print {
                    * { margin: 0; padding: 0; }
                    html, body { padding: 0; margin: 0; height: auto; }
                    body { padding: 2px 3px; }
                    .order { margin-bottom: 5px; padding: 3px; border: 2px solid #5e72e4; }
                    .header { margin-bottom: 3px; padding-bottom: 3px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
                    .header h2 { font-size: 11px; color: white; }
                    .order-info { margin-bottom: 3px; font-size: 8px; padding: 2px; background: #f8f9fa; }
                    .order-info .invoice { font-size: 10px; color: #5e72e4; }
                    .order-info .time { font-size: 8px; }
                    .items { padding: 3px; margin-bottom: 3px; background: #f8f9fa; }
                    .item { padding: 2px 0; border-bottom: 1px solid #dee2e6; }
                    .item-qty { min-width: 22px; font-size: 10px; margin-right: 8px; padding: 1px 4px; background: #e7f3ff; color: #5e72e4; }
                    .item-separator { margin: 0 8px; color: #adb5bd; }
                    .item-name { font-size: 9px; }
                    .customer { padding: 3px; margin-bottom: 3px; font-size: 7px; background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%); border-left: 3px solid #5e72e4; }
                    .customer-name { font-size: 9px; }
                    .notes { padding: 3px; margin-bottom: 3px; font-size: 7px; background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border-left: 3px solid #f7b731; }
                    .cashier { font-size: 7px; margin-bottom: 2px; padding: 2px; background: #f8f9fa; }
                    .footer { font-size: 7px; margin-top: 2px; padding: 2px; background: #f8f9fa; }
                    * { page-break-inside: avoid; page-break-after: avoid; page-break-before: avoid; }
                    @page { margin: 0; size: auto; }
                }
            </style>
        </head>
        <body>
    `;
    
    pendingOrders.forEach((orderCard) => {
        const invoiceNumber = orderCard.querySelector('.fw-bold')?.textContent || 'N/A';
        const timeAgo = orderCard.querySelector('.time-ago')?.textContent?.trim() || '';
        const customer = orderCard.querySelector('.fe-user')?.parentElement?.textContent?.trim() || '';
        const items = orderCard.querySelectorAll('.order-item');
        const notes = orderCard.querySelector('.bg-light')?.textContent?.trim() || '';
        
        let itemsHtml = '';
        items.forEach(item => {
            const qty = item.querySelector('.qty-badge')?.textContent || '1';
            const name = item.querySelector('.fw-semibold')?.textContent || '';
            const prepTime = item.querySelector('.badge.bg-info')?.textContent || '';
            itemsHtml += `
                <div class="item">
                    <span class="item-qty">${qty}</span>
                    <span class="item-separator">|</span>
                    <span class="item-name">${name}${prepTime ? ' ' + prepTime : ''}</span>
                </div>
            `;
        });
        
        printContent += `
            <div class="order">
                <div class="header">
                    <h2>🍳 KITCHEN ORDER</h2>
                </div>
                <div class="order-info">
                    <div class="invoice">${invoiceNumber}</div>
                    <div class="time">${timeAgo}</div>
                </div>
                ${customer ? `<div class="customer"><div class="customer-name">${customer}</div></div>` : ''}
                <div class="items">${itemsHtml}</div>
                ${notes ? `<div class="notes">${notes}</div>` : ''}
                <div class="footer">Total items: ${items.length}</div>
            </div>
        `;
    });
    
    printContent += `
        </body>
        </html>
    `;
    
    // Try to open print window (like POS does)
    const printWindow = window.open('', '_blank', 'width=400,height=600');
    
    if (!printWindow) {
        // If popup blocked, try alternative method
        alert('Popup blocked. Please allow popups for this site, or use Ctrl+P to print.');
        return;
    }
    
    printWindow.document.write(printContent);
    printWindow.document.close();
    
    // Wait for content to load before printing (like POS)
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
}

// Start auto-refresh when page loads
document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
});

// Stop auto-refresh when page is hidden, restart when visible
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
        refreshOrders(); // Immediate refresh when coming back
    }
});
</script>
@endpush





