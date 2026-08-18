@extends('layouts.admin')

@section('title', 'Low Stock Purchase Invoice')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
<li class="breadcrumb-item active" aria-current="page">Low Stock Invoice</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Create Purchase Invoice for Low Stock Products</h3>
                <div class="card-options">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i> Back to Products
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($lowStockProducts->count() === 0)
                <div class="alert alert-success">
                    <i class="fe fe-check-circle me-2"></i> All products are well stocked!
                </div>
                @else
                <form action="{{ route('admin.products.generate-purchase-invoice') }}" method="POST" id="invoiceForm" target="_blank">
                    @csrf
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Supplier Information</h5>
                            <div class="mb-3">
                                <label class="form-label">Select Existing Supplier (Optional)</label>
                                <select id="supplierSelect" class="form-select">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" 
                                            data-name="{{ $supplier->name }}"
                                            data-phone="{{ $supplier->phone }}"
                                            data-address="{{ $supplier->address ?? '' }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="supplier_name">Supplier Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="supplier_name" name="supplier_name" required>
                                <input type="hidden" id="supplier_id" name="supplier_id">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="supplier_phone">Supplier Phone</label>
                                <input type="text" class="form-control" id="supplier_phone" name="supplier_phone">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="supplier_address">Supplier Address</label>
                                <textarea class="form-control" id="supplier_address" name="supplier_address" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>Invoice Details</h5>
                            <div class="mb-3">
                                <label class="form-label" for="invoice_date">Invoice Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="expected_delivery_date">Expected Delivery Date</label>
                                <input type="date" class="form-control" id="expected_delivery_date" name="expected_delivery_date">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="notes">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes for the invoice..."></textarea>
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3">Low Stock Products</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th style="width: 25%;">Product</th>
                                    <th style="width: 10%;">Current Stock</th>
                                    <th style="width: 10%;">Reorder Level</th>
                                    <th style="width: 15%;">Quantity to Order</th>
                                    <th style="width: 15%;">Unit Price</th>
                                    <th style="width: 15%;">Total</th>
                                    <th style="width: 5%;">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockProducts as $index => $product)
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               name="items[{{ $index }}][product_id]" 
                                               value="{{ $product->id }}" 
                                               class="form-check-input product-checkbox"
                                               data-product-id="{{ $product->id }}"
                                               data-reorder-level="{{ $product->reorder_level }}">
                                    </td>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                        <br><small class="text-muted">{{ $product->category->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ $product->stock_quantity }}</span>
                                    </td>
                                    <td>{{ $product->reorder_level }}</td>
                                    <td>
                                        <input type="number" 
                                               name="items[{{ $index }}][quantity]" 
                                               class="form-control quantity-input" 
                                               min="1" 
                                               value="{{ max($product->reorder_level - $product->stock_quantity, $product->reorder_level) }}"
                                               disabled>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">₦</span>
                                            <input type="text" 
                                                   name="items[{{ $index }}][unit_price]" 
                                                   class="form-control unit-price-input" 
                                                   placeholder="0"
                                                   value="{{ number_format($product->cost_price, 0, '.', ',') }}"
                                                   disabled>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="item-total">₦0.00</span>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="items[{{ $index }}][notes]" 
                                               class="form-control form-control-sm" 
                                               placeholder="Notes"
                                               disabled>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-end"><strong>Total:</strong></td>
                                    <td><strong id="grandTotal">₦0.00</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" onclick="window.print()">
                            <i class="fe fe-printer me-2"></i> Print Preview
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-file-text me-2"></i> Generate Invoice
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const productCheckboxes = document.querySelectorAll('.product-checkbox');
        const supplierSelect = document.getElementById('supplierSelect');
        const supplierName = document.getElementById('supplier_name');
        const supplierPhone = document.getElementById('supplier_phone');
        const supplierAddress = document.getElementById('supplier_address');
        const supplierId = document.getElementById('supplier_id');

        // Supplier selection
        supplierSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                supplierName.value = selectedOption.getAttribute('data-name');
                supplierPhone.value = selectedOption.getAttribute('data-phone');
                supplierAddress.value = selectedOption.getAttribute('data-address');
                supplierId.value = selectedOption.value;
            } else {
                supplierName.value = '';
                supplierPhone.value = '';
                supplierAddress.value = '';
                supplierId.value = '';
            }
        });

        // Select all checkbox
        selectAll.addEventListener('change', function() {
            productCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                toggleProductRow(checkbox);
            });
        });

        // Product checkbox change
        productCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                toggleProductRow(this);
            });
        });

        function toggleProductRow(checkbox) {
            const row = checkbox.closest('tr');
            const quantityInput = row.querySelector('.quantity-input');
            const unitPriceInput = row.querySelector('.unit-price-input');
            const notesInput = row.querySelector('input[name*="[notes]"]');
            
            if (checkbox.checked) {
                quantityInput.removeAttribute('disabled');
                unitPriceInput.removeAttribute('disabled');
                notesInput.removeAttribute('disabled');
                calculateRowTotal(row);
            } else {
                quantityInput.setAttribute('disabled', 'disabled');
                unitPriceInput.setAttribute('disabled', 'disabled');
                notesInput.setAttribute('disabled', 'disabled');
                row.querySelector('.item-total').textContent = '₦0.00';
                updateGrandTotal();
            }
        }

        // Calculate row total
        function calculateRowTotal(row) {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const unitPrice = parseFloat(row.querySelector('.unit-price-input').value.replace(/,/g, '')) || 0;
            const total = quantity * unitPrice;
            row.querySelector('.item-total').textContent = '₦' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            updateGrandTotal();
        }

        // Update grand total
        function updateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
                const row = checkbox.closest('tr');
                const totalText = row.querySelector('.item-total').textContent;
                const total = parseFloat(totalText.replace(/[₦,]/g, '')) || 0;
                grandTotal += total;
            });
            document.getElementById('grandTotal').textContent = '₦' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // Quantity and price input listeners
        document.querySelectorAll('.quantity-input, .unit-price-input').forEach(input => {
            input.addEventListener('input', function() {
                if (!this.disabled) {
                    const row = this.closest('tr');
                    if (this.classList.contains('unit-price-input')) {
                        // Format with commas
                        let value = this.value.replace(/,/g, '');
                        if (value && !isNaN(value)) {
                            this.value = parseFloat(value).toLocaleString('en-US');
                        }
                    }
                    calculateRowTotal(row);
                }
            });
        });

        // Form submission - remove commas and ensure only checked items are submitted
        document.getElementById('invoiceForm').addEventListener('submit', function(e) {
            // Remove commas from unit prices
            document.querySelectorAll('.unit-price-input:not([disabled])').forEach(input => {
                input.value = input.value.replace(/,/g, '');
            });

            // Remove unchecked items from form
            document.querySelectorAll('.product-checkbox:not(:checked)').forEach(checkbox => {
                const row = checkbox.closest('tr');
                row.querySelectorAll('input').forEach(input => {
                    if (input.type !== 'checkbox') {
                        input.removeAttribute('name');
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection

