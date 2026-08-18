<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Invoice - {{ $invoice_number }}</title>
    <style>
        @charset "UTF-8";
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            padding: 20px;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .invoice-logo {
            max-width: 150px;
            max-height: 80px;
            margin-bottom: 15px;
        }
        .invoice-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .invoice-header p {
            font-size: 14px;
            color: #666;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-box {
            flex: 1;
        }
        .info-box h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #666;
            text-transform: uppercase;
        }
        .info-box p {
            margin: 5px 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f5f5f5;
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #333;
        }
        .notes h4 {
            margin-bottom: 10px;
        }
        @media print {
            body {
                padding: 0;
            }
            .invoice-container {
                border: none;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            @php
                if (isset($isPdf)) {
                    // For PDF, use base64 encoded image
                    $logoPath = public_path('sash/assets/images/brand/logo.png');
                    if (file_exists($logoPath)) {
                        $logoData = base64_encode(file_get_contents($logoPath));
                        $logoSrc = 'data:image/png;base64,' . $logoData;
                    } else {
                        $logoSrc = '';
                    }
                } else {
                    // For web view, use asset URL
                    $logoSrc = asset('sash/assets/images/brand/logo.png');
                }
            @endphp
            @if($logoSrc)
            <img src="{{ $logoSrc }}" class="invoice-logo" alt="Optizee Hotel and Suites">
            @endif
            <h1>PURCHASE ORDER</h1>
            <p>Invoice Number: <strong>{{ $invoice_number }}</strong></p>
            <p>Date: {{ date('F d, Y', strtotime($invoice_date)) }}</p>
        </div>

        <div class="invoice-info">
            <div class="info-box">
                <h3>Supplier Information</h3>
                <p><strong>{{ $supplier_name }}</strong></p>
                @if($supplier_phone)
                <p>Phone: {{ $supplier_phone }}</p>
                @endif
                @if($supplier_address)
                <p>{{ $supplier_address }}</p>
                @endif
            </div>
            <div class="info-box">
                <h3>Order Details</h3>
                <p><strong>Invoice Date:</strong> {{ date('F d, Y', strtotime($invoice_date)) }}</p>
                @if($expected_delivery_date)
                <p><strong>Expected Delivery:</strong> {{ date('F d, Y', strtotime($expected_delivery_date)) }}</p>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item['product']->name }}</strong>
                        @if($item['notes'])
                        <br><small style="color: #666;">{{ $item['notes'] }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item['quantity'] }} {{ $item['product']->unit }}</td>
                    <td class="text-right">&#8358;{{ number_format($item['unit_price'], 2) }}</td>
                    <td class="text-right">&#8358;{{ number_format($item['total'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                    <td class="text-right"><strong>&#8358;{{ number_format($subtotal, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        @if($notes)
        <div class="notes">
            <h4>Notes:</h4>
            <p>{{ $notes }}</p>
        </div>
        @endif
    </div>

    @if(!isset($isPdf))
    <div class="no-print" style="text-align: center; margin-top: 30px; padding: 20px; background: #f5f5f5; border-radius: 5px;">
        <a href="{{ route('admin.products.low-stock-invoice') }}" style="display: inline-block; padding: 12px 24px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; font-weight: bold;">
            <i class="fe fe-arrow-left" style="margin-right: 5px;"></i> Go Back
        </a>
        <form id="pdfDownloadForm" action="{{ route('admin.products.download-purchase-invoice-pdf') }}" method="POST" style="display: inline-block;">
            @csrf
            <input type="hidden" name="invoice_number" value="{{ $invoice_number }}">
            <input type="hidden" name="supplier_id" value="{{ $supplier_id ?? '' }}">
            <input type="hidden" name="supplier_name" value="{{ $supplier_name ?? '' }}">
            <input type="hidden" name="supplier_phone" value="{{ $supplier_phone ?? '' }}">
            <input type="hidden" name="supplier_address" value="{{ $supplier_address ?? '' }}">
            <input type="hidden" name="invoice_date" value="{{ $invoice_date }}">
            <input type="hidden" name="expected_delivery_date" value="{{ $expected_delivery_date ?? '' }}">
            <input type="hidden" name="notes" value="{{ $notes ?? '' }}">
            @foreach($items as $index => $item)
            <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item['product']->id }}">
            <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}">
            <input type="hidden" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] }}">
            <input type="hidden" name="items[{{ $index }}][notes]" value="{{ $item['notes'] ?? '' }}">
            @endforeach
            <button type="submit" style="padding: 12px 24px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; margin-right: 10px;">
                <i class="fe fe-download" style="margin-right: 5px;"></i> Download as PDF
            </button>
        </form>
        <button onclick="saveAndPrint()" style="padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;">
            <i class="fe fe-printer" style="margin-right: 5px;"></i> Print Invoice
        </button>
    </div>
    @endif

    @if(!isset($isPdf))
    <script>
        // Function to save invoice when print is clicked
        function saveAndPrint() {
            // The invoice is already saved when the page loads (in generatePurchaseInvoice)
            // Just trigger print
            window.print();
        }
        
        // Set document title for PDF download
        window.addEventListener('beforeprint', function() {
            document.title = 'Purchase_Invoice_{{ $invoice_number }}';
        });
        
        // After print dialog closes, restore title
        window.addEventListener('afterprint', function() {
            document.title = 'Purchase Invoice - {{ $invoice_number }}';
        });
    </script>
    @endif
</body>
</html>

