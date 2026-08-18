@extends('layouts.admin')

@section('title', 'Purchase Invoice Checklist')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.suppliers.index') }}">Suppliers</a></li>
<li class="breadcrumb-item active" aria-current="page">Purchase Invoice Checklist</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Delivery Checklist - {{ $purchaseInvoice->invoice_number }}</h3>
                <div class="card-options">
                    <a href="{{ route('admin.suppliers.orders', $purchaseInvoice->supplier) }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Supplier Information</h5>
                        <p><strong>Supplier:</strong> {{ $purchaseInvoice->supplier_name ?? ($purchaseInvoice->supplier->name ?? 'N/A') }}</p>
                        <p><strong>Invoice Number:</strong> {{ $purchaseInvoice->invoice_number }}</p>
                        <p><strong>Invoice Date:</strong> {{ $purchaseInvoice->invoice_date->format('M d, Y') }}</p>
                        @if($purchaseInvoice->expected_delivery_date)
                        <p><strong>Expected Delivery:</strong> {{ $purchaseInvoice->expected_delivery_date->format('M d, Y') }}</p>
                        @endif
                        <p><strong>Total Amount:</strong> ₦{{ number_format($purchaseInvoice->total_amount, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Order Details</h5>
                        @if($purchaseInvoice->notes)
                        <p><strong>Notes:</strong> {{ $purchaseInvoice->notes }}</p>
                        @endif
                    </div>
                </div>

                <form action="{{ route('admin.purchase-invoices.verify', $purchaseInvoice) }}" method="POST" id="checklistForm">
                    @csrf
                    
                    <h5 class="mb-3">Ordered Items Checklist</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">Check</th>
                                    <th style="width: 30%;">Product</th>
                                    <th style="width: 15%;">Ordered Qty</th>
                                    <th style="width: 15%;">Received Qty</th>
                                    <th style="width: 15%;">Unit Cost</th>
                                    <th style="width: 20%;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="orderedItemsTable">
                                @php
                                    $stockMovements = $purchaseInvoice->stockMovements;
                                    $processedProducts = [];
                                @endphp
                                
                                @if($purchaseInvoice->items && count($purchaseInvoice->items) > 0)
                                    @foreach($purchaseInvoice->items as $index => $invoiceItem)
                                        @php
                                            $product = \App\Models\Product::find($invoiceItem['product_id'] ?? null);
                                            if (!$product) continue;
                                            
                                            $movement = $stockMovements->where('product_id', $product->id)->first();
                                            $processedProducts[] = $product->id;
                                        @endphp
                                        <tr>
                                            <td>
                                                <input type="checkbox" 
                                                       name="items[{{ $product->id }}][checked]" 
                                                       value="1" 
                                                       class="form-check-input item-checkbox"
                                                       data-product-id="{{ $product->id }}"
                                                       {{ $movement ? 'checked' : '' }}>
                                            </td>
                                            <td>
                                                <strong>{{ $product->name }}</strong>
                                                <br><small class="text-muted">{{ $product->category->name ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control expected-qty" 
                                                       value="{{ $invoiceItem['quantity'] ?? 0 }}" 
                                                       readonly>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       name="items[{{ $product->id }}][quantity_received]" 
                                                       class="form-control received-qty" 
                                                       value="{{ $movement ? $movement->quantity : ($invoiceItem['quantity'] ?? 0) }}" 
                                                       min="0" 
                                                       required>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text">₦</span>
                                                    <input type="text" 
                                                           name="items[{{ $product->id }}][unit_cost]" 
                                                           class="form-control unit-cost" 
                                                           value="{{ $movement ? number_format($movement->unit_cost, 0, '.', ',') : number_format($invoiceItem['unit_price'] ?? 0, 0, '.', ',') }}" 
                                                           placeholder="0" 
                                                           required>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="hidden" name="items[{{ $product->id }}][product_id]" value="{{ $product->id }}">
                                                @if($movement)
                                                <span class="badge bg-success">Received</span>
                                                @else
                                                <span class="badge bg-warning">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr id="noOrderedItems">
                                        <td colspan="6" class="text-center text-muted">
                                            <p>No items found in this purchase invoice.</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mb-3">Additional Items (Not in Original Order)</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered" id="additionalItemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Product</th>
                                    <th style="width: 15%;">Quantity</th>
                                    <th style="width: 15%;">Unit Cost</th>
                                    <th style="width: 20%;">Description</th>
                                    <th style="width: 20%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="additionalItemsBody">
                                <tr id="noAdditionalItems">
                                    <td colspan="5" class="text-center text-muted">No additional items added yet</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary mb-3" onclick="addAdditionalItem()">
                        <i class="fe fe-plus me-1"></i> Add Additional Item
                    </button>

                    <div class="mb-3">
                        <label class="form-label" for="notes">Verification Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add any notes about the delivery..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('admin.suppliers.orders', $purchaseInvoice->supplier) }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-success">
                            <i class="fe fe-check me-2"></i> Verify Delivery & Update Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let additionalItemIndex = 0;
    const products = @json($products->map(function($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category->name ?? 'N/A'
        ];
    }));

    // Format number inputs with commas
    document.addEventListener('DOMContentLoaded', function() {
        const unitCostInputs = document.querySelectorAll('.unit-cost');
        unitCostInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/,/g, '');
                if (value && !isNaN(value)) {
                    e.target.value = parseFloat(value).toLocaleString('en-US');
                }
            });
        });

        // Remove commas before form submission
        document.getElementById('checklistForm').addEventListener('submit', function(e) {
            document.querySelectorAll('.unit-cost').forEach(input => {
                let value = input.value.replace(/,/g, '');
                input.value = value;
            });
        });
    });

    function addAdditionalItem() {
        const tbody = document.getElementById('additionalItemsBody');
        const noItemsRow = document.getElementById('noAdditionalItems');
        
        if (noItemsRow) {
            noItemsRow.remove();
        }

        const row = document.createElement('tr');
        row.id = 'additionalItem-' + additionalItemIndex;
        row.innerHTML = `
            <td>
                <select name="additional_items[${additionalItemIndex}][product_id]" class="form-select" required>
                    <option value="">Select Product</option>
                    ${products.map(p => `<option value="${p.id}">${p.name} (${p.category})</option>`).join('')}
                </select>
            </td>
            <td>
                <input type="number" name="additional_items[${additionalItemIndex}][quantity]" class="form-control" min="1" value="1" required>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text">₦</span>
                    <input type="text" name="additional_items[${additionalItemIndex}][unit_cost]" class="form-control additional-unit-cost" placeholder="0" required>
                </div>
            </td>
            <td>
                <input type="text" name="additional_items[${additionalItemIndex}][description]" class="form-control" placeholder="Optional description">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeAdditionalItem(${additionalItemIndex})">
                    <i class="fe fe-trash"></i> Remove
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
        
        // Add comma formatting to new unit cost input
        const newInput = row.querySelector('.additional-unit-cost');
        newInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/,/g, '');
            if (value && !isNaN(value)) {
                e.target.value = parseFloat(value).toLocaleString('en-US');
            }
        });
        
        additionalItemIndex++;
    }

    function removeAdditionalItem(index) {
        const row = document.getElementById('additionalItem-' + index);
        if (row) {
            row.remove();
        }
        
        // Show "no items" message if table is empty
        const tbody = document.getElementById('additionalItemsBody');
        if (tbody.children.length === 0) {
            tbody.innerHTML = '<tr id="noAdditionalItems"><td colspan="5" class="text-center text-muted">No additional items added yet</td></tr>';
        }
    }

    // Handle checkbox changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-checkbox')) {
            const row = e.target.closest('tr');
            const receivedQtyInput = row.querySelector('.received-qty');
            const unitCostInput = row.querySelector('.unit-cost');
            
            if (e.target.checked) {
                receivedQtyInput.removeAttribute('disabled');
                receivedQtyInput.removeAttribute('readonly');
                unitCostInput.removeAttribute('disabled');
                unitCostInput.removeAttribute('readonly');
            } else {
                receivedQtyInput.setAttribute('disabled', 'disabled');
                unitCostInput.setAttribute('disabled', 'disabled');
            }
        }
    });
    
    // Ensure checkboxes are enabled on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            if (checkbox.checked) {
                const row = checkbox.closest('tr');
                const receivedQtyInput = row.querySelector('.received-qty');
                const unitCostInput = row.querySelector('.unit-cost');
                if (receivedQtyInput) {
                    receivedQtyInput.removeAttribute('disabled');
                    receivedQtyInput.removeAttribute('readonly');
                }
                if (unitCostInput) {
                    unitCostInput.removeAttribute('disabled');
                    unitCostInput.removeAttribute('readonly');
                }
            }
        });
    });
</script>
@endpush
@endsection

