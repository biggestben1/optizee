@extends('layouts.admin')

@section('title', 'Supplier Orders - ' . $supplier->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.suppliers.index') }}">Suppliers</a></li>
<li class="breadcrumb-item active" aria-current="page">Orders - {{ $supplier->name }}</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplyModal">
            <i class="fe fe-plus me-2"></i> Record New Supply
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Purchase Invoices - {{ $supplier->name }}</h3>
                <div class="card-options">
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i> Back to Suppliers
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th>Invoice Number</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseInvoices as $invoice)
                            <tr>
                                <td>
                                    <strong>{{ $invoice->invoice_number }}</strong>
                                </td>
                                <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                <td>₦{{ number_format($invoice->total_amount, 2) }}</td>
                                <td>
                                    <a href="{{ route('admin.purchase-invoices.checklist', $invoice) }}" class="btn btn-sm btn-primary" title="Checklist">
                                        <i class="fe fe-check-square me-1"></i> Checklist
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No purchase invoices found for this supplier.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $purchaseInvoices->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Supply Modal -->
<div class="modal fade" id="addSupplyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.suppliers.record-supply', $supplier) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record New Supply - {{ $supplier->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Supply Date</label>
                            <input type="date" name="supply_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Items Description</label>
                            <input type="text" name="items_description" class="form-control" placeholder="e.g., Office supplies, Equipment" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Products</label>
                        <div id="productsContainer">
                            <div class="product-row mb-2">
                                <div class="row">
                                    <div class="col-md-5">
                                        <select name="products[0][id]" class="form-select product-select">
                                            <option value="">-- Select Product --</option>
                                            @foreach(\App\Models\Product::active()->get() as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }} - ₦{{ number_format($product->cost_price, 2) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="products[0][unit_cost]" class="form-control unit-cost-input" placeholder="Unit Cost" data-index="0">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="products[0][quantity]" class="form-control qty-input" placeholder="Qty" min="1" step="1" data-index="0">
                                    </div>
                                    <div class="col-md-2">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addProductBtn">
                            <i class="fe fe-plus me-1"></i> Add Another Product
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="number" name="total_amount" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Amount Paid</label>
                            <input type="number" name="amount_paid" class="form-control" step="0.01" min="0" value="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Type</label>
                            <select name="payment_type" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="credit">Credit</option>
                                <option value="partial">Partial Payment</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes about this supply..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Supply</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let productCount = 1;
    const addProductBtn = document.getElementById('addProductBtn');
    const productsContainer = document.getElementById('productsContainer');

    // Format currency input
    function formatCurrency(value) {
        // Remove all non-digit and non-dot characters
        value = value.replace(/[^\d.]/g, '');
        
        // Split by decimal point
        let parts = value.split('.');
        let integerPart = parts[0] || '0';
        let decimalPart = parts[1] || '';
        
        // Add commas to integer part
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        
        // Limit decimal to 2 places
        if (decimalPart) {
            decimalPart = decimalPart.substring(0, 2);
        }
        
        // Combine
        return decimalPart ? integerPart + '.' + decimalPart : integerPart;
    }

    // Add event listeners to unit cost inputs
    function setupUnitCostFormatting() {
        document.querySelectorAll('.unit-cost-input').forEach(input => {
            input.addEventListener('input', function(e) {
                let cursorPos = this.selectionStart;
                let oldValue = this.value;
                let newValue = formatCurrency(this.value);
                
                this.value = newValue;
                
                // Adjust cursor position for added commas
                let diff = newValue.length - oldValue.length;
                this.setSelectionRange(cursorPos + diff, cursorPos + diff);
            });

            input.addEventListener('blur', function(e) {
                // Ensure it ends with .00 if no decimal part
                if (this.value && !this.value.includes('.')) {
                    this.value = this.value + '.00';
                } else if (this.value && this.value.split('.')[1]?.length === 1) {
                    this.value = this.value + '0';
                }
            });
        });
    }

    // Initial setup
    setupUnitCostFormatting();

    if (addProductBtn) {
        addProductBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productRow = document.createElement('div');
            productRow.className = 'product-row mb-2';
            productRow.innerHTML = `
                <div class="row">
                    <div class="col-md-5">
                        <select name="products[${productCount}][id]" class="form-select product-select">
                            <option value="">-- Select Product --</option>
                            @foreach(\App\Models\Product::active()->get() as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} - ₦{{ number_format($product->cost_price, 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="products[${productCount}][quantity]" class="form-control" placeholder="Qty" min="1" step="1">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="products[${productCount}][unit_cost]" class="form-control unit-cost-input" placeholder="Unit Cost" data-index="${productCount}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-danger remove-product-btn" title="Remove">
                            <i class="fe fe-trash-2"></i>
                        </button>
                    </div>
                </div>
            `;
            
            productsContainer.appendChild(productRow);
            
            // Re-setup formatting for new inputs
            setupUnitCostFormatting();
            
            // Add remove functionality
            productRow.querySelector('.remove-product-btn').addEventListener('click', function() {
                productRow.remove();
            });
            
            productCount++;
        });

        // Add remove functionality to initial row
        document.querySelectorAll('.remove-product-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.product-row').remove();
            });
        });
    }
});
</script>
@endsection
