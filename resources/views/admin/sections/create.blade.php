@extends('layouts.admin')

@section('title', 'Create Section')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.sections.index') }}">Sections</a></li>
<li class="breadcrumb-item active" aria-current="page">Create</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Section Details</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sections.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="name">Section Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" 
                               placeholder="e.g., Kitchen, Bar, Reception" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="code">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" 
                               id="code" name="code" value="{{ old('code') }}" 
                               placeholder="e.g., KIT, BAR, REC" maxlength="20" required>
                        <small class="text-muted">Short unique code for the section (will be uppercase)</small>
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" 
                               id="location" name="location" value="{{ old('location') }}"
                               placeholder="e.g., Ground Floor, Building A">
                        @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Brief description of this section">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="custom-switch">
                            <input type="checkbox" name="is_active" class="custom-switch-input" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">Active</span>
                        </label>
                    </div>
                    
                    <div class="d-flex">
                        <a href="{{ route('admin.sections.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Section</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection










