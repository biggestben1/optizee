@extends('layouts.admin')

@section('title', 'Edit Customer')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Customer - {{ $customer->name }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" for="name">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="phone">Phone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" required>
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $customer->email) }}">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="address">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address', $customer->address) }}</textarea>
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="credit_limit">Credit Limit</label>
                        <div class="input-group">
                            <span class="input-group-text">₦</span>
                            <input type="text" class="form-control @error('credit_limit') is-invalid @enderror" 
                                   id="credit_limit" name="credit_limit" 
                                   value="{{ old('credit_limit', number_format($customer->credit_limit, 2)) }}" 
                                   placeholder="0.00"
                                   oninput="formatCurrency(this)">
                        </div>
                        @error('credit_limit')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Enter amount with commas (e.g., 2,000.00)</small>
                    </div>
                    <div class="mb-3">
                        <p class="mb-1">Current Balance: <strong class="text-danger">₦{{ number_format($customer->credit_balance, 2) }}</strong></p>
                        <p class="mb-0">Available Credit: <strong class="text-success">₦{{ number_format($customer->getAvailableCredit(), 2) }}</strong></p>
                    </div>
                    <div class="mb-3">
                        <label class="custom-switch">
                            <input type="checkbox" name="credit_enabled" class="custom-switch-input" value="1" {{ old('credit_enabled', $customer->credit_enabled) ? 'checked' : '' }}>
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">Enable Credit Purchases</span>
                        </label>
                    </div>
                    <div class="mb-3">
                        <label class="custom-switch">
                            <input type="checkbox" name="is_active" class="custom-switch-input" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">Active</span>
                        </label>
                    </div>
                    <div class="d-flex">
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function formatCurrency(input) {
    // Remove all non-digit characters except decimal point
    let value = input.value.replace(/[^\d.]/g, '');
    
    // Ensure only one decimal point
    let parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Limit to 2 decimal places
    if (parts.length === 2 && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    
    // Split into integer and decimal parts
    parts = value.split('.');
    let integerPart = parts[0] || '0';
    let decimalPart = parts[1] || '';
    
    // Add commas to integer part
    integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    
    // Reconstruct the value
    if (decimalPart) {
        value = integerPart + '.' + decimalPart;
    } else {
        value = integerPart;
    }
    
    input.value = value;
}

// Remove commas before form submission
document.querySelector('form').addEventListener('submit', function(e) {
    const creditLimitInput = document.getElementById('credit_limit');
    if (creditLimitInput) {
        creditLimitInput.value = creditLimitInput.value.replace(/,/g, '');
    }
});
</script>
@endpush


