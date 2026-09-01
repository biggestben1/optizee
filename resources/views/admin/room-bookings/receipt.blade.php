@extends('layouts.admin')

@section('title', 'Booking Receipt')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.room-bookings.index') }}">Room Bookings</a></li>
<li class="breadcrumb-item active" aria-current="page">Receipt</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 col-xl-6 mx-auto">
        <div class="card">
            <div class="card-header bg-primary-transparent">
                <h4 class="card-title mb-0">Booking Receipt</h4>
            </div>
            <div class="card-body" id="receipt-content">
                <!-- Header Section -->
                <div class="text-center mb-4 pb-3 border-bottom">
                    <img src="{{ asset('logo.jpg') }}" alt="Optizee Hotel and Suites" style="max-width: 150px; max-height: 80px; margin-bottom: 15px;">
                    <h4>Optizee Hotel and Suites</h4>
                    <p class="mb-1"><strong>Booking Number:</strong> {{ $roomBooking->booking_number }}</p>
                    <p class="mb-0 text-muted">{{ $roomBooking->created_at ? $roomBooking->created_at->format('F d, Y h:i A') : 'N/A' }}</p>
                </div>

                <!-- Room Details Section -->
                @if($roomBooking->room)
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="mb-3 text-primary"><i class="fe fe-home me-2"></i>Room Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Room Number:</strong> {{ $roomBooking->room->room_number ?? 'N/A' }}</p>
                            @if($roomBooking->room->room_name)
                            <p class="mb-2"><strong>Room Name:</strong> {{ $roomBooking->room->room_name }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Category:</strong> {{ ($roomBooking->room && $roomBooking->room->category) ? $roomBooking->room->category->name : 'N/A' }}</p>
                            @if($roomBooking->is_full_suite_booking)
                            <p class="mb-2"><span class="badge bg-info">Full Suite Booking</span></p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Guest Information Section -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="mb-3 text-primary"><i class="fe fe-user me-2"></i>Guest Information</h5>
                    <p class="mb-1"><strong>Name:</strong> {{ $roomBooking->customer->name ?? 'Walk-in Guest' }}</p>
                    @if($roomBooking->customer)
                    <p class="mb-0"><strong>Phone:</strong> {{ $roomBooking->customer->phone }}</p>
                    @endif
                </div>

                <!-- Booking Period Section -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="mb-3 text-primary"><i class="fe fe-calendar me-2"></i>Booking Period</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Check-in:</strong><br>{{ $roomBooking->check_in_date ? $roomBooking->check_in_date->format('F d, Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Check-out:</strong><br>{{ $roomBooking->check_out_date ? $roomBooking->check_out_date->format('F d, Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2"><strong>Nights:</strong><br>{{ $roomBooking->number_of_nights }} night(s)</p>
                        </div>
                    </div>
                </div>

                <!-- Billing Details Section -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="mb-3 text-primary"><i class="fe fe-dollar-sign me-2"></i>Billing Details</h5>
                    <table class="table table-sm table-bordered">
                        <tbody>
                            @if($roomBooking->booking_type === 'short_stay')
                            <tr>
                                <td><strong>Hourly Rate:</strong></td>
                                <td class="text-end">₦{{ number_format((float)$roomBooking->hourly_rate, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Hours Stayed:</strong></td>
                                <td class="text-end">{{ $roomBooking->hours_stayed ?? 0 }} hour(s)</td>
                            </tr>
                            <tr>
                                <td><strong>Subtotal:</strong></td>
                                <td class="text-end">₦{{ number_format((float)$roomBooking->subtotal, 2) }}</td>
                            </tr>
                            @else
                            <tr>
                                <td><strong>Room Price per Night:</strong></td>
                                <td class="text-end">₦{{ number_format((float)$roomBooking->room_price_per_night, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Subtotal ({{ $roomBooking->number_of_nights ?? 0 }} night(s)):</strong></td>
                                <td class="text-end">₦{{ number_format((float)$roomBooking->subtotal, 2) }}</td>
                            </tr>
                            @endif
                            @if((float)$roomBooking->discount > 0)
                            <tr>
                                <td>Discount:</td>
                                <td class="text-end text-success">-₦{{ number_format((float)$roomBooking->discount, 2) }}</td>
                            </tr>
                            @endif
                            @if((float)$roomBooking->tax > 0)
                            <tr>
                                <td>Tax (VAT):</td>
                                <td class="text-end">₦{{ number_format((float)$roomBooking->tax, 2) }}</td>
                            </tr>
                            @endif
                            @if((float)$roomBooking->service_charge > 0)
                            <tr>
                                <td>Service Charge:</td>
                                <td class="text-end">₦{{ number_format((float)$roomBooking->service_charge, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <td><strong>TOTAL:</strong></td>
                                <td class="text-end"><strong>₦{{ number_format((float)$roomBooking->total, 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Payment Details Section -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="mb-3 text-primary"><i class="fe fe-credit-card me-2"></i>Payment Details</h5>
                    <table class="table table-sm table-bordered">
                        <tbody>
                            <tr>
                                <td><strong>Amount Paid:</strong></td>
                                <td class="text-end">₦{{ number_format((float)$roomBooking->amount_paid, 2) }}</td>
                            </tr>
                            @if((float)$roomBooking->change > 0)
                            <tr>
                                <td><strong>Change:</strong></td>
                                <td class="text-end">₦{{ number_format((float)$roomBooking->change, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Payment Method:</strong></td>
                                <td class="text-end"><strong>{{ strtoupper($roomBooking->payment_method) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Status & Staff Section -->
                <div class="mb-4 pb-3 border-bottom">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Status:</strong> 
                                @if($roomBooking->status === 'confirmed')
                                <span class="badge bg-primary">Confirmed</span>
                                @elseif($roomBooking->status === 'checked-in')
                                <span class="badge bg-success">Checked In</span>
                                @elseif($roomBooking->status === 'checked-out')
                                <span class="badge bg-secondary">Checked Out</span>
                                @elseif($roomBooking->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                                @elseif($roomBooking->status === 'cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-0"><strong>Served by:</strong> {{ $roomBooking->user->name ?? 'Unknown' }}</p>
                        </div>
                    </div>
                </div>

                @if($roomBooking->notes)
                <!-- Notes Section -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="mb-3 text-primary"><i class="fe fe-file-text me-2"></i>Notes</h5>
                    <p class="mb-0">{{ $roomBooking->notes }}</p>
                </div>
                @endif

                <!-- Footer Section -->
                <div class="text-center mt-4 pt-3 border-top">
                    <p class="mb-3"><strong>Thank you for choosing Optizee Hotel and Suites!</strong></p>
                    
                    @php
                        $bankName = \App\Models\Setting::getValue('bank.name', '');
                        $accountName = \App\Models\Setting::getValue('bank.account_name', '');
                        $accountNumber = \App\Models\Setting::getValue('bank.account_number', '');
                    @endphp
                    @if($bankName || $accountName || $accountNumber)
                    <div class="mt-4 pt-3 border-top">
                        <p class="mb-2"><strong>Bank Transfer Details:</strong></p>
                        @if($bankName)<p class="mb-1"><strong>Bank:</strong> {{ $bankName }}</p>@endif
                        @if($accountNumber)<p class="mb-1"><strong>Account Number:</strong> {{ $accountNumber }}</p>@endif
                        @if($accountName)<p class="mb-0"><strong>Account Name:</strong> {{ $accountName }}</p>@endif
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fe fe-printer me-2"></i> Print Receipt
                    </button>
                    @if($roomBooking->status === 'checked-in')
                    <a href="{{ route('admin.room-bookings.checkout', $roomBooking) }}" class="btn btn-warning">
                        <i class="fe fe-log-out me-2"></i> Check Out
                    </a>
                    @endif
                    <a href="{{ route('admin.room-bookings.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i> Back to Bookings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .card-header, .card-footer, .breadcrumb, .btn, .app-sidebar, .app-header {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    body {
        background: white !important;
    }
}
</style>
@endpush

