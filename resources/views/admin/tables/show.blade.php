@extends('layouts.admin')

@section('title', 'Table ' . $table->number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.tables.index') }}">Tables</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ $table->number }}</li>
@endsection

@section('actions')
<a href="{{ route('admin.pos.index', ['table_id' => $table->id]) }}" class="btn btn-success">
    <i class="fe fe-shopping-cart me-1"></i> Add Order
</a>
<a href="{{ route('admin.tables.split-bill', $table) }}" class="btn btn-info">
    <i class="fe fe-dollar-sign me-1"></i> Split Bill
</a>
@if($table->isOccupied())
<form action="{{ route('admin.tables.release', $table) }}" method="POST" class="d-inline"
      onsubmit="return confirm('Release this table? All guests will be marked as left.');">
    @csrf
    @method('POST')
    <button type="submit" class="btn btn-warning">
        <i class="fe fe-x-circle me-1"></i> Release Table
    </button>
</form>
@endif
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Table Info</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Table Number</label>
                    <p class="mb-0 fw-bold">{{ $table->number }}</p>
                </div>
                @if($table->name)
                <div class="mb-3">
                    <label class="text-muted small">Name</label>
                    <p class="mb-0">{{ $table->name }}</p>
                </div>
                @endif
                <div class="mb-3">
                    <label class="text-muted small">Capacity</label>
                    <p class="mb-0">{{ $table->capacity }} seats</p>
                </div>
                @if($table->location)
                <div class="mb-3">
                    <label class="text-muted small">Location</label>
                    <p class="mb-0"><i class="fe fe-map-pin me-1"></i>{{ $table->location }}</p>
                </div>
                @endif
                <div class="mb-3">
                    <label class="text-muted small">Status</label>
                    <p class="mb-0">
                        @if($table->status === 'occupied')
                        <span class="badge bg-success">Occupied</span>
                        @elseif($table->status === 'reserved')
                        <span class="badge bg-warning">Reserved</span>
                        @elseif($table->status === 'cleaning')
                        <span class="badge bg-info">Cleaning</span>
                        @else
                        <span class="badge bg-secondary">Available</span>
                        @endif
                    </p>
                </div>
                @if($table->occupied_at)
                <div class="mb-0">
                    <label class="text-muted small">Occupied Since</label>
                    <p class="mb-0">{{ $table->occupied_at->format('M d, Y H:i') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Current Guests -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    Current Guests
                    <span class="badge bg-info ms-2">{{ $table->activeGuests->count() }}</span>
                </h4>
            </div>
            <div class="card-body">
                @if($table->activeGuests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Guest Name</th>
                                <th>Customer</th>
                                <th>Bill</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($table->activeGuests as $guest)
                            @php
                                $guestSales = $guest->sales()->completed()->get();
                                $guestTotal = $guestSales->sum('total');
                                $guestPaid = $guestSales->sum('amount_paid');
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $guest->guest_name }}</strong>
                                    <br><small class="text-muted">Seated: {{ $guest->seated_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if($guest->customer)
                                    <a href="{{ route('admin.customers.show', $guest->customer) }}">
                                        {{ $guest->customer->name }}
                                    </a>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>₦{{ number_format($guestTotal, 2) }}</strong>
                                    @if($guestPaid < $guestTotal)
                                    <br><small class="text-danger">Unpaid: ₦{{ number_format($guestTotal - $guestPaid, 2) }}</small>
                                    @else
                                    <br><small class="text-success">Paid</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.pos.index', ['table_id' => $table->id, 'guest_id' => $guest->id]) }}" 
                                           class="btn btn-sm btn-outline-success" title="Add Order">
                                            <i class="fe fe-plus"></i>
                                        </a>
                                        <form action="{{ route('admin.tables.remove-guest', $guest) }}" method="POST" 
                                              class="d-inline" onsubmit="return confirm('Remove this guest?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                <i class="fe fe-x"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center mb-0">No active guests</p>
                @endif
                
                @if($table->isOccupied())
                <div class="mt-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addGuestModal">
                        <i class="fe fe-user-plus me-1"></i> Add Guest
                    </button>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Combined Bill -->
        @if($combinedSales->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-warning-transparent">
                <h4 class="card-title mb-0 text-warning">Combined Bill (All Guests)</h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span>Total:</span>
                    <strong class="fs-4">₦{{ number_format($combinedTotal, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-0">
                    <span>Paid:</span>
                    <strong class="text-success">₦{{ number_format($combinedPaid, 2) }}</strong>
                </div>
                @if($combinedPaid < $combinedTotal)
                <div class="d-flex justify-content-between mt-2">
                    <span>Unpaid:</span>
                    <strong class="text-danger">₦{{ number_format($combinedTotal - $combinedPaid, 2) }}</strong>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Add Guest Modal -->
<div class="modal fade" id="addGuestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.tables.add-guest', $table) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Guest to Table {{ $table->number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Guest Name <span class="text-danger">*</span></label>
                        <input type="text" name="guest_name" class="form-control" placeholder="e.g., John, Friend 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link to Customer (Optional)</label>
                        <select name="customer_id" class="form-select">
                            <option value="">No Customer</option>
                            @foreach(\App\Models\Customer::active()->get() as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Guest</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection









