@extends('layouts.admin')

@section('title', 'Rooms')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Rooms</li>
@endsection

@section('actions')
<a href="{{ route('admin.rooms.create') }}" class="btn btn-success">
    <i class="fe fe-plus me-2"></i> Add Room
</a>
<a href="{{ route('admin.hotel-pos.index') }}" class="btn btn-primary">
    <i class="fe fe-calendar me-2"></i> Book Room
</a>
@endsection

@section('content')
@foreach($categories as $category)
<div class="card mb-4">
    <div class="card-header bg-primary-transparent">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">
                    <i class="fe fe-home me-2"></i>{{ $category->name }}
                    <a href="{{ route('admin.room-categories.edit', $category) }}" class="btn btn-sm btn-warning ms-2" title="Edit Category & Prices">
                        <i class="fe fe-edit"></i>
                    </a>
                </h4>
                @if($category->is_suite)
                <small class="text-muted">
                    Full Suite: ₦{{ number_format($category->full_suite_price, 2) }} | 
                    Individual Rooms Available
                </small>
                @else
                <small class="text-muted">
                    From ₦{{ number_format($category->price_per_room, 2) }}/night
                    @if($category->hourly_rate)
                    · Hourly: ₦{{ number_format($category->hourly_rate, 2) }}/hr
                    @else
                    · Est. hourly: ₦{{ number_format($category->price_per_room / 24, 2) }}/hr (if not set per room)
                    @endif
                </small>
                @endif
            </div>
            <span class="badge bg-primary">{{ $category->rooms->count() }} Rooms</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($category->rooms as $room)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                <div class="card h-100 {{ $room->status === 'booked' || $room->status === 'checked-in' ? 'border-danger' : ($room->status === 'cleaning' || $room->status === 'maintenance' ? 'border-warning' : 'border-success') }}">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="card-title mb-0">
                            <i class="fe fe-home me-1"></i>
                            {{ $room->room_number }}
                        </h6>
                        @if($room->status === 'available')
                        <span class="badge bg-success">Available</span>
                        @elseif($room->status === 'booked')
                        <span class="badge bg-danger">Booked</span>
                        @elseif($room->status === 'checked-in')
                        <span class="badge bg-primary">Checked In</span>
                        @elseif($room->status === 'checked-out')
                        <span class="badge bg-secondary">Checked Out</span>
                        @elseif($room->status === 'cleaning')
                        <span class="badge bg-warning">Cleaning</span>
                        @elseif($room->status === 'maintenance')
                        <span class="badge bg-dark">Maintenance</span>
                        @endif
                    </div>
                    <div class="card-body py-2">
                        @if($room->room_name)
                        <p class="mb-2"><strong>{{ $room->room_name }}</strong></p>
                        @endif
                        
                        <div class="mb-2">
                            <i class="fe fe-dollar-sign text-success me-1"></i>
                            <strong>₦{{ number_format($room->getEffectivePrice(), 2) }}</strong> / night
                        </div>
                        <div class="mb-2">
                            <i class="fe fe-clock text-info me-1"></i>
                            <strong>₦{{ number_format($room->getEffectiveHourlyRate(), 2) }}</strong> / hour
                            @if($room->hourly_rate)
                            <span class="badge bg-info-transparent text-info ms-1" title="This room has a custom hourly rate">custom</span>
                            @endif
                        </div>

                        @if($room->is_suite_sub_room)
                        <div class="mb-2">
                            <span class="badge bg-info">Suite Sub-Room</span>
                        </div>
                        @endif

                        @php
                            $activeBooking = $room->activeBookings()->first();
                        @endphp

                        @if($activeBooking)
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fe fe-calendar me-1"></i>
                                Check-in: {{ $activeBooking->check_in_date->format('M d, Y') }}<br>
                                Check-out: {{ $activeBooking->check_out_date->format('M d, Y') }}<br>
                                <i class="fe fe-user me-1"></i>
                                {{ $activeBooking->customer->name ?? 'Walk-in' }}
                            </small>
                        </div>
                        @endif
                    </div>
                    <div class="card-footer py-2">
                        <div class="d-flex gap-1 flex-wrap">
                            @if($room->isAvailable())
                            <a href="{{ route('admin.hotel-pos.index') }}?room_id={{ $room->id }}" class="btn btn-sm btn-success">
                                <i class="fe fe-calendar"></i> Book
                            </a>
                            @elseif($activeBooking)
                            <a href="{{ route('admin.room-bookings.index') }}?booking_id={{ $activeBooking->id }}" class="btn btn-sm btn-primary">
                                <i class="fe fe-eye"></i> View Booking
                            </a>
                            @endif
                            
                            @if($room->status === 'checked-in' && $activeBooking)
                            <a href="{{ route('admin.room-bookings.checkout', $activeBooking) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fe fe-log-out"></i> Check Out
                            </a>
                            @endif
                            
                            <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-sm btn-warning" title="Edit Room">
                                <i class="fe fe-edit"></i>
                            </a>
                            
                            <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" class="d-inline" 
                                  onsubmit="return confirm('Are you sure you want to delete room {{ $room->room_number }}? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete Room">
                                    <i class="fe fe-trash-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endforeach

@if($categories->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fe fe-home text-muted" style="font-size: 48px;"></i>
        <h4 class="mt-3">No Rooms Found</h4>
        <p class="text-muted">Run the room seeder to populate rooms.</p>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
            <i class="fe fe-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
</div>
@endif
@endsection

