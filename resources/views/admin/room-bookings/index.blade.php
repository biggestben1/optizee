@extends('layouts.admin')

@section('title', 'Room Bookings')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Room Bookings</li>
@endsection

@section('actions')
<a href="{{ route('admin.pos.index') }}" class="btn btn-primary">
    <i class="fe fe-calendar me-2"></i> New Booking
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">All Room Bookings</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th>Booking #</th>
                                <th>Room</th>
                                <th>Customer</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Nights</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                            <tr>
                                <td>
                                    <strong>{{ $booking->booking_number }}</strong>
                                    @if($booking->is_full_suite_booking)
                                    <br><span class="badge bg-info">Full Suite</span>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->room)
                                    <strong>{{ $booking->room->room_number ?? 'N/A' }}</strong>
                                    @if($booking->room->room_name)
                                    <br><small class="text-muted">{{ $booking->room->room_name }}</small>
                                    @endif
                                    <br><small class="text-muted">{{ $booking->room->category->name ?? 'N/A' }}</small>
                                    @else
                                    <span class="text-danger">Room Deleted</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $booking->guest_name ?: ($booking->customer->name ?? 'Walk-in') }}
                                    @if($booking->guest_phone || ($booking->customer && $booking->customer->phone))
                                    <br><small class="text-muted">{{ $booking->guest_phone ?: $booking->customer->phone }}</small>
                                    @endif
                                    @if(($booking->booking_source ?? 'walkin') === 'online')
                                    <br><span class="badge bg-info">Online</span>
                                    @elseif(($booking->booking_source ?? null) === 'credit' || $booking->is_credit_booking)
                                    <br><span class="badge bg-warning">Credit</span>
                                    @endif
                                </td>
                                <td>{{ $booking->check_in_date ? $booking->check_in_date->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $booking->check_out_date ? $booking->check_out_date->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $booking->number_of_nights }}</td>
                                <td>
                                    <strong>₦{{ number_format($booking->total, 2) }}</strong>
                                    @if($booking->discount > 0)
                                    <br><small class="text-success">Discount: ₦{{ number_format($booking->discount, 2) }}</small>
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
                                    <span class="badge bg-danger" title="Cancelled bookings are excluded from daily reports">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.room-bookings.receipt', $booking) }}" class="btn btn-sm btn-info" title="View Receipt">
                                            <i class="fe fe-file-text"></i>
                                        </a>
                                        @if($booking->status === 'confirmed')
                                        <form action="{{ route('admin.room-bookings.check-in', $booking) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Check in this guest?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Check In">
                                                <i class="fe fe-log-in"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @if(!in_array($booking->status, ['checked-out', 'cancelled']))
                                        <a href="{{ route('admin.room-bookings.checkout', $booking) }}" class="btn btn-sm btn-warning" title="Check Out Guest">
                                            <i class="fe fe-log-out me-1"></i>Check Out
                                        </a>
                                        @endif
                                        @if(in_array($booking->status, ['pending', 'confirmed']))
                                        <button type="button" class="btn btn-sm btn-danger" title="Cancel" 
                                                onclick="cancelBooking({{ $booking->id }})">
                                            <i class="fe fe-x"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fe fe-calendar text-muted" style="font-size: 48px;"></i>
                                    <h5 class="mt-3">No Bookings Found</h5>
                                    <p class="text-muted">Create your first room booking to get started.</p>
                                    <a href="{{ route('admin.pos.index') }}" class="btn btn-primary">
                                        <i class="fe fe-calendar me-1"></i> Create Booking
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($bookings->hasPages())
                <div class="mt-3">
                    {{ $bookings->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div class="modal fade" id="cancelBookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="cancelBookingForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this booking?</p>
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Cancellation Reason</label>
                        <textarea name="reason" id="cancellation_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function cancelBooking(bookingId) {
    const form = document.getElementById('cancelBookingForm');
    form.action = `/admin/room-bookings/${bookingId}/cancel`;
    const modal = new bootstrap.Modal(document.getElementById('cancelBookingModal'));
    modal.show();
}
</script>
@endpush

