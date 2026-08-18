@extends('layouts.admin')

@section('title', 'Categories')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Categories</li>
@endsection

@section('actions')
@if(auth()->user()->is_admin || auth()->user()->isStorekeeper())
<a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i> Add Category
</a>
@endif
@endsection

@section('content')
<!-- Parent Categories with Subcategories -->
@if($parentCategories->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Categories & Subcategories</h3>
            </div>
            <div class="card-body">
                @foreach($parentCategories as $parent)
                <div class="mb-4 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="mb-1">
                                <i class="fe fe-folder text-primary me-2"></i>
                                <a href="{{ route('admin.products.index', ['category' => $parent->id]) }}" class="text-decoration-none">
                                    <strong>{{ $parent->name }}</strong>
                                </a>
                                <a href="{{ route('admin.products.index', ['category' => $parent->id]) }}" class="badge bg-primary-transparent text-primary ms-2 text-decoration-none">
                                    {{ $parent->products_count }} products
                                </a>
                            </h5>
                            @if($parent->description)
                            <p class="text-muted mb-2">{{ $parent->description }}</p>
                            @endif
                        </div>
                        @if(auth()->user()->is_admin || auth()->user()->isStorekeeper())
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.categories.create', ['parent_id' => $parent->id]) }}" 
                               class="btn btn-sm btn-outline-success" title="Add Subcategory">
                                <i class="fe fe-plus"></i> Add Subcategory
                            </a>
                            <a href="{{ route('admin.categories.edit', $parent) }}" 
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fe fe-edit"></i>
                            </a>
                            <form action="{{ route('admin.categories.destroy', $parent) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirm('Are you sure? This will delete all subcategories too!')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fe fe-trash-2"></i>
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                    
                    @if($parent->children->count() > 0)
                    <div class="ms-4 mt-2">
                        <h6 class="text-muted mb-2">Subcategories:</h6>
                        <div class="row">
                            @foreach($parent->children as $child)
                            <div class="col-md-4 mb-2">
                                    <div class="card border-left-info">
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <a href="{{ route('admin.products.index', ['category' => $child->id]) }}" class="text-decoration-none">
                                                        <strong class="text-info">
                                                            <i class="fe fe-arrow-right me-1"></i>
                                                            {{ $child->name }}
                                                        </strong>
                                                    </a>
                                                    <br>
                                                    <a href="{{ route('admin.products.index', ['category' => $child->id]) }}" class="text-decoration-none">
                                                        <small class="text-muted">{{ $child->products_count }} products</small>
                                                    </a>
                                                </div>
                                            @if(auth()->user()->is_admin || auth()->user()->isStorekeeper())
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('admin.categories.edit', $child) }}" 
                                                   class="btn btn-xs btn-outline-primary" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.categories.destroy', $child) }}" method="POST" 
                                                      class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="ms-4">
                        <small class="text-muted">
                            <i class="fe fe-info me-1"></i>
                            No subcategories yet.
                            @if(auth()->user()->is_admin || auth()->user()->isStorekeeper())
                            <a href="{{ route('admin.categories.create', ['parent_id' => $parent->id]) }}">Add one</a>
                            @endif
                        </small>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- All Categories Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">All Categories</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Type</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allCategories as $category)
                            <tr>
                                <td>
                                    @if($category->parent)
                                    <a href="{{ route('admin.products.index', ['category' => $category->parent->id]) }}" class="text-muted text-decoration-none">
                                        {{ $category->parent->name }}
                                    </a> >
                                    @endif
                                    <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="text-decoration-none">
                                        <strong>{{ $category->name }}</strong>
                                    </a>
                                    @if($category->description)
                                    <br><small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $category->department === 'hotel' ? 'bg-warning' : 'bg-success' }}">
                                        {{ $category->department === 'hotel' ? 'Hotel' : 'Bar n Kitchen' }}
                                    </span>
                                </td>
                                <td>
                                    @if($category->isSubcategory())
                                    <span class="badge bg-info">Subcategory</span>
                                    @else
                                    <span class="badge bg-primary">Category</span>
                                    @endif
                                </td>
                                <td>{{ $category->products_count }}</td>
                                <td>
                                    @if($category->is_active)
                                    <span class="badge bg-success">Active</span>
                                    @else
                                    <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $category->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if(auth()->user()->is_admin || auth()->user()->isStorekeeper())
                                        @if($category->isParent())
                                        <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}" 
                                           class="btn btn-sm btn-outline-success" title="Add Subcategory">
                                            <i class="fe fe-plus"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-primary">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fe fe-trash-2"></i>
                                            </button>
                                        </form>
                                    @else
                                    <span class="text-muted">View only</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fe fe-folder" style="font-size: 32px;"></i>
                                    <p class="mt-2 mb-0">No categories found</p>
                                    @if(auth()->user()->is_admin || auth()->user()->isStorekeeper())
                                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary mt-3">
                                        <i class="fe fe-plus me-1"></i> Create Category
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $allCategories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection


