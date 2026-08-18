@extends('layouts.admin')

@section('title', $parentId ? 'Add Subcategory' : 'Add Category')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ $parentId ? 'Add Subcategory' : 'Add New' }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $parentId ? 'Subcategory Details' : 'Category Details' }}</h3>
            </div>
            <div class="card-body">
                @if($parentId)
                @php $parent = \App\Models\Category::find($parentId); @endphp
                <div class="alert alert-info mb-3">
                    <i class="fe fe-info me-2"></i>
                    Creating subcategory under: <strong>{{ $parent->name }}</strong>
                </div>
                @endif

                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    @if($parentId)
                    <input type="hidden" name="parent_id" value="{{ $parentId }}">
                    @else
                    <div class="mb-3">
                        <label class="form-label" for="parent_id">Parent Category <small class="text-muted">(Optional - leave empty for main category)</small></label>
                        <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                            <option value="">-- None (Main Category) --</option>
                            @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
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
                            <option value="bar_kitchen" {{ old('department') == 'bar_kitchen' ? 'selected' : '' }}>Bar n Kitchen</option>
                            <option value="hotel" {{ old('department') == 'hotel' ? 'selected' : '' }}>Hotel (Consumables)</option>
                        </select>
                        @error('department')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="name">{{ $parentId ? 'Subcategory' : 'Category' }} Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="custom-switch">
                            <input type="checkbox" name="is_active" class="custom-switch-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">Active</span>
                        </label>
                    </div>
                    <div class="d-flex">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            Save {{ $parentId ? 'Subcategory' : 'Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


