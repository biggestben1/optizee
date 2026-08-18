@extends('layouts.admin')

@section('title', 'Tables')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Tables</li>
@endsection

@section('actions')
<a href="{{ route('admin.tables.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i> Add Table
</a>
@endsection

@section('content')
<div class="row">
    @foreach($tables as $table)
    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card {{ $table->status === 'occupied' ? 'border-success' : ($table->status === 'reserved' ? 'border-warning' : 'border-secondary') }}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="fe fe-grid me-2"></i>
                    {{ $table->number }}
                </h4>
                @if($table->status === 'occupied')
                <span class="badge bg-success">Occupied</span>
                @elseif($table->status === 'reserved')
                <span class="badge bg-warning">Reserved</span>
                @elseif($table->status === 'cleaning')
                <span class="badge bg-info">Cleaning</span>
                @else
                <span class="badge bg-secondary">Available</span>
                @endif
            </div>
            <div class="card-body">
                @if($table->name)
                <p class="mb-2"><strong>{{ $table->name }}</strong></p>
                @endif
                
                <div class="mb-2">
                    <i class="fe fe-users text-muted me-1"></i>
                    Capacity: {{ $table->capacity }} seats
                </div>
                
                @if($table->location)
                <div class="mb-2">
                    <i class="fe fe-map-pin text-muted me-1"></i>
                    {{ $table->location }}
                </div>
                @endif
                
                @if($table->isOccupied())
                <div class="mb-2">
                    <i class="fe fe-user-check text-success me-1"></i>
                    <strong>{{ $table->activeGuests->count() }} Guest(s)</strong>
                </div>
                @if($table->servedBy)
                <div class="mb-2">
                    <small class="text-muted">Served by: {{ $table->servedBy->name }}</small>
                </div>
                @endif
                @endif
            </div>
            <div class="card-footer">
                <div class="d-flex gap-2 flex-wrap">
                    @if($table->isOccupied())
                    <a href="{{ route('admin.tables.show', $table) }}" class="btn btn-sm btn-primary">
                        <i class="fe fe-eye"></i> View
                    </a>
                    <a href="{{ route('admin.tables.split-bill', $table) }}" class="btn btn-sm btn-info">
                        <i class="fe fe-dollar-sign"></i> Split Bill
                    </a>
                    <a href="{{ route('admin.pos.index', ['table_id' => $table->id]) }}" class="btn btn-sm btn-success">
                        <i class="fe fe-shopping-cart"></i> Add Order
                    </a>
                    <form action="{{ route('admin.tables.release', $table) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Release this table? All guests will be marked as left.');">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-sm btn-outline-warning">
                            <i class="fe fe-x-circle"></i> Release
                        </button>
                    </form>
                    @else
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#occupyModal{{ $table->id }}">
                        <i class="fe fe-user-plus"></i> Occupy
                    </button>
                    <a href="{{ route('admin.tables.edit', $table) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fe fe-edit"></i> Edit
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Occupy Table Modal -->
    <div class="modal fade" id="occupyModal{{ $table->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.tables.occupy', $table) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Occupy Table {{ $table->number }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Add guests to this table:</p>
                        <div id="guests-container-{{ $table->id }}">
                            <div class="guest-row mb-2">
                                <div class="row">
                                    <div class="col-8">
                                        <input type="text" name="guests[0][name]" class="form-control" placeholder="Guest Name" required>
                                    </div>
                                    <div class="col-4">
                                        <select name="guests[0][customer_id]" class="form-select">
                                            <option value="">No Customer</option>
                                            @foreach(\App\Models\Customer::active()->get() as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addGuestRow({{ $table->id }})">
                            <i class="fe fe-plus"></i> Add Guest
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Occupy Table</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($tables->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fe fe-grid text-muted" style="font-size: 48px;"></i>
        <h4 class="mt-3">No Tables Found</h4>
        <p class="text-muted">Create your first table to get started.</p>
        <a href="{{ route('admin.tables.create') }}" class="btn btn-primary">
            <i class="fe fe-plus me-1"></i> Create Table
        </a>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
let guestRowCount = {};

function addGuestRow(tableId) {
    if (!guestRowCount[tableId]) {
        guestRowCount[tableId] = 0;
    }
    guestRowCount[tableId]++;
    
    const container = document.getElementById(`guests-container-${tableId}`);
    const row = document.createElement('div');
    row.className = 'guest-row mb-2';
    row.innerHTML = `
        <div class="row">
            <div class="col-8">
                <input type="text" name="guests[${guestRowCount[tableId]}][name]" class="form-control" placeholder="Guest Name" required>
            </div>
            <div class="col-3">
                <select name="guests[${guestRowCount[tableId]}][customer_id]" class="form-select">
                    <option value="">No Customer</option>
                    @foreach(\App\Models\Customer::active()->get() as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-1">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.guest-row').remove()">
                    <i class="fe fe-x"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(row);
}
</script>
@endpush









