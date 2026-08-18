@extends('layouts.admin')

@section('title', 'Supervisor Dashboard - Live Orders')

@push('styles')
<style>
    .order-card {
        border-left: 4px solid #5e72e4;
        transition: all 0.3s;
        margin-bottom: 15px;
    }
    .order-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    .order-card.new-order {
        animation: pulse 2s infinite;
        border-left-color: #ffc107;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    .waiter-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .table-badge {
        background: #28a745;
        color: white;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
    }
    .total-badge {
        background: #ffc107;
        color: #000;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 1rem;
    }
    .refresh-indicator {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 1000;
    }
    .items-list {
        max-height: 200px;
        overflow-y: auto;
    }
    .item-row {
        padding: 8px;
        border-bottom: 1px solid #f0f0f0;
    }
    .item-row:last-child {
        border-bottom: none;
    }
    .auto-refresh-badge {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        font-size: 0.875rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">
                            <i class="fe fe-eye me-2"></i>Supervisor Dashboard - Live Orders
                        </h4>
                        <small class="text-muted">Real-time view of all active orders: pending payments and orders being prepared in kitchen</small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="refresh-indicator" id="refresh-indicator" style="display: none;">
                            <span class="badge bg-info">
                                <i class="fe fe-refresh-cw me-1"></i>Updating...
                            </span>
                        </div>
                        <button class="btn btn-primary" onclick="refreshOrders()" id="refresh-btn">
                            <i class="fe fe-refresh-cw me-2"></i>Refresh Now
                        </button>
                        <button class="btn btn-outline-secondary" onclick="testAPI()" title="Test API Connection">
                            <i class="fe fe-check me-2"></i>Test API
                        </button>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto-refresh-toggle" checked>
                            <label class="form-check-label" for="auto-refresh-toggle">Auto-refresh</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="orders-container">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading pending orders...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="auto-refresh-badge" id="auto-refresh-badge">
    <i class="fe fe-clock me-2"></i>Auto-refreshing every 3 seconds
</div>
@endsection

@push('scripts')
<script>
let autoRefreshInterval;
let lastOrderCount = 0;
let knownOrderIds = new Set();

function formatCurrency(amount) {
    return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}

function refreshOrders() {
    const indicator = document.getElementById('refresh-indicator');
    const container = document.getElementById('orders-container');
    
    if (indicator) indicator.style.display = 'block';
    
    fetch('{{ route("admin.pos.supervisor.orders") }}', {
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
        if (indicator) indicator.style.display = 'none';
        
        console.log('Orders data received:', data); // Debug log
        console.log('Success:', data.success);
        console.log('Orders count:', data.orders ? data.orders.length : 0);
        
        if (data.success && Array.isArray(data.orders)) {
            renderOrders(data.orders);
            
            // Check for new orders
            const currentOrderIds = new Set(data.orders.map(o => o.id));
            const newOrders = data.orders.filter(o => !knownOrderIds.has(o.id));
            
            if (newOrders.length > 0 && knownOrderIds.size > 0) {
                // Show notification for new orders
                if (Notification.permission === 'granted') {
                    new Notification(`New Order!`, {
                        body: `${newOrders.length} new order(s) from ${newOrders[0].waiter_name}`,
                        icon: '/logo.jpg'
                    });
                }
            }
            
            knownOrderIds = currentOrderIds;
            lastOrderCount = data.orders.length;
        } else {
            console.warn('No orders or invalid response:', data);
            container.innerHTML = `
                <div class="alert alert-info text-center">
                    <i class="fe fe-info me-2"></i>No pending orders at the moment.
                    ${data.message ? `<br><small>${data.message}</small>` : ''}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading orders:', error);
        if (indicator) indicator.style.display = 'none';
        container.innerHTML = `
            <div class="alert alert-danger text-center">
                <i class="fe fe-alert-circle me-2"></i>Error loading orders. Please refresh the page.
                <br><small>${error.message}</small>
            </div>
        `;
    });
}

function renderOrders(orders) {
    const container = document.getElementById('orders-container');
    
    console.log('Rendering orders:', orders.length);
    
    if (!orders || orders.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info text-center">
                <i class="fe fe-info me-2"></i>No active orders at the moment.
                <br><small class="text-muted">Orders will appear here when waiters create pending orders or when orders are being prepared in the kitchen.</small>
            </div>
        `;
        return;
    }
    
    let html = '<div class="row">';
    
    orders.forEach(order => {
        const isNew = !knownOrderIds.has(order.id);
        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card order-card ${isNew ? 'new-order' : ''}" data-order-id="${order.id}">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <span class="waiter-badge">
                                <i class="fe fe-user me-1"></i>${order.waiter_name}
                            </span>
                            <span class="table-badge ms-2">
                                <i class="fe fe-grid me-1"></i>Table ${order.table_number}
                            </span>
                            ${order.status_badge ? `<span class="ms-2">${order.status_badge}</span>` : ''}
                        </div>
                        <small class="text-muted">${order.created_at_human}</small>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Guest:</strong> ${order.guest_name}
                            ${order.customer_name ? `<br><small class="text-muted">Customer: ${order.customer_name}</small>` : ''}
                        </div>
                        
                        <div class="items-list mb-3">
                            ${order.items.map(item => `
                                <div class="item-row">
                                    <div class="d-flex justify-content-between">
                                        <span>${item.name} × ${item.quantity}</span>
                                        <strong>₦${formatCurrency(item.total)}</strong>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div>
                                <small class="text-muted">Subtotal: ₦${formatCurrency(order.subtotal)}</small>
                                ${order.discount > 0 ? `<br><small class="text-muted">Discount: -₦${formatCurrency(order.discount)}</small>` : ''}
                            </div>
                            <div class="total-badge">
                                ₦${formatCurrency(order.total)}
                            </div>
                        </div>
                        
                        <div class="mt-3 d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary flex-fill" onclick="viewOrder(${order.id})">
                                <i class="fe fe-eye me-1"></i>View Details
                            </button>
                            <button class="btn btn-sm btn-outline-info flex-fill" onclick="printOrder(${order.id})">
                                <i class="fe fe-printer me-1"></i>Print
                            </button>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-center">
                        <small class="text-muted">
                            <i class="fe fe-clock me-1"></i>${order.items_count} item(s) • ${formatTime(order.created_at)}
                        </small>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
    
    // Remove 'new-order' class after animation
    setTimeout(() => {
        document.querySelectorAll('.order-card.new-order').forEach(card => {
            card.classList.remove('new-order');
        });
    }, 2000);
}

function viewOrder(orderId) {
    window.open(`{{ route("admin.pos.index") }}?sale_id=${orderId}`, '_blank');
}

function printOrder(orderId) {
    window.open(`{{ route("admin.pos.index") }}?sale_id=${orderId}&print=true`, '_blank');
}

// Auto-refresh toggle
document.getElementById('auto-refresh-toggle').addEventListener('change', function(e) {
    if (e.target.checked) {
        startAutoRefresh();
        document.getElementById('auto-refresh-badge').style.display = 'block';
    } else {
        stopAutoRefresh();
        document.getElementById('auto-refresh-badge').style.display = 'none';
    }
});

function startAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    autoRefreshInterval = setInterval(refreshOrders, 3000); // Refresh every 3 seconds
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
}

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}

// Initial load
console.log('Supervisor dashboard initialized');
console.log('Route URL:', '{{ route("admin.pos.supervisor.orders") }}');
refreshOrders();

// Start auto-refresh
startAutoRefresh();

// Refresh on page visibility change
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        refreshOrders();
    }
});

// Test API function
function testAPI() {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fe fe-loader me-2 fa-spin"></i>Testing...';
    btn.disabled = true;
    
    fetch('{{ route("admin.pos.supervisor.orders") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.json();
    })
    .then(data => {
        console.log('API Test Response:', data);
        alert('API Test Result:\n\nSuccess: ' + (data.success ? 'Yes' : 'No') + '\nOrders Found: ' + (data.orders ? data.orders.length : 0) + '\n\nCheck browser console for full details.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    })
    .catch(error => {
        console.error('API Test Error:', error);
        alert('API Test Failed!\n\nError: ' + error.message + '\n\nCheck browser console for details.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>
@endpush








