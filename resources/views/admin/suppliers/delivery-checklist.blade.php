@extends('layouts.admin')

@section('title', 'Delivery Checklist')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.suppliers.index') }}">Suppliers</a></li>
<li class="breadcrumb-item active" aria-current="page">Delivery Checklist</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Delivery Checklist - {{ $supply->reference_number }}</h3>
                <div class="card-options">
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Supplier Information</h5>
                        <p><strong>Supplier:</strong> {{ $supply->supplier->name }}</p>
                        <p><strong>Reference:</strong> {{ $supply->reference_number }}</p>
                        <p><strong>Supply Date:</strong> {{ $supply->supply_date->format('M d, Y') }}</p>
                        <p><strong>Total Amount:</strong> ₦{{ number_format($supply->total_amount, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Order Details</h5>
                        <p><strong>Items Description:</strong></p>
                        <p>{{ $supply->items_description ?? 'No description provided' }}</p>
                        @if($supply->notes)
                        <p><strong>Notes:</strong> {{ $supply->notes }}</p>
                        @endif
                    </div>
                </div>

                <form action="{{ route('admin.supplier-supplies.verify', $supply) }}" method="POST" id="checklistForm">
                    @csrf
                    
                    <h5 class="mb-3">Ordered Items Checklist</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">Check</th>
                                    <th style="width: 25%;">Product</th>
                                    <th style="width: 10%;">Ordered</th>
                                    <th style="width: 10%;">Total Received</th>
                                    <th style="width: 10%;">Balance</th>
                                    <th style="width: 12%;">New Received</th>
                                    <th style="width: 14%;">Unit Cost</th>
                                    <th style="width: 14%;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="orderedItemsTable">
                                @php
                                    $stockMovements = $supply->stockMovements;
                                    $processedProducts = [];
                                @endphp
                                
                                    @foreach($supply->stockMovements->groupBy('product_id') as $productId => $movements)
                                        @php 
                                            $movement = $movements->first();
                                            $product = $movement->product;
                                            // Only sum movements that are explicitly verified or balance deliveries
                                            $totalReceived = $movements->filter(function($m) {
                                                $reason = (string)$m->reason;
                                                return str_contains($reason, 'Verified') || str_contains($reason, 'Balance Delivery');
                                            })->sum('quantity');
                                            $quantityOrdered = $movements->whereNotNull('quantity_ordered')->first()?->quantity_ordered ?? $movements->first()?->quantity ?? 0;
                                            $processedProducts[] = $productId; 
                                        @endphp
                                    @if($product)
                                         <tr>
                                             <td>
                                                 <input type="checkbox" 
                                                        name="items[{{ $product->id }}][checked]" 
                                                        value="1" 
                                                        class="form-check-input item-checkbox"
                                                        data-product-id="{{ $product->id }}">
                                             </td>
                                             <td>
                                                 <strong>{{ $product->name }}</strong>
                                                 <br><small class="text-muted">{{ $product->category->name ?? 'N/A' }}</small>
                                             </td>
                                             <td>
                                                 <input type="number" 
                                                        name="items[{{ $product->id }}][quantity_ordered]" 
                                                        class="form-control ordered-qty" 
                                                        value="{{ $quantityOrdered }}" 
                                                        min="0" 
                                                        required>
                                             </td>
                                             <td>
                                                 <span class="badge bg-secondary">{{ $totalReceived }}</span>
                                             </td>
                                             <td>
                                                 <span class="badge bg-{{ ($quantityOrdered - $totalReceived) > 0 ? 'warning' : 'success' }}">
                                                     {{ max(0, $quantityOrdered - $totalReceived) }}
                                                 </span>
                                             </td>
                                             <td>
                                                 <input type="number" 
                                                        name="items[{{ $product->id }}][quantity_received]" 
                                                        class="form-control received-qty" 
                                                        value="{{ max(0, $quantityOrdered - $totalReceived) }}" 
                                                        min="0" 
                                                        required
                                                        disabled>
                                             </td>
                                             <td>
                                                 <div class="input-group">
                                                     <span class="input-group-text">₦</span>
                                                     <input type="text" 
                                                            name="items[{{ $product->id }}][unit_cost]" 
                                                            class="form-control unit-cost" 
                                                            value="{{ number_format($movement->unit_cost, 0, '.', ',') }}" 
                                                            placeholder="0" 
                                                            required
                                                            disabled>
                                                 </div>
                                             </td>
                                             <td>
                                                 <input type="hidden" name="items[{{ $product->id }}][product_id]" value="{{ $product->id }}">
                                                 @if($quantityOrdered > 0 && $totalReceived >= $quantityOrdered)
                                                     <span class="badge bg-success status-badge">Completed</span>
                                                 @elseif($totalReceived > 0)
                                                     <span class="badge bg-warning status-badge">Partial</span>
                                                 @else
                                                     <span class="badge bg-secondary status-badge">Pending</span>
                                                 @endif
                                             </td>
                                         </tr>
                                    @endif
                                @endforeach

                                @if($stockMovements->isEmpty())
                                    <tr id="noOrderedItems">
                                        <td colspan="7" class="text-center text-muted">
                                            <p>No items found in this order. Use the "Additional Items" section below to add received items.</p>
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
                        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-success">
                            <i class="fe fe-check me-2"></i> Verify Delivery & Update Stock
                        </button>
                    </div>
                </form>

                <hr class="my-5">

                <h5 class="mb-3">Delivery History</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-nowrap">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Cost</th>
                                <th>User</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supply->stockMovements->sortByDesc('created_at') as $history)
                            <tr>
                                <td>{{ $history->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $history->product->name }}</td>
                                <td><span class="badge bg-primary">{{ $history->quantity }}</span></td>
                                <td>₦{{ number_format($history->unit_cost, 2) }}</td>
                                <td>{{ $history->user->name }}</td>
                                <td>{{ $history->reason }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No history records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
            const statusBadge = row.querySelector('.status-badge');
            
            if (e.target.checked) {
                receivedQtyInput.removeAttribute('disabled');
                unitCostInput.removeAttribute('disabled');
                statusBadge.textContent = 'Verified';
                statusBadge.className = 'badge bg-success status-badge';
            } else {
                receivedQtyInput.setAttribute('disabled', 'disabled');
                unitCostInput.setAttribute('disabled', 'disabled');
                statusBadge.textContent = 'Pending';
                statusBadge.className = 'badge bg-secondary status-badge';
            }
        }
    });
</script>
@endpush
@endsection

