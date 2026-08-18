<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Order - {{ $sale->invoice_number }}</title>
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
            padding: 3px;
            max-width: 300px;
            margin: 0 auto;
            height: auto;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 3px;
            margin-bottom: 3px;
        }
        
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 1px;
        }
        
        .order-info {
            margin-bottom: 3px;
            font-size: 10px;
        }
        .order-info .invoice {
            font-size: 14px;
            font-weight: bold;
        }
        
        .order-info .time {
            font-size: 11px;
            margin-top: 1px;
        }
        
        .items {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 5px 0;
            margin-bottom: 3px;
        }
        
        .item {
            display: flex;
            align-items: center;
            padding: 4px 0;
            border-bottom: 1px solid #ccc;
        }
        
        .item:last-child {
            border-bottom: none;
        }
        
        .item-qty {
            display: inline-block;
            min-width: 30px;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin-right: 12px;
        }
        
        .item-name {
            font-size: 13px;
            font-weight: bold;
            flex: 1;
        }
        
        .item-separator {
            margin: 0 12px;
            color: #ccc;
            font-size: 14px;
        }
        
        .customer {
            background: #f5f5f5;
            padding: 4px;
            margin-bottom: 3px;
            border-radius: 2px;
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
            margin-bottom: 3px;
            border-left: 2px solid #ffc107;
        }
        
        .notes-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 1px;
        }
        
        .notes-content {
            font-size: 10px;
            font-weight: bold;
        }
        
        .footer {
            text-align: center;
            font-size: 8px;
            color: #666;
            margin-top: 2px;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .cashier {
            text-align: center;
            font-size: 9px;
            color: #666;
            margin-bottom: 1px;
        }
        
        @media print {
            * {
                margin: 0;
                padding: 0;
            }
            
            html {
                height: auto;
                width: 100%;
            }
            
            body {
                padding: 2px 3px;
                margin: 0;
                height: auto;
                width: 100%;
                max-width: 100%;
                overflow: visible;
            }
            
            .no-print {
                display: none !important;
            }
            
            /* Prevent any page breaks */
            * {
                page-break-inside: avoid;
                page-break-after: avoid;
                page-break-before: avoid;
            }
            
            .header {
                margin-bottom: 2px;
                padding-bottom: 2px;
            }
            
            .header h1 {
                font-size: 14px;
                margin-bottom: 0;
                line-height: 1.1;
            }
            
            .order-info {
                margin-bottom: 2px;
                font-size: 9px;
                line-height: 1.1;
            }
            
            .order-info .invoice {
                font-size: 12px;
            }
            
            .order-info .time {
                font-size: 10px;
                margin-top: 0;
            }
            
            .items {
                padding: 3px 0;
                margin-bottom: 2px;
            }
            
            .item {
                padding: 2px 0;
                line-height: 1.3;
                border-bottom: 1px solid #ccc;
            }
            
            .item:last-child {
                border-bottom: none;
            }
            
            .item-qty {
                min-width: 25px;
                font-size: 12px;
                margin-right: 8px;
            }
            
            .item-separator {
                margin: 0 10px;
                color: #ccc;
                font-size: 13px;
            }
            
            .item-name {
                font-size: 12px;
            }
            
            .customer {
                padding: 3px;
                margin-bottom: 2px;
                font-size: 8px;
            }
            
            .customer-name {
                font-size: 10px;
            }
            
            .notes {
                padding: 3px;
                margin-bottom: 2px;
                font-size: 8px;
            }
            
            .notes-content {
                font-size: 8px;
            }
            
            .cashier {
                margin-bottom: 1px;
                font-size: 8px;
                line-height: 1.1;
            }
            
            .footer {
                margin-top: 1px;
                margin-bottom: 0;
                padding-bottom: 0;
                font-size: 7px;
                line-height: 1.1;
            }
            
            @page {
                margin: 0;
                size: auto;
            }
        }
        
    </style>
</head>
<body>
    <div class="header">
        <h1>🍳 KITCHEN ORDER</h1>
    </div>
    
    <div class="order-info">
        <div class="invoice">{{ $sale->invoice_number }}</div>
        <div class="time">{{ $sale->created_at->format('H:i:s') }}</div>
        <div>{{ $sale->created_at->format('M d, Y') }}</div>
    </div>
    
    @if($sale->customer)
    <div class="customer">
        <div class="customer-label">Customer</div>
        <div class="customer-name">{{ $sale->customer->name }}</div>
    </div>
    @endif
    
    <div class="items">
        @foreach($sale->items as $item)
        <div class="item">
            <span class="item-qty">{{ $item->quantity }}</span>
            <span class="item-separator">|</span>
            <span class="item-name">
                {{ $item->product_name }}
                @if($item->product && $item->product->preparation_time)
                <span style="font-size: 11px; color: #666; margin-left: 8px;">
                    ({{ $item->product->preparation_time }} min)
                </span>
                @endif
            </span>
        </div>
        @endforeach
    </div>
    
    @if($sale->notes)
    <div class="notes">
        <div class="notes-label">⚠️ Special Notes</div>
        <div class="notes-content">{{ $sale->notes }}</div>
    </div>
    @endif
    
    <div class="cashier">
        Cashier: {{ $sale->user->name }}
    </div>
    
    <div class="footer">
        Order received at {{ $sale->created_at->format('H:i:s') }}
        <br>
        Total items: {{ $sale->items->sum('quantity') }}
    </div>
    
    <script>
        // Auto-print on load (optional - comment out if not wanted)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>





