@extends('layouts.admin')

@section('title', 'Edit Table')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.tables.index') }}">Tables</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Table: {{ $table->number }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.tables.update', $table) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label" for="number">Table Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('number') is-invalid @enderror" 
                               id="number" name="number" value="{{ old('number', $table->number) }}" required>
                        @error('number')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="name">Table Name (Optional)</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $table->name) }}">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="capacity">Capacity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                               id="capacity" name="capacity" value="{{ old('capacity', $table->capacity) }}" 
                               min="1" max="50" required>
                        @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" 
                               id="location" name="location" value="{{ old('location', $table->location) }}">
                        @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="2">{{ old('notes', $table->notes) }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="custom-switch">
                            <input type="checkbox" name="is_active" class="custom-switch-input" value="1" 
                                   {{ old('is_active', $table->is_active) ? 'checked' : '' }}>
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">Active</span>
                        </label>
                    </div>
                    
                    <div class="d-flex">
                        <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Table</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection









