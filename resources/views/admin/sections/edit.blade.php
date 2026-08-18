@extends('layouts.admin')

@section('title', 'Edit Section')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.sections.index') }}">Sections</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Section: {{ $section->name }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sections.update', $section) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label" for="name">Section Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $section->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="code">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" 
                               id="code" name="code" value="{{ old('code', $section->code) }}" maxlength="20" required>
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" 
                               id="location" name="location" value="{{ old('location', $section->location) }}">
                        @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description', $section->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="custom-switch">
                            <input type="checkbox" name="is_active" class="custom-switch-input" value="1" 
                                   {{ old('is_active', $section->is_active) ? 'checked' : '' }}>
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">Active</span>
                        </label>
                    </div>
                    
                    <div class="d-flex">
                        <a href="{{ route('admin.sections.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Section</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 col-md-12">
        <div class="card border-danger">
            <div class="card-header bg-danger-transparent">
                <h3 class="card-title text-danger">Danger Zone</h3>
            </div>
            <div class="card-body">
                <p>Deleting this section will remove all staff assignments associated with it.</p>
                <form action="{{ route('admin.sections.destroy', $section) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this section?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fe fe-trash-2 me-1"></i> Delete Section
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection










