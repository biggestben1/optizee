@extends('layouts.admin')

@section('title', 'Hall Booking Receipt')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.hall-rentals.index') }}">Hall Rentals</a></li>
<li class="breadcrumb-item active" aria-current="page">Receipt</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 col-xl-6 mx-auto">
        <div class="card">
            <div class="card-header bg-primary-transparent">
                <h4 class="card-title mb-0">Hall Booking Receipt</h4>
            </div>
            <div class="card-body">
                <div class="text-center mb-4 pb-3 border-bottom">
                    <img src="{{ asset('logo.jpg') }}" alt="Optizee Hotel and Suites" style="max-width: 150px; max-height: 80px; margin-bottom: 15px;">
                    <h4>Optizee Hotel and Suites</h4>
                    <p class="mb-1"><strong>Booking Number:</strong> {{ $booking->booking_number }}</p>
                    <p class="mb-0 text-muted">{{ $booking->created_at->format('F d, Y h:i A') }}</p>
                </div>

                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="mb-3 text-primary"><i class="fe fe-star me-2"></i>Event Details</h5>
                    <p class="mb-1"><strong>Occasion:</strong> {{ $booking->occasion_name }}</p>
                    <p class="mb-1"><strong>Event Date:</strong> {{ $booking->event_date->format('l, F d, Y') }}</p>
                    @if($booking->start_time)
                    <p class="mb-1"><strong>Time:</strong>
                        {{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }}
                        @if($booking->end_time) - {{ \Carbon\Carbon::parse($booking->end_time)->format('h:i A') }} @endif
                    </p>
                    @endif
                    @if($booking->expected_guests)
                    <p class="mb-0"><strong>Expected Guests:</strong> {{ number_format($booking->expected_guests) }}</p>
                    @endif
                </div>

                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="mb-3 text-primary"><i class="fe fe-user me-2"></i>Customer</h5>
                    <p class="mb-1"><strong>Name:</strong> {{ $booking->customer_name }}</p>
                    <p class="mb-1"><strong>Phone:</strong> {{ $booking->customer_phone }}</p>
                    @if($booking->customer_email)
                    <p class="mb-0"><strong>Email:</strong> {{ $booking->customer_email }}</p>
                    @endif
                </div>

                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="mb-3 text-primary"><i class="fe fe-credit-card me-2"></i>Payment</h5>
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <td>Hall Price:</td>
                                <td class="text-end">₦{{ number_format($booking->price, 2) }}</td>
                            </tr>
                            @if((float) $booking->discount > 0)
                            <tr>
                                <td>Discount:</td>
                                <td class="text-end text-success">-₦{{ number_format($booking->discount, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <td><strong>TOTAL:</strong></td>
                                <td class="text-end"><strong>₦{{ number_format($booking->total, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td>Amount Paid:</td>
                                <td class="text-end">₦{{ number_format($booking->amount_paid, 2) }}</td>
                            </tr>
                            @if((float) $booking->change > 0)
                            <tr>
                                <td>Change:</td>
                                <td class="text-end">₦{{ number_format($booking->change, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td>Payment Method:</td>
                                <td class="text-end"><strong>{{ strtoupper($booking->payment_method) }}</strong></td>
                            </tr>
                            @if($booking->payment_method === 'mixed')
                            @php
                                $splitRows = [
                                    'Cash' => (float) $booking->cash_amount + (float) $booking->change,
                                    'Transfer' => (float) $booking->transfer_amount,
                                    'POS' => (float) $booking->pos_amount,
                                ];
                            @endphp
                            @foreach($splitRows as $label => $value)
                            @if($value > 0)
                            <tr>
                                <td class="ps-4">{{ $label }}:</td>
                                <td class="text-end">₦{{ number_format($value, 2) }}</td>
                            </tr>
                            @endif
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mb-4 pb-3 border-bottom">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Status:</strong>
                                @if($booking->isCancelled())
                                <span class="badge bg-danger">Cancelled</span>
                                @else
                                <span class="badge bg-primary">Confirmed</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-0"><strong>Served by:</strong> {{ $booking->user->name ?? 'Unknown' }}</p>
                        </div>
                    </div>
                    @if($booking->isCancelled())
                    <p class="mb-0 text-danger small">
                        Cancelled {{ $booking->cancelled_at?->format('M d, Y h:i A') }}
                        @if($booking->cancelledBy) by {{ $booking->cancelledBy->name }} @endif
                        &mdash; {{ $booking->cancellation_reason }}
                    </p>
                    @endif
                </div>

                @if($booking->notes)
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="mb-3 text-primary"><i class="fe fe-file-text me-2"></i>Notes</h5>
                    <p class="mb-0">{{ $booking->notes }}</p>
                </div>
                @endif

                <div class="text-center mt-4">
                    <p class="mb-0"><strong>Thank you for choosing Optizee Hotel and Suites!</strong></p>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fe fe-printer me-2"></i> Print Receipt
                    </button>
                    <a href="{{ route('admin.hall-rentals.create') }}" class="btn btn-success">
                        <i class="fe fe-plus me-2"></i> New Hall Booking
                    </a>
                    <a href="{{ route('admin.hall-rentals.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i> Back to Hall Rentals
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
    .card-header, .card-footer, .breadcrumb, .btn, .app-sidebar, .app-header, .page-header {
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
