@extends('layouts.admin')

@section('title', 'Edit ' . ($category->isSubcategory() ? 'Subcategory' : 'Category'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit {{ $category->isSubcategory() ? 'Subcategory' : 'Category' }}</h3>
            </div>
            <div class="card-body">
                @if($category->isSubcategory() && $category->parent)
                <div class="alert alert-info mb-3">
                    <i class="fe fe-info me-2"></i>
                    This is a subcategory of: <strong>{{ $category->parent->name }}</strong>
                </div>
                @endif

                <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    @if($category->isParent())
                    <div class="mb-3">
                        <label class="form-label" for="parent_id">Parent Category <small class="text-muted">(Leave empty to keep as main category)</small></label>
                        <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                            <option value="">-- None (Main Category) --</option>
                            @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" 
                                {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @else
                    <div class="mb-3">
                        <label class="form-label" for="parent_id">Parent Category</label>
                        <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                            <option value="">-- None (Make Main Category) --</option>
                            @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" 
                                {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="department">Department <span class="text-danger">*</span></label>
                        <select class="form-select @error('department') is-invalid @enderror" id="department" name="department" required>
                            <option value="bar_kitchen" {{ old('department', $category->department) == 'bar_kitchen' ? 'selected' : '' }}>Bar n Kitchen</option>
                            <option value="hotel" {{ old('department', $category->department) == 'hotel' ? 'selected' : '' }}>Hotel (Consumables)</option>
                        </select>
                        @error('department')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="name">{{ $category->isSubcategory() ? 'Subcategory' : 'Category' }} Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="custom-switch">
                            <input type="checkbox" name="is_active" class="custom-switch-input" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">Active</span>
                        </label>
                    </div>
                    <div class="d-flex">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            Update {{ $category->isSubcategory() ? 'Subcategory' : 'Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


