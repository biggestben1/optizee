@extends('layouts.admin')

@section('title', 'Add Supplier')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.suppliers.index') }}">Suppliers</a></li>
<li class="breadcrumb-item active" aria-current="page">Add New</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Supplier Details</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.suppliers.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="name">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="phone">Phone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required>
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="address">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address') }}</textarea>
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="products_supplied">Products Supplied</label>
                        <textarea class="form-control @error('products_supplied') is-invalid @enderror" id="products_supplied" name="products_supplied" rows="2" placeholder="e.g., Beers, Soft Drinks, Spirits">{{ old('products_supplied') }}</textarea>
                        @error('products_supplied')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex">
                        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection











