@extends('layouts.admin')

@section('title', 'Hall Rentals')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Hall Rentals</li>
@endsection

@section('actions')
<a href="{{ route('admin.hall-rentals.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i>New Hall Booking
</a>
@endsection

@section('content')
@php $canManageOccasions = auth()->user()->is_admin || auth()->user()->isManager(); @endphp

<div class="row mb-4">
    <div class="col-lg col-md-4 col-6 mb-3">
        <div class="card bg-primary-transparent h-100">
            <div class="card-body text-center">
                <h3 class="text-primary mb-1">{{ $todaySummary['count'] }}</h3>
                <p class="text-muted mb-0">Bookings Today</p>
            </div>
        </div>
    </div>
    <div class="col-lg col-md-4 col-6 mb-3">
        <div class="card bg-info-transparent h-100">
            <div class="card-body text-center">
                <h3 class="text-info mb-1">₦{{ number_format($todaySummary['total'], 2) }}</h3>
                <p class="text-muted mb-0">Income Today</p>
            </div>
        </div>
    </div>
    <div class="col-lg col-md-4 col-6 mb-3">
        <div class="card bg-success-transparent h-100">
            <div class="card-body text-center">
                <h3 class="text-success mb-1">₦{{ number_format($todaySummary['cash'], 2) }}</h3>
                <p class="text-muted mb-0">Cash</p>
            </div>
        </div>
    </div>
    <div class="col-lg col-md-6 col-6 mb-3">
        <div class="card bg-secondary-transparent h-100">
            <div class="card-body text-center">
                <h3 class="text-secondary mb-1">₦{{ number_format($todaySummary['transfer'], 2) }}</h3>
                <p class="text-muted mb-0">Transfer</p>
            </div>
        </div>
    </div>
    <div class="col-lg col-md-6 col-12 mb-3">
        <div class="card bg-warning-transparent h-100">
            <div class="card-body text-center">
                <h3 class="text-warning mb-1">₦{{ number_format($todaySummary['pos'], 2) }}</h3>
                <p class="text-muted mb-0">POS</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-0"><i class="fe fe-calendar me-2"></i>Upcoming Events</h4>
            </div>
            <div class="card-body">
                @forelse($upcoming as $event)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <strong>{{ $event->occasion_name }}</strong> &mdash; {{ $event->customer_name }}
                        <br>
                        <small class="text-muted">
                            {{ $event->event_date->format('D, M d, Y') }}
                            @if($event->start_time)
                            &middot; {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                            @if($event->end_time) - {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }} @endif
                            @endif
                        </small>
                    </div>
                    <a href="{{ route('admin.hall-rentals.show', $event) }}" class="btn btn-sm btn-outline-primary">View</a>
                </div>
                @empty
                <p class="text-muted mb-0">No upcoming hall events.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-0"><i class="fe fe-tag me-2"></i>Occasions &amp; Prices</h4>
            </div>
            <div class="card-body">
                <table class="table table-sm table-bordered mb-3">
                    <thead>
                        <tr>
                            <th>Occasion</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">Status</th>
                            @if($canManageOccasions)<th></th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($occasions as $occasion)
                        <tr>
                            <td>{{ $occasion->name }}</td>
                            <td class="text-end">₦{{ number_format($occasion->price, 2) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $occasion->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $occasion->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            @if($canManageOccasions)
                            <td class="text-nowrap text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="editOccasion({{ $occasion->id }}, @js($occasion->name), {{ (float) $occasion->price }}, @js($occasion->description), {{ $occasion->is_active ? 'true' : 'false' }})">
                                    <i class="fe fe-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.hall-rentals.occasions.destroy', $occasion) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this occasion?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-trash-2"></i></button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-muted text-center">No occasions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                @if($canManageOccasions)
                <form method="POST" action="{{ route('admin.hall-rentals.occasions.store') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-sm-6">
                        <label class="form-label small mb-1">New Occasion</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Naming Ceremony" required>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label small mb-1">Price (₦)</label>
                        <input type="number" name="price" class="form-control form-control-sm" min="0" step="0.01" required>
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fe fe-plus"></i></button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4 class="card-title mb-0">All Hall Bookings</h4>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Event From</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Event To</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2"><i class="fe fe-filter me-1"></i>Filter</button>
                <a href="{{ route('admin.hall-rentals.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Occasion</th>
                        <th>Customer</th>
                        <th>Event Date</th>
                        <th class="text-end">Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Booked By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    <tr>
                        <td><strong>{{ $booking->booking_number }}</strong><br><small class="text-muted">{{ $booking->created_at->format('M d, Y H:i') }}</small></td>
                        <td>{{ $booking->occasion_name }}</td>
                        <td>{{ $booking->customer_name }}<br><small class="text-muted">{{ $booking->customer_phone }}</small></td>
                        <td>
                            {{ $booking->event_date->format('M d, Y') }}
                            @if($booking->start_time)
                            <br><small class="text-muted">{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }}</small>
                            @endif
                        </td>
                        <td class="text-end fw-bold">₦{{ number_format($booking->total, 2) }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ strtoupper($booking->payment_method) }}</span>
                            @if($booking->payment_method === 'mixed')
                            <br><small class="text-muted">
                                C: ₦{{ number_format($booking->cash_amount) }}
                                · T: ₦{{ number_format($booking->transfer_amount) }}
                                @if($booking->pos_amount > 0) · P: ₦{{ number_format($booking->pos_amount) }} @endif
                            </small>
                            @endif
                        </td>
                        <td>
                            @if($booking->isCancelled())
                            <span class="badge bg-danger">Cancelled</span>
                            @else
                            <span class="badge bg-primary">Confirmed</span>
                            @endif
                        </td>
                        <td>{{ $booking->user->name ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('admin.hall-rentals.show', $booking) }}" class="btn btn-sm btn-info" title="Receipt"><i class="fe fe-file-text"></i></a>
                            @unless($booking->isCancelled())
                            <button type="button" class="btn btn-sm btn-danger" title="Cancel" onclick="cancelHallBooking({{ $booking->id }})"><i class="fe fe-x"></i></button>
                            @endunless
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No hall bookings yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $bookings->links() }}</div>
    </div>
</div>

<div class="modal fade" id="cancelHallModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="cancelHallForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Hall Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" required></textarea>
                    <small class="text-muted">If money is returned to the customer, also record it under Refunds.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($canManageOccasions)
<div class="modal fade" id="editOccasionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editOccasionForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Occasion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="occasion-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (₦)</label>
                        <input type="number" name="price" id="occasion-price" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="occasion-description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="occasion-active">
                        <label class="form-check-label" for="occasion-active">Active (shown when booking)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function cancelHallBooking(id) {
    const form = document.getElementById('cancelHallForm');
    form.action = @json(route('admin.hall-rentals.cancel', ':id')).replace(':id', id);
    new bootstrap.Modal(document.getElementById('cancelHallModal')).show();
}

function editOccasion(id, name, price, description, isActive) {
    const form = document.getElementById('editOccasionForm');
    form.action = @json(route('admin.hall-rentals.occasions.update', ':id')).replace(':id', id);
    document.getElementById('occasion-name').value = name;
    document.getElementById('occasion-price').value = price;
    document.getElementById('occasion-description').value = description || '';
    document.getElementById('occasion-active').checked = isActive;
    new bootstrap.Modal(document.getElementById('editOccasionModal')).show();
}
</script>
@endpush
