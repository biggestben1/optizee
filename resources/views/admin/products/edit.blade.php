@extends('layouts.admin')

@section('title', 'Edit Product')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Product - {{ $product->name }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.products.update', $product) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="name">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="category_id">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        data-is-food="{{ $category->name === 'Food' || ($category->parent && $category->parent->name === 'Food') ? '1' : '0' }}"
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="unit">Unit <span class="text-danger">*</span></label>
                            <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                                <option value="piece" {{ old('unit', $product->unit) == 'piece' ? 'selected' : '' }}>Piece</option>
                                <option value="bottle" {{ old('unit', $product->unit) == 'bottle' ? 'selected' : '' }}>Bottle</option>
                                <option value="can" {{ old('unit', $product->unit) == 'can' ? 'selected' : '' }}>Can</option>
                                <option value="pack" {{ old('unit', $product->unit) == 'pack' ? 'selected' : '' }}>Pack</option>
                                <option value="crate" {{ old('unit', $product->unit) == 'crate' ? 'selected' : '' }}>Crate</option>
                                <option value="portions" id="portionsOption" style="display: none;" {{ old('unit', $product->unit) == 'portions' ? 'selected' : '' }}>Portions</option>
                            </select>
                            @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3" id="costPriceField">
                            <label class="form-label" for="cost_price">Cost Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₦</span>
                                <input type="text" class="form-control @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price', number_format($product->cost_price, 0, '.', ',')) }}" placeholder="2,000">
                            </div>
                            @error('cost_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="selling_price">Selling Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₦</span>
                                <input type="text" class="form-control @error('selling_price') is-invalid @enderror" id="selling_price" name="selling_price" value="{{ old('selling_price', number_format($product->selling_price, 0, '.', ',')) }}" placeholder="2,000" required>
                            </div>
                            @error('selling_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="stock_quantity">Current Stock</label>
                            <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required>
                            @error('stock_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="reorder_level">Reorder Level <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('reorder_level') is-invalid @enderror" id="reorder_level" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level) }}" required>
                            @error('reorder_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="custom-switch">
                            <input type="checkbox" name="is_active" class="custom-switch-input" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">Active</span>
                        </label>
                    </div>
                    <div class="d-flex">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const costPriceInput = document.getElementById('cost_price');
        const costPriceField = document.getElementById('costPriceField');
        const sellingPriceInput = document.getElementById('selling_price');
        const categorySelect = document.getElementById('category_id');
        const unitSelect = document.getElementById('unit');
        const portionsOption = document.getElementById('portionsOption');
        const form = costPriceInput.closest('form');
        
        // Function to check if category is food
        function isFoodCategory() {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            return selectedOption && selectedOption.getAttribute('data-is-food') === '1';
        }
        
        // Function to toggle cost price field and unit options
        function toggleCostPrice() {
            if (isFoodCategory()) {
                costPriceField.style.display = 'none';
                costPriceInput.removeAttribute('required');
                costPriceInput.value = '0';
                // Show portions option for food
                if (portionsOption) {
                    portionsOption.style.display = 'block';
                }
            } else {
                costPriceField.style.display = 'block';
                costPriceInput.setAttribute('required', 'required');
                // Hide portions option for non-food
                if (portionsOption) {
                    portionsOption.style.display = 'none';
                    // If portions was selected, change to piece
                    if (unitSelect && unitSelect.value === 'portions') {
                        unitSelect.value = 'piece';
                    }
                }
            }
        }
        
        // Check on category change
        categorySelect.addEventListener('change', toggleCostPrice);
        
        // Check on page load
        toggleCostPrice();
        
        // Function to format number with commas
        function formatNumber(input) {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/,/g, '');
                if (value && !isNaN(value)) {
                    e.target.value = parseFloat(value).toLocaleString('en-US');
                }
            });
        }
        
        // Format cost price with commas on input
        formatNumber(costPriceInput);
        
        // Format selling price with commas on input
        formatNumber(sellingPriceInput);
        
        // Remove commas before form submission
        form.addEventListener('submit', function(e) {
            if (!isFoodCategory()) {
                let costValue = costPriceInput.value.replace(/,/g, '');
                costPriceInput.value = costValue;
            }
            
            let sellingValue = sellingPriceInput.value.replace(/,/g, '');
            sellingPriceInput.value = sellingValue;
        });
        
        // Format initial values if they exist
        function formatInitialValue(input) {
            if (input.value && input.value !== '0') {
                let value = input.value.replace(/,/g, '');
                if (!isNaN(value)) {
                    input.value = parseFloat(value).toLocaleString('en-US');
                }
            }
        }
        
        formatInitialValue(costPriceInput);
        formatInitialValue(sellingPriceInput);
    });
</script>
@endpush
@endsection











