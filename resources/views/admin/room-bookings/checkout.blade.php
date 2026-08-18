@extends('layouts.admin')

@section('title', 'Check Out Guest')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.room-bookings.index') }}">Room Bookings</a></li>
<li class="breadcrumb-item active" aria-current="page">Check Out</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 col-xl-6 mx-auto">
        <div class="card">
            <div class="card-header bg-warning-transparent">
                <h4 class="card-title mb-0">
                    <i class="fe fe-log-out me-2"></i>Check Out Guest
                </h4>
            </div>
            <div class="card-body">
                <!-- Booking Information -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="mb-3 text-primary"><i class="fe fe-info me-2"></i>Booking Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Booking Number:</strong> {{ $roomBooking->booking_number }}</p>
                            @if($roomBooking->room)
                            <p class="mb-2"><strong>Room:</strong> {{ $roomBooking->room->room_number }}</p>
                            @if($roomBooking->room->room_name)
                            <p class="mb-2"><strong>Room Name:</strong> {{ $roomBooking->room->room_name }}</p>
                            @endif
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Guest:</strong> {{ $roomBooking->customer->name ?? 'Walk-in Guest' }}</p>
                            <p class="mb-2"><strong>Check-in Date:</strong> {{ $roomBooking->check_in_date ? $roomBooking->check_in_date->format('F d, Y') : 'N/A' }}</p>
                            <p class="mb-2"><strong>Original Check-out:</strong> {{ $roomBooking->check_out_date ? $roomBooking->check_out_date->format('F d, Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stay Summary -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="mb-3 text-primary"><i class="fe fe-calendar me-2"></i>Stay Summary</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h6 class="text-muted mb-1">Original Nights</h6>
                                <h4 class="mb-0">{{ $roomBooking->number_of_nights }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-info-transparent rounded">
                                <h6 class="text-muted mb-1">Actual Nights Stayed</h6>
                                <h4 class="mb-0 text-info">{{ $actualNights }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h6 class="text-muted mb-1">Check-out Date</h6>
                                <h6 class="mb-0">{{ $checkOutDate->format('M d, Y') }}</h6>
                                <small class="text-muted">{{ $checkOutDate->format('h:i A') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Original Billing -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="mb-3 text-primary"><i class="fe fe-dollar-sign me-2"></i>Original Billing</h5>
                    <table class="table table-sm table-bordered">
                        <tbody>
                            <tr>
                                <td><strong>Room Price per Night:</strong></td>
                                <td class="text-end">₦{{ number_format($roomBooking->room_price_per_night, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Subtotal ({{ $roomBooking->number_of_nights }} nights):</strong></td>
                                <td class="text-end">₦{{ number_format($roomBooking->subtotal, 2) }}</td>
                            </tr>
                            @if($roomBooking->discount > 0)
                            <tr>
                                <td>Discount:</td>
                                <td class="text-end text-success">-₦{{ number_format($roomBooking->discount, 2) }}</td>
                            </tr>
                            @endif
                            @if($roomBooking->tax > 0)
                            <tr>
                                <td>Tax (VAT):</td>
                                <td class="text-end">₦{{ number_format($roomBooking->tax, 2) }}</td>
                            </tr>
                            @endif
                            @if($roomBooking->service_charge > 0)
                            <tr>
                                <td>Service Charge:</td>
                                <td class="text-end">₦{{ number_format($roomBooking->service_charge, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <td><strong>TOTAL PAID:</strong></td>
                                <td class="text-end"><strong>₦{{ number_format($roomBooking->total, 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Important Notice -->
                <div class="alert alert-warning mb-4">
                    <h6 class="alert-heading"><i class="fe fe-alert-triangle me-2"></i>Check Out Confirmation</h6>
                    <p class="mb-2">You are about to check out this guest. This will:</p>
                    <ul class="mb-0">
                        <li>Mark the booking as "Checked Out"</li>
                        <li>Set the room status to "Available"</li>
                        <li>Update the checkout date to today</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 justify-content-center">
                    <form action="{{ route('admin.room-bookings.check-out', $roomBooking) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Confirm checkout? The room will be marked as available.');">
                            <i class="fe fe-check me-2"></i>Confirm Check Out
                        </button>
                    </form>
                    <a href="{{ route('admin.room-bookings.index') }}" class="btn btn-secondary btn-lg">
                        <i class="fe fe-x me-2"></i>Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




