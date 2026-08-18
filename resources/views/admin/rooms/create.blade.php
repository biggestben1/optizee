@extends('layouts.admin')

@section('title', 'Add New Room')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Rooms</a></li>
<li class="breadcrumb-item active" aria-current="page">Add Room</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 col-xl-6 mx-auto">
        <div class="card">
            <div class="card-header bg-success-transparent">
                <h4 class="card-title mb-0">
                    <i class="fe fe-plus me-2"></i>Add New Room
                </h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.rooms.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Room Category <span class="text-danger">*</span></label>
                        <select name="room_category_id" class="form-select @error('room_category_id') is-invalid @enderror" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('room_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                                @if($category->is_suite)
                                (Suite)
                                @else
                                (₦{{ number_format($category->price_per_room, 2) }}/night)
                                @endif
                            </option>
                            @endforeach
                        </select>
                        @error('room_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Room Number <span class="text-danger">*</span></label>
                        <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror" 
                               value="{{ old('room_number') }}" placeholder="e.g., STD-01, E-05, SUITE-MB1" required>
                        @error('room_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Unique room identifier</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Room Name (Optional)</label>
                        <input type="text" name="room_name" class="form-control @error('room_name') is-invalid @enderror" 
                               value="{{ old('room_name') }}" placeholder="e.g., Master Bedroom 1, Deluxe Room">
                        @error('room_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hourly rate (₦) (optional)</label>
                        <input type="number" name="hourly_rate" class="form-control @error('hourly_rate') is-invalid @enderror"
                               value="{{ old('hourly_rate') }}" step="0.01" min="0" placeholder="Leave empty for category default or nightly ÷ 24">
                        @error('hourly_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">For short-stay bookings. Optional if the category has an hourly rate or you use the default from nightly price.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="booked" {{ old('status') == 'booked' ? 'selected' : '' }}>Booked</option>
                            <option value="checked-in" {{ old('status') == 'checked-in' ? 'selected' : '' }}>Checked In</option>
                            <option value="checked-out" {{ old('status') == 'checked-out' ? 'selected' : '' }}>Checked Out</option>
                            <option value="cleaning" {{ old('status') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                            <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Parent Suite (Optional - for suite sub-rooms)</label>
                        <select name="parent_suite_id" class="form-select @error('parent_suite_id') is-invalid @enderror">
                            <option value="">None (Main Room)</option>
                            @foreach($categories as $category)
                                @if($category->is_suite)
                                    @foreach($category->rooms->whereNull('parent_suite_id') as $suite)
                                    <option value="{{ $suite->id }}" {{ old('parent_suite_id') == $suite->id ? 'selected' : '' }}>
                                        {{ $suite->room_number }} - {{ $category->name }}
                                    </option>
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                        @error('parent_suite_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Only select if this is a sub-room within a suite</small>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
                            <i class="fe fe-x me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fe fe-check me-2"></i>Create Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection




