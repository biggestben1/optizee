@extends('layouts.admin')

@section('title', 'Receipt - ' . $sale->invoice_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Receipt</h3>
                    <div>
                        <button onclick="window.print()" class="btn btn-primary me-2">
                            <i class="fe fe-printer me-2"></i>Print
                        </button>
                        <a href="{{ route('admin.pos.index') }}" class="btn btn-secondary">
                            <i class="fe fe-arrow-left me-2"></i>Back to POS
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="receipt-container" id="receipt-content">
                        <div class="text-center mb-4">
                            <img src="{{ asset('logo.jpg') }}" alt="Optizee Hotel and Suites" style="max-width: 150px; max-height: 80px; margin-bottom: 15px;">
                            <h4>Optizee Hotel and Suites</h4>
                            <p class="mb-0"><strong>Invoice:</strong> {{ $sale->invoice_number }}</p>
                            <p class="mb-0">{{ $sale->created_at->format('F d, Y h:i A') }}</p>
                            @if($sale->table)
                            <p class="mb-0"><strong>Table:</strong> {{ $sale->table->number }} {{ $sale->table->name ? '- ' . $sale->table->name : '' }}</p>
                            @endif
                            @if($sale->tableGuest)
                            <p class="mb-0"><strong>Guest:</strong> {{ $sale->tableGuest->guest_name }}</p>
                            @endif
                            @if($sale->customer)
                            <p class="mb-0"><strong>Customer:</strong> {{ $sale->customer->name }}</p>
                            @endif
                        </div>
                        
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">₦{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">₦{{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end"><strong>₦{{ number_format($sale->subtotal, 2) }}</strong></td>
                                </tr>
                                @if($sale->discount > 0)
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Discount:</strong></td>
                                    <td class="text-end"><strong>-₦{{ number_format($sale->discount, 2) }}</strong></td>
                                </tr>
                                @endif
                                @if($sale->tax > 0)
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tax/VAT:</strong></td>
                                    <td class="text-end"><strong>₦{{ number_format($sale->tax, 2) }}</strong></td>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
                                    <td class="text-end"><strong>₦{{ number_format($sale->total, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Payment Method:</strong></td>
                                    <td class="text-end"><strong>{{ ucfirst($sale->payment_method) }}</strong></td>
                                </tr>
                                @if($sale->amount_paid > 0)
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Amount Paid:</strong></td>
                                    <td class="text-end"><strong>₦{{ number_format($sale->amount_paid, 2) }}</strong></td>
                                </tr>
                                @if($sale->change > 0)
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Change:</strong></td>
                                    <td class="text-end"><strong>₦{{ number_format($sale->change, 2) }}</strong></td>
                                </tr>
                                @endif
                                @endif
                                @if($sale->is_credit_sale)
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Status:</strong></td>
                                    <td class="text-end"><span class="badge bg-warning">Credit Sale</span></td>
                                </tr>
                                @endif
                            </tfoot>
                        </table>
                        
                        @if($sale->notes)
                        <div class="alert alert-info mt-3">
                            <strong>Notes:</strong> {{ $sale->notes }}
                        </div>
                        @endif
                        
                        <div class="text-center mt-4">
                            <p class="mb-0"><strong>Cashier:</strong> {{ $sale->user->name }}</p>
                            <p class="mb-0 text-muted">Thank you for your business!</p>
                        </div>
                        
                        @php
                            $bankName = \App\Models\Setting::getValue('bank.name', '');
                            $accountName = \App\Models\Setting::getValue('bank.account_name', '');
                            $accountNumber = \App\Models\Setting::getValue('bank.account_number', '');
                        @endphp
                        @if($bankName || $accountName || $accountNumber)
                        <div class="text-center mt-3 pt-3 border-top">
                            <p class="mb-1"><strong>Bank Transfer Details:</strong></p>
                            @if($bankName)<p class="mb-0"><strong>Bank:</strong> {{ $bankName }}</p>@endif
                            @if($accountNumber)<p class="mb-0"><strong>Account Number:</strong> {{ $accountNumber }}</p>@endif
                            @if($accountName)<p class="mb-0"><strong>Account Name:</strong> {{ $accountName }}</p>@endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .card-header,
        .btn,
        .breadcrumb,
        .sidebar,
        .header,
        .footer {
            display: none !important;
        }
        .card {
            border: none;
            box-shadow: none;
        }
        .receipt-container {
            max-width: 100%;
        }
    }
</style>
@endsection
















