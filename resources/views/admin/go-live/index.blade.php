@extends('layouts.admin')

@section('title', 'Ready to Go Live')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Ready to Go Live</li>
@endsection

@section('content')
<div class="alert alert-warning">
    <i class="fe fe-alert-triangle me-2"></i>
    <strong>Before going live:</strong> Use these tools to remove test orders and customers from the system.
    These actions are permanent and cannot be undone.
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row mb-4" id="data-counts">
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h2 class="mb-0 text-danger" data-count="sales">{{ number_format($counts['sales']) }}</h2>
                <p class="mb-0 text-muted">Sales &amp; Pending Orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h2 class="mb-0 text-warning" data-count="table_guests">{{ number_format($counts['table_guests']) }}</h2>
                <p class="mb-0 text-muted">Active Table Guests</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <h2 class="mb-0 text-info" data-count="customers">{{ number_format($counts['customers']) }}</h2>
                <p class="mb-0 text-muted">Customers</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Clear All Orders</h4>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted">
                    Deletes all completed sales, pending POS orders, sale line items, and table guests.
                    Use this to remove test transactions before launch.
                </p>
                <ul class="small text-muted mb-4">
                    <li>{{ number_format($counts['sales']) }} sale(s) and pending order(s)</li>
                    <li>{{ number_format($counts['sale_items']) }} sale item(s)</li>
                    <li>{{ number_format($counts['table_guests']) }} table guest(s)</li>
                </ul>
                <button type="button" class="btn btn-outline-danger mt-auto" onclick="clearData('orders')">
                    <i class="fe fe-trash-2 me-2"></i>Clear All Orders
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Clear All Customers</h4>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="text-muted">
                    Deletes all customers and their payment records. Orders must be cleared first.
                    Use this to remove test customer accounts before launch.
                </p>
                <ul class="small text-muted mb-4">
                    <li>{{ number_format($counts['customers']) }} customer(s)</li>
                    <li>{{ number_format($counts['customer_payments']) }} customer payment(s)</li>
                </ul>
                <button type="button" class="btn btn-outline-warning mt-auto" onclick="clearData('customers')">
                    <i class="fe fe-user-x me-2"></i>Clear All Customers
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card border-danger mb-4">
    <div class="card-header bg-danger-transparent">
        <h4 class="card-title mb-0 text-danger">Clear Everything &amp; Go Live</h4>
    </div>
    <div class="card-body">
        <p class="mb-3">
            Removes all test orders and customers in one step. Products, staff, rooms, and settings are kept.
        </p>
        <button type="button" class="btn btn-danger" onclick="clearData('all')">
            <i class="fe fe-zap me-2"></i>Clear All Orders &amp; Customers
        </button>
    </div>
</div>

<div class="card border-primary" id="hotel-bookings">
    <div class="card-header bg-primary-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="card-title mb-0 text-primary"><i class="fe fe-home me-2"></i>Hotel Room Bookings</h4>
        <span class="text-muted">
            {{ number_format($counts['room_bookings']) }} booking(s) &middot; {{ number_format($counts['active_room_bookings']) }} active
        </span>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="p-3 border rounded h-100">
                    <h5 class="mb-2">Delete bookings made on or before a date</h5>
                    <p class="text-muted small mb-3">
                        Removes every room booking created on or before the selected date (including that day), frees the rooms,
                        and reverses any credit those bookings added to customer accounts.
                    </p>
                    <form method="GET" action="{{ route('admin.go-live.index') }}#hotel-bookings" class="row g-2 align-items-end mb-2">
                        <div class="col-sm-6">
                            <label class="form-label small mb-1">On or before</label>
                            <input type="date" name="cutoff" class="form-control" value="{{ $cutoff }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-sm-6">
                            <div class="alert alert-warning py-2 mb-0">
                                <strong>{{ number_format($bookingsBeforeCutoff) }}</strong> booking(s) on or before
                                {{ \Carbon\Carbon::parse($cutoff)->format('M d, Y') }}
                            </div>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.go-live.bookings.delete-up-to') }}"
                          onsubmit="return confirmTwice('Delete {{ $bookingsBeforeCutoff }} room booking(s) made on or before {{ \Carbon\Carbon::parse($cutoff)->format('M d, Y') }}?');">
                        @csrf
                        <input type="hidden" name="cutoff" value="{{ $cutoff }}">
                        <button type="submit" class="btn btn-danger" @disabled($bookingsBeforeCutoff === 0)>
                            <i class="fe fe-trash-2 me-2"></i>Delete Bookings On or Before {{ \Carbon\Carbon::parse($cutoff)->format('M d') }}
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="p-3 border border-danger rounded h-100 d-flex flex-column">
                    <h5 class="mb-2">Delete all room bookings</h5>
                    <p class="text-muted small">Removes every room booking and sets booked rooms back to available.</p>
                    <form method="POST" action="{{ route('admin.go-live.bookings.delete-all') }}" class="mt-auto"
                          onsubmit="return confirmTwice('Delete ALL {{ $counts['room_bookings'] }} room booking(s)?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100" @disabled($counts['room_bookings'] === 0)>
                            <i class="fe fe-trash me-2"></i>Delete All Bookings
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.go-live.index') }}#hotel-bookings" class="row g-2 mb-3">
            <input type="hidden" name="cutoff" value="{{ $cutoff }}">
            <div class="col-md-6">
                <input type="text" name="booking_search" class="form-control" placeholder="Search booking #, guest name or phone" value="{{ request('booking_search') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fe fe-search me-1"></i>Search</button>
                @if(request('booking_search'))
                <a href="{{ route('admin.go-live.index', ['cutoff' => $cutoff]) }}#hotel-bookings" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-nowrap mb-0">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Booked On</th>
                        <th>Room</th>
                        <th>Guest</th>
                        <th>Check-in</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    <tr>
                        <td><a href="{{ route('admin.room-bookings.receipt', $booking) }}">{{ $booking->booking_number }}</a></td>
                        <td>{{ $booking->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $booking->room->room_number ?? 'Deleted' }}</td>
                        <td>{{ $booking->guest_name ?: ($booking->customer->name ?? 'Walk-in') }}</td>
                        <td>{{ $booking->check_in_date?->format('M d, Y') }}</td>
                        <td class="text-end">₦{{ number_format($booking->total, 2) }}</td>
                        <td>
                            @php
                                $badge = [
                                    'confirmed' => 'bg-primary',
                                    'checked-in' => 'bg-success',
                                    'checked-out' => 'bg-secondary',
                                    'cancelled' => 'bg-danger',
                                ][$booking->status] ?? 'bg-warning';
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($booking->status) }}</span>
                        </td>
                        <td>
                            @if($booking->status !== 'cancelled')
                            <form method="POST" action="{{ route('admin.go-live.bookings.cancel', $booking) }}" class="d-inline"
                                  onsubmit="return askCancelReason(this, '{{ $booking->booking_number }}');">
                                @csrf
                                <input type="hidden" name="reason">
                                <button type="submit" class="btn btn-sm btn-warning"><i class="fe fe-x me-1"></i>Cancel</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('admin.go-live.bookings.delete', $booking) }}" class="d-inline"
                                  onsubmit="return confirm('Permanently delete booking {{ $booking->booking_number }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-trash-2 me-1"></i>Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No room bookings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $bookings->fragment('hotel-bookings')->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const clearRoutes = {
    orders: @json(route('admin.go-live.clear-orders')),
    customers: @json(route('admin.go-live.clear-customers')),
    all: @json(route('admin.go-live.clear-all')),
};

const clearLabels = {
    orders: 'all orders (sales, pending orders, and table guests)',
    customers: 'all customers and their payment records',
    all: 'ALL orders and ALL customers',
};

function confirmTwice(message) {
    return confirm('⚠️ ' + message + '\n\nThis cannot be undone.') && confirm('Please confirm again: ' + message);
}

function askCancelReason(form, bookingNumber) {
    const reason = prompt(`Cancel booking ${bookingNumber}?\n\nReason (optional):`, 'Test booking');
    if (reason === null) {
        return false;
    }
    form.querySelector('input[name="reason"]').value = reason;
    return true;
}

function clearData(type) {
    const label = clearLabels[type];

    if (!confirm(`⚠️ WARNING: This will permanently delete ${label}.\n\nThis cannot be undone. Continue?`)) {
        return;
    }

    if (!confirm(`Please confirm again: delete ${label}?`)) {
        return;
    }

    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fe fe-loader me-2"></i>Clearing...';

    fetch(clearRoutes[type], {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) {
            throw new Error(data.message || 'Failed to clear data.');
        }

        alert('✅ ' + data.message);

        if (data.counts) {
            Object.entries(data.counts).forEach(([key, value]) => {
                const el = document.querySelector(`[data-count="${key}"]`);
                if (el) {
                    el.textContent = Number(value).toLocaleString();
                }
            });
        }

        window.location.reload();
    })
    .catch(error => {
        alert('❌ ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}
</script>
@endpush
