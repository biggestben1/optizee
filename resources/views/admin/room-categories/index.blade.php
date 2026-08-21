@extends('layouts.admin')

@section('title', 'Room Categories')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Room Categories</li>
@endsection

@section('actions')
<a href="{{ route('admin.room-categories.create') }}" class="btn btn-success">
    <i class="fe fe-plus me-2"></i> Add Category
</a>
<a href="{{ route('admin.rooms.index') }}" class="btn btn-primary">
    <i class="fe fe-home me-2"></i> View Rooms
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">All Room Categories</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Type</th>
                                <th>Price per Night</th>
                                <th>Hourly Rate</th>
                                <th>Full Suite Price</th>
                                <th>Total Rooms</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                            <tr>
                                <td>
                                    <strong>{{ $category->name }}</strong>
                                    @if($category->description)
                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($category->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($category->is_suite)
                                    <span class="badge bg-info">Suite</span>
                                    @else
                                    <span class="badge bg-primary">Standard</span>
                                    @endif
                                </td>
                                <td>
                                    @if($category->price_per_room)
                                    ₦{{ number_format((float)$category->price_per_room, 2) }}
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($category->hourly_rate)
                                    ₦{{ number_format((float)$category->hourly_rate, 2) }}
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($category->full_suite_price)
                                    ₦{{ number_format((float)$category->full_suite_price, 2) }}
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $category->rooms_count }} rooms</span>
                                </td>
                                <td>
                                    @if($category->is_active)
                                    <span class="badge bg-success">Active</span>
                                    @else
                                    <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.room-categories.edit', $category) }}" class="btn btn-sm btn-warning" title="Edit Category">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.room-categories.destroy', $category) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm(@json($category->rooms_count > 0 ? 'Delete '.$category->name.'? This will also delete '.$category->rooms_count.' room(s) and their bookings.' : 'Are you sure you want to delete '.$category->name.'?'));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Category">
                                                <i class="fe fe-trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fe fe-home" style="font-size: 48px;"></i>
                                    <p class="mt-2 mb-0">No room categories found.</p>
                                    <a href="{{ route('admin.room-categories.create') }}" class="btn btn-primary mt-3">
                                        <i class="fe fe-plus me-2"></i> Create First Category
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

