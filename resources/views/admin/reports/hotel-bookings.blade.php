@extends('layouts.admin')

@section('title', 'Hotel Bookings Report')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active" aria-current="page">Hotel Bookings</li>
@endsection

@section('actions')
<a href="{{ route('admin.reports.export.hotel-bookings', request()->all()) }}" class="btn btn-success">
    <i class="fe fe-download me-2"></i>Export CSV
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Date Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.hotel-bookings') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fe fe-filter me-2"></i>Filter
                        </button>
                        <a href="{{ route('admin.reports.hotel-bookings') }}" class="btn btn-secondary">
                            <i class="fe fe-refresh-cw me-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total Revenue</h6>
                                <h3 class="mb-0">₦{{ number_format((float)$summary['total_revenue'], 2) }}</h3>
                            </div>
                            <i class="fe fe-dollar-sign" style="font-size: 40px; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total Bookings</h6>
                                <h3 class="mb-0">{{ $summary['total_bookings'] }}</h3>
                            </div>
                            <i class="fe fe-calendar" style="font-size: 40px; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Average Booking</h6>
                                <h3 class="mb-0">₦{{ number_format((float)$summary['average_booking'], 2) }}</h3>
                            </div>
                            <i class="fe fe-trending-up" style="font-size: 40px; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total Nights</h6>
                                <h3 class="mb-0">{{ $summary['total_nights'] }} nights</h3>
                            </div>
                            <i class="fe fe-moon" style="font-size: 40px; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Booking Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Confirmed:</span>
                                    <strong class="text-primary">{{ $summary['confirmed_bookings'] }}</strong>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Checked In:</span>
                                    <strong class="text-success">{{ $summary['checked_in_bookings'] }}</strong>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Checked Out:</span>
                                    <strong class="text-secondary">{{ $summary['checked_out_bookings'] }}</strong>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Cancelled:</span>
                                    <strong class="text-danger">{{ $summary['cancelled_bookings'] }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Payment Methods</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Cash:</span>
                                    <strong>₦{{ number_format((float)$summary['cash_bookings'], 2) }}</strong>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Transfer:</span>
                                    <strong>₦{{ number_format((float)$summary['transfer_bookings'], 2) }}</strong>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>POS:</span>
                                    <strong>₦{{ number_format((float)$summary['pos_bookings'], 2) }}</strong>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Credit:</span>
                                    <strong>₦{{ number_format((float)$summary['credit_bookings'], 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Types -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Booking Types</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Overnight Stay:</span>
                            <strong>{{ $summary['overnight_bookings'] }} bookings</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Nights:</span>
                            <strong>{{ $summary['total_nights'] }} nights</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Short Stay:</span>
                            <strong>{{ $summary['short_stay_bookings'] }} bookings</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Total Hours:</span>
                            <strong>{{ $summary['total_hours'] }} hours</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Bookings by Category</h5>
                    </div>
                    <div class="card-body">
                        @forelse($bookingsByCategory as $category => $data)
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ $category }}:</span>
                            <div class="text-end">
                                <strong>{{ $data['count'] }} bookings</strong><br>
                                <small class="text-muted">₦{{ number_format((float)$data['revenue'], 2) }}</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted mb-0">No bookings by category</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Breakdown -->
        @if($dailyBookings->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Daily Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Bookings</th>
                                <th>Overnight</th>
                                <th>Short Stay</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyBookings as $date => $data)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</td>
                                <td>{{ $data['count'] }}</td>
                                <td>{{ $data['overnight'] }}</td>
                                <td>{{ $data['short_stay'] }}</td>
                                <td>₦{{ number_format((float)$data['total'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Bookings Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">All Bookings</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Booking #</th>
                                <th>Date</th>
                                <th>Room</th>
                                <th>Category</th>
                                <th>Guest</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Staff</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.room-bookings.receipt', $booking) }}" class="text-primary">
                                        {{ $booking->booking_number }}
                                    </a>
                                </td>
                                <td>{{ $booking->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($booking->room)
                                    <strong>{{ $booking->room->room_number }}</strong>
                                    @if($booking->room->room_name)
                                    <br><small class="text-muted">{{ $booking->room->room_name }}</small>
                                    @endif
                                    @else
                                    <span class="text-danger">Room Deleted</span>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->room && $booking->room->category)
                                    {{ $booking->room->category->name }}
                                    @else
                                    <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $booking->customer ? $booking->customer->name : 'Walk-in' }}</td>
                                <td>
                                    {{ $booking->check_in_date ? $booking->check_in_date->format('M d, Y') : 'N/A' }}
                                    @if($booking->check_in_time)
                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($booking->check_in_time)->format('h:i A') }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $booking->check_out_date ? $booking->check_out_date->format('M d, Y') : 'N/A' }}
                                    @if($booking->check_out_time)
                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($booking->check_out_time)->format('h:i A') }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->booking_type === 'short-stay')
                                    <span class="badge bg-info">Short Stay</span>
                                    @else
                                    <span class="badge bg-primary">Overnight</span>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->booking_type === 'short-stay')
                                    {{ $booking->hours_stayed }} hour(s)
                                    @else
                                    {{ $booking->number_of_nights }} night(s)
                                    @endif
                                </td>
                                <td><strong>₦{{ number_format((float)$booking->total, 2) }}</strong></td>
                                <td>₦{{ number_format((float)$booking->amount_paid, 2) }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($booking->payment_method) }}</span>
                                    @if($booking->is_credit_booking)
                                    <br><small class="text-warning">Credit</small>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->status === 'confirmed')
                                    <span class="badge bg-primary">Confirmed</span>
                                    @elseif($booking->status === 'checked-in')
                                    <span class="badge bg-success">Checked In</span>
                                    @elseif($booking->status === 'checked-out')
                                    <span class="badge bg-secondary">Checked Out</span>
                                    @elseif($booking->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                    @elseif($booking->status === 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td>{{ $booking->user ? $booking->user->name : 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="14" class="text-center text-muted py-4">
                                    <i class="fe fe-home" style="font-size: 48px;"></i>
                                    <p class="mt-2 mb-0">No bookings found for the selected period.</p>
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




