@extends('layouts.admin')

@section('title', 'Edit Room')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Rooms</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit Room</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 col-xl-6 mx-auto">
        <div class="card">
            <div class="card-header bg-warning-transparent">
                <h4 class="card-title mb-0">
                    <i class="fe fe-edit me-2"></i>Edit Room: {{ $room->room_number }}
                </h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.rooms.update', $room) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Room Category <span class="text-danger">*</span></label>
                        <select name="room_category_id" class="form-select @error('room_category_id') is-invalid @enderror" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('room_category_id', $room->room_category_id) == $category->id ? 'selected' : '' }}>
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
                               value="{{ old('room_number', $room->room_number) }}" required>
                        @error('room_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Room Name (Optional)</label>
                        <input type="text" name="room_name" class="form-control @error('room_name') is-invalid @enderror" 
                               value="{{ old('room_name', $room->room_name) }}">
                        @error('room_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hourly rate override (₦) (optional)</label>
                        <input type="number" name="hourly_rate" class="form-control @error('hourly_rate') is-invalid @enderror"
                               value="{{ old('hourly_rate', $room->hourly_rate) }}" step="0.01" min="0" placeholder="Leave empty to use category or nightly ÷ 24">
                        @error('hourly_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">For short-stay (hourly) bookings. Empty = category hourly, or effective nightly price ÷ 24.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="available" {{ old('status', $room->status) == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="booked" {{ old('status', $room->status) == 'booked' ? 'selected' : '' }}>Booked</option>
                            <option value="checked-in" {{ old('status', $room->status) == 'checked-in' ? 'selected' : '' }}>Checked In</option>
                            <option value="checked-out" {{ old('status', $room->status) == 'checked-out' ? 'selected' : '' }}>Checked Out</option>
                            <option value="cleaning" {{ old('status', $room->status) == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                            <option value="maintenance" {{ old('status', $room->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
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
                                    @foreach($category->rooms->whereNull('parent_suite_id')->where('id', '!=', $room->id) as $suite)
                                    <option value="{{ $suite->id }}" {{ old('parent_suite_id', $room->parent_suite_id) == $suite->id ? 'selected' : '' }}>
                                        {{ $suite->room_number }} - {{ $category->name }}
                                    </option>
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                        @error('parent_suite_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" 
                                   {{ old('is_active', $room->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active (Room is available for booking)
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong>Current Pricing:</strong><br>
                        @if($room->category->is_suite)
                        Full Suite: ₦{{ number_format($room->category->full_suite_price ?? 0, 2) }}<br>
                        @endif
                        @if($room->category->price_per_room)
                        Per Room: ₦{{ number_format($room->category->price_per_room, 2) }}/night<br>
                        @endif
                        @if($room->category->hourly_rate)
                        Category hourly: ₦{{ number_format($room->category->hourly_rate, 2) }}/hour<br>
                        @endif
                        <strong>Effective hourly (this room):</strong> ₦{{ number_format($room->getEffectiveHourlyRate(), 2) }}/hour<br>
                        <small class="text-muted mt-2 d-block">Override hourly above, or edit the category for all rooms in that category.</small>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
                            <i class="fe fe-x me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fe fe-save me-2"></i>Update Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

