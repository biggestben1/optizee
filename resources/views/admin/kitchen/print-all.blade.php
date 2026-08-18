<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print All Kitchen Orders</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.2;
            padding: 5px;
            background: #f5f5f5;
        }
        
        .no-print {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #5e72e4;
            color: white;
        }
        
        .btn-primary:hover {
            background: #4a5bc7;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .status-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-pending {
            background: #ffc107;
            color: #000;
        }
        
        .badge-preparing {
            background: #17a2b8;
            color: white;
        }
        
        .badge-ready {
            background: #28a745;
            color: white;
        }
        
        .orders-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .order-card {
            background: white;
            border: 2px solid #000;
            padding: 8px;
            page-break-inside: avoid;
            margin-bottom: 10px;
        }
        
        .order-card.pending {
            border-color: #ffc107;
        }
        
        .order-card.preparing {
            border-color: #17a2b8;
        }
        
        .order-card.ready {
            border-color: #28a745;
        }
        
        .order-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        
        .order-header h2 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .order-info {
            margin-bottom: 5px;
            font-size: 10px;
        }
        
        .order-info .invoice {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .order-info .time {
            font-size: 11px;
        }
        
        .items {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 5px 0;
            margin: 5px 0;
        }
        
        .item {
            display: flex;
            align-items: center;
            padding: 2px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .item:last-child {
            border-bottom: none;
        }
        
        .item-qty {
            width: 28px;
            height: 28px;
            background: #000;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            border-radius: 3px;
            margin-right: 8px;
        }
        
        .item-name {
            font-size: 12px;
            font-weight: bold;
            flex: 1;
        }
        
        .customer {
            background: #f5f5f5;
            padding: 4px;
            margin-bottom: 5px;
            border-radius: 3px;
            font-size: 10px;
        }
        
        .customer-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #666;
        }
        
        .customer-name {
            font-size: 12px;
            font-weight: bold;
        }
        
        .notes {
            background: #fffacd;
            padding: 4px;
            margin-bottom: 5px;
            border-left: 3px solid #ffc107;
            font-size: 10px;
        }
        
        .notes-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 2px;
        }
        
        .notes-content {
            font-size: 10px;
            font-weight: bold;
        }
        
        .footer {
            text-align: center;
            font-size: 8px;
            color: #666;
            margin-top: 5px;
        }
        
        .cashier {
            text-align: center;
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .table-info {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .orders-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
            
            .order-card {
                margin-bottom: 8px;
                padding: 5px;
            }
            
            .order-header {
                margin-bottom: 3px;
                padding-bottom: 2px;
            }
            
            .order-info {
                margin-bottom: 3px;
            }
            
            .items {
                padding: 3px 0;
                margin: 3px 0;
            }
            
            .item {
                padding: 1px 0;
            }
            
            .customer {
                padding: 3px;
                margin-bottom: 3px;
            }
            
            .notes {
                padding: 3px;
                margin-bottom: 3px;
            }
            
            .footer {
                margin-top: 3px;
            }
            
            @page {
                size: A4 landscape;
                margin: 0.5cm;
            }
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div class="header-controls">
            <h1>🍳 Print All Kitchen Orders</h1>
            <div>
                <button class="btn btn-primary" onclick="window.print()">
                    🖨️ Print All Orders
                </button>
                <a href="{{ route('admin.kitchen.index') }}" class="btn btn-secondary">
                    ← Back to Kitchen
                </a>
            </div>
        </div>
        
        <div class="status-filter">
            <span class="status-badge badge-pending">Pending: {{ $ordersByStatus['pending']->count() }}</span>
            <span class="status-badge badge-preparing">Preparing: {{ $ordersByStatus['preparing']->count() }}</span>
            <span class="status-badge badge-ready">Ready: {{ $ordersByStatus['ready']->count() }}</span>
            <span class="status-badge" style="background: #6c757d; color: white;">Total: {{ $orders->count() }}</span>
        </div>
        
        @if($isKitchenUser)
        <div style="background: #d1ecf1; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 12px;">
            <strong>Note:</strong> You are viewing only your assigned orders. Managers/Supervisors see all orders.
        </div>
        @endif
    </div>
    
    @if($orders->count() > 0)
    <div class="orders-container">
        @foreach($orders as $order)
        <div class="order-card {{ $order->kitchen_status }}">
            <div class="order-header">
                <h2>🍳 KITCHEN ORDER</h2>
            </div>
            
            <div class="order-info">
                <div class="invoice">{{ $order->invoice_number }}</div>
                <div class="time">{{ $order->created_at->format('H:i:s') }} - {{ $order->created_at->format('M d, Y') }}</div>
                @if($order->table)
                <div class="table-info">
                    <strong>Table:</strong> {{ $order->table->number }}
                    @if($order->tableGuest)
                    | <strong>Guest:</strong> {{ $order->tableGuest->guest_name }}
                    @endif
                </div>
                @endif
            </div>
            
            @if($order->customer)
            <div class="customer">
                <div class="customer-label">Customer</div>
                <div class="customer-name">{{ $order->customer->name }}</div>
            </div>
            @endif
            
            <div class="items">
                @foreach($order->items as $item)
                <div class="item">
                    <div class="item-qty">{{ $item->quantity }}</div>
                    <div class="item-name">
                        {{ $item->product_name }}
                        @if($item->product && $item->product->preparation_time)
                        <span style="font-size: 10px; color: #666; margin-left: 8px;">
                            ({{ $item->product->preparation_time }} min)
                        </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            
            @if($order->notes)
            <div class="notes">
                <div class="notes-label">⚠️ Special Notes</div>
                <div class="notes-content">{{ $order->notes }}</div>
            </div>
            @endif
            
            <div class="cashier">
                Cashier: {{ $order->user->name }}
            </div>
            
            @if($order->preparedBy)
            <div class="cashier">
                Prepared by: {{ $order->preparedBy->name }}
            </div>
            @endif
            
            <div class="footer">
                Status: <strong>{{ strtoupper($order->kitchen_status) }}</strong>
                <br>
                Total items: {{ $order->items->sum('quantity') }}
                @if($order->kitchen_ready_at)
                <br>
                Ready at: {{ $order->kitchen_ready_at->format('H:i:s') }}
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div style="font-size: 48px;">🍳</div>
        <h3>No Kitchen Orders Found</h3>
        <p>There are no kitchen orders to print at this time.</p>
    </div>
    @endif
    
    <script>
        // Auto-print on load (optional - uncomment if desired)
        // window.onload = function() {
        //     setTimeout(() => window.print(), 500);
        // }
    </script>
</body>
</html>

