@extends('layouts.admin')

@section('title', 'Add Table')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.tables.index') }}">Tables</a></li>
<li class="breadcrumb-item active" aria-current="page">Add New</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Table Details</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.tables.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="number">Table Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('number') is-invalid @enderror" 
                               id="number" name="number" value="{{ old('number') }}" 
                               placeholder="e.g., T1, T2, VIP-1" required>
                        @error('number')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="name">Table Name (Optional)</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}"
                               placeholder="e.g., Window Table, Corner Table">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="capacity">Capacity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                               id="capacity" name="capacity" value="{{ old('capacity', 4) }}" 
                               min="1" max="50" required>
                        <small class="text-muted">Number of seats</small>
                        @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" 
                               id="location" name="location" value="{{ old('location') }}"
                               placeholder="e.g., Indoor, Outdoor, VIP Section">
                        @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="2" 
                                  placeholder="Any special notes about this table">{{ old('notes') }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex">
                        <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Table</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection









