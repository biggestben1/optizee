@extends('mobile.layout')

@section('title', 'Kitchen Orders')

@section('styles')
<style>
    .stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 14px;
    }
    .stat {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 10px 6px;
        text-align: center;
    }
    .stat .n {
        display: block;
        font-size: 1.25rem;
        font-weight: 800;
    }
    .stat .l {
        color: var(--muted);
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .filters {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        margin-bottom: 12px;
        scrollbar-width: none;
    }
    .filters::-webkit-scrollbar { display: none; }
    .filter {
        flex: 0 0 auto;
        border: 1px solid var(--border);
        background: var(--bg-2);
        color: var(--muted);
        border-radius: 999px;
        padding: 8px 12px;
        font-weight: 700;
        font-size: 0.82rem;
    }
    .filter.active {
        background: rgba(14,165,233,0.18);
        color: #7dd3fc;
        border-color: rgba(14,165,233,0.45);
    }
    .orders { display: grid; gap: 12px; }
    .order {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 14px;
    }
    .order.pending { border-left: 4px solid var(--warning); }
    .order.preparing { border-left: 4px solid var(--accent); }
    .order.ready { border-left: 4px solid var(--success); }
    .order.served { border-left: 4px solid var(--muted); opacity: 0.85; }
    .order-head {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 10px;
    }
    .order-items {
        margin: 0 0 12px;
        padding: 0;
        list-style: none;
    }
    .order-items li {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        padding: 6px 0;
        border-bottom: 1px dashed var(--border);
        font-size: 0.92rem;
    }
    .order-items li:last-child { border-bottom: 0; }
    .actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .actions .btn { flex: 1; min-width: 120px; padding: 11px 12px; }
    .empty {
        text-align: center;
        color: var(--muted);
        padding: 40px 12px;
    }
    .pulse {
        animation: pulse 1.2s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.35); }
        50% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
    }
</style>
@endsection

@section('top')
<div>
    <h1>Kitchen</h1>
    <div class="sub">{{ $userName }} · Live orders</div>
</div>
<button type="button" class="icon-btn" onclick="loadOrders(true)" title="Refresh">
    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-2.6-6.3M21 3v6h-6"/></svg>
</button>
@endsection

@section('content')
<div class="stats">
    <div class="stat"><span class="n" id="stat-pending">0</span><span class="l">Pending</span></div>
    <div class="stat"><span class="n" id="stat-preparing">0</span><span class="l">Prep</span></div>
    <div class="stat"><span class="n" id="stat-ready">0</span><span class="l">Ready</span></div>
    <div class="stat"><span class="n" id="stat-served">0</span><span class="l">Served</span></div>
</div>

<div class="filters">
    <button type="button" class="filter active" data-filter="all">All</button>
    <button type="button" class="filter" data-filter="pending">Pending</button>
    <button type="button" class="filter" data-filter="preparing">Preparing</button>
    <button type="button" class="filter" data-filter="ready">Ready</button>
</div>

<div class="orders" id="orders">
    <div class="empty">Loading orders...</div>
</div>
@endsection

@section('scripts')
<script>
const canControl = @json($canControl);
let filter = 'all';
let lastPendingCount = null;
let audioCtx = null;

document.querySelectorAll('.filter').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.filter').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filter = btn.dataset.filter;
        loadOrders(false);
    });
});

function playAlert() {
    try {
        audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.frequency.value = 880;
        gain.gain.value = 0.05;
        osc.start();
        setTimeout(() => osc.stop(), 180);
    } catch (e) {}
}

function timeAgo(dateStr) {
    const diff = Math.max(0, Math.floor((Date.now() - new Date(dateStr).getTime()) / 60000));
    if (diff < 1) return 'Just now';
    if (diff === 1) return '1 min ago';
    return diff + ' mins ago';
}

function orderMeta(order) {
    const table = order.table?.number || 'Walk-in';
    const guest = order.table_guest?.guest_name ? ' · ' + order.table_guest.guest_name : '';
    const customer = order.customer?.name ? ' · ' + order.customer.name : '';
    return table + guest + customer;
}

function renderOrders(data) {
    const stats = data.stats || {};
    document.getElementById('stat-pending').textContent = stats.pending || 0;
    document.getElementById('stat-preparing').textContent = stats.preparing || 0;
    document.getElementById('stat-ready').textContent = stats.ready || 0;
    document.getElementById('stat-served').textContent = stats.served || 0;

    const pendingCount = (data.pendingOrders || []).filter(o => o.kitchen_status === 'pending').length;
    if (lastPendingCount !== null && pendingCount > lastPendingCount) {
        playAlert();
        showToast('New kitchen order');
    }
    lastPendingCount = pendingCount;

    let orders = [
        ...(data.pendingOrders || []),
        ...(data.readyOrders || []),
    ];

    if (filter !== 'all') {
        orders = orders.filter(o => o.kitchen_status === filter);
    }

    const el = document.getElementById('orders');
    if (!orders.length) {
        el.innerHTML = '<div class="empty">No orders right now.</div>';
        return;
    }

    el.innerHTML = orders.map(order => {
        const items = (order.items || []).map(item => `
            <li>
                <span>${item.product_name}</span>
                <strong>×${item.quantity}</strong>
            </li>
        `).join('');

        let actions = '';
        if (order.kitchen_status === 'pending' && canControl) {
            actions = `<button class="btn btn-warning" onclick="setStatus(${order.id}, 'preparing')">Start Preparing</button>`;
        } else if (order.kitchen_status === 'preparing' && canControl) {
            actions = `<button class="btn btn-success" onclick="setStatus(${order.id}, 'ready')">Mark Ready</button>`;
        } else if (order.kitchen_status === 'ready') {
            actions = `<button class="btn btn-primary" onclick="setStatus(${order.id}, 'served')">Mark Served</button>`;
        }

        return `
            <article class="order ${order.kitchen_status} ${order.kitchen_status === 'pending' ? 'pulse' : ''}">
                <div class="order-head">
                    <div>
                        <strong>#${order.invoice_number}</strong>
                        <div class="muted" style="font-size:0.8rem;margin-top:2px;">${orderMeta(order)}</div>
                        <div class="muted" style="font-size:0.75rem;">${timeAgo(order.created_at)}</div>
                    </div>
                    <span class="badge badge-${order.kitchen_status === 'ready' ? 'success' : (order.kitchen_status === 'pending' ? 'warning' : 'info')}">${order.kitchen_status}</span>
                </div>
                <ul class="order-items">${items}</ul>
                ${order.notes ? `<div class="muted" style="margin-bottom:10px;font-size:0.82rem;">Note: ${order.notes}</div>` : ''}
                <div class="actions">${actions}</div>
            </article>
        `;
    }).join('');
}

async function loadOrders(manual = false) {
    try {
        const res = await fetch(`{{ route('admin.kitchen.live') }}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!res.ok) throw new Error('Failed to load');
        const data = await res.json();
        renderOrders(data);
        if (manual) showToast('Refreshed');
    } catch (e) {
        if (manual) showToast('Could not refresh', true);
    }
}

async function setStatus(orderId, action) {
    try {
        const res = await fetch(`{{ url('/admin/kitchen') }}/${orderId}/${action}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await res.json();
        if (!res.ok || data.error) throw new Error(data.error || 'Update failed');
        showToast(data.message || 'Updated');
        loadOrders(false);
    } catch (e) {
        showToast(e.message || 'Update failed', true);
    }
}

loadOrders(false);
setInterval(() => loadOrders(false), 10000);
</script>
@endsection
