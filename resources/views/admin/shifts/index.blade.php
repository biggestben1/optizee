@extends('layouts.admin')

@section('title', 'Shifts')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Shifts</li>
@endsection

@section('actions')
@if(!auth()->user()->hasOpenShift())
<a href="{{ route('admin.shifts.create') }}" class="btn btn-success">
    <i class="fe fe-play-circle me-2"></i> Open Shift
</a>
@else
<a href="{{ route('admin.shifts.current') }}" class="btn btn-warning">
    <i class="fe fe-clock me-2"></i> View Current Shift
</a>
@endif
@endsection

@section('content')
<!-- Staff On Duty Section -->
@if($staffOnDuty->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-success-transparent">
        <h3 class="card-title text-success mb-0">
            <i class="fe fe-users me-2"></i>
            Staff Currently On Duty
            <span class="badge bg-success ms-2">{{ $staffOnDuty->count() }}</span>
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($staffOnDuty as $duty)
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border-success mb-0">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-lg bg-success-transparent text-success rounded-circle">
                                    {{ strtoupper(substr($duty['user']->name, 0, 2)) }}
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1">{{ $duty['user']->name }}</h5>
                                <span class="badge bg-secondary mb-1">{{ $duty['user']->role?->display_name }}</span>
                                <div class="text-muted small">
                                    <i class="fe fe-clock me-1"></i>
                                    Since {{ $duty['shift']->opened_at->format('H:i') }}
                                    ({{ $duty['shift']->opened_at->diffForHumans() }})
                                </div>
                            </div>
                        </div>
                        @if($duty['sections']->count() > 0)
                        <div class="mt-2 pt-2 border-top">
                            <small class="text-muted">Assigned Sections:</small>
                            <div class="mt-1">
                                @foreach($duty['assignments'] as $assignment)
                                <span class="badge bg-primary-transparent text-primary me-1 mb-1">
                                    <i class="fe fe-map-pin me-1"></i>
                                    {{ $assignment->section->name }}
                                    <small>({{ $assignment->getShiftLabel() }})</small>
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="mt-2 pt-2 border-top">
                            <small class="text-warning">
                                <i class="fe fe-alert-circle me-1"></i>
                                No section assigned
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Staff Assignments by Section -->
@if($currentAssignments->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-info-transparent">
        <h3 class="card-title text-info mb-0">
            <i class="fe fe-layers me-2"></i>
            Today's Section Assignments
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($currentAssignments as $sectionId => $assignments)
            @php $section = $assignments->first()->section; @endphp
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card border mb-0">
                    <div class="card-header py-2 bg-light">
                        <h5 class="mb-0">
                            <span class="badge bg-primary me-2">{{ $section->code }}</span>
                            {{ $section->name }}
                        </h5>
                    </div>
                    <div class="card-body py-2">
                        @foreach($assignments as $assignment)
                        <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <strong>{{ $assignment->user->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $assignment->user->role?->display_name }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-info-transparent text-info">
                                    {{ $assignment->getShiftLabel() }}
                                </span>
                                <br>
                                <small class="text-muted">{{ $assignment->getShiftTime() }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Shift History</h3>
                <div class="ms-auto">
                    <form action="" method="GET" class="d-flex gap-2">
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}" style="max-width: 200px;">
                        <select name="status" class="form-select" style="max-width: 150px;">
                            <option value="">All Status</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                        <button type="submit" class="btn btn-secondary">Filter</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Section(s)</th>
                                <th>Opened At</th>
                                <th>Closed At</th>
                                <th>Opening Cash</th>
                                <th>Closing Cash</th>
                                <th>Difference</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shifts as $shift)
                            <tr>
                                <td>
                                    <strong>{{ $shift->user->name }}</strong>
                                    <br><small class="text-muted">{{ $shift->user->role?->display_name }}</small>
                                </td>
                                <td>
                                    @if($shift->user->currentAssignments->count() > 0)
                                        @foreach($shift->user->currentAssignments as $assignment)
                                        <span class="badge bg-primary-transparent text-primary mb-1">
                                            {{ $assignment->section->code }}
                                        </span>
                                        @endforeach
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $shift->opened_at->format('M d, Y H:i') }}</td>
                                <td>{{ $shift->closed_at ? $shift->closed_at->format('M d, Y H:i') : '-' }}</td>
                                <td>₦{{ number_format($shift->opening_cash, 2) }}</td>
                                <td>{{ $shift->closing_cash !== null ? '₦' . number_format($shift->closing_cash, 2) : '-' }}</td>
                                <td>
                                    @if($shift->cash_difference !== null)
                                        @if($shift->cash_difference >= 0)
                                        <span class="text-success">+₦{{ number_format($shift->cash_difference, 2) }}</span>
                                        @else
                                        <span class="text-danger">-₦{{ number_format(abs($shift->cash_difference), 2) }}</span>
                                        @endif
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>
                                    @if($shift->isOpen())
                                    <span class="badge bg-success">Open</span>
                                    @else
                                    <span class="badge bg-secondary">Closed</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.shifts.show', $shift) }}" class="btn btn-sm btn-info">
                                        <i class="fe fe-eye"></i> View
                                    </a>
                                    @if($shift->isOpen() && ($shift->user_id == auth()->id() || auth()->user()->canVoidSales()))
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#closeShiftModal{{ $shift->id }}">
                                        <i class="fe fe-stop-circle"></i> Close
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Close Shift Modal -->
                            @if($shift->isOpen())
                            <div class="modal fade" id="closeShiftModal{{ $shift->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.shifts.close', $shift) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Close Shift</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Opening Cash: <strong>₦{{ number_format($shift->opening_cash, 2) }}</strong></p>
                                                <p>Expected Cash: <strong>₦{{ number_format($shift->opening_cash + $shift->getTotalCashSales(), 2) }}</strong></p>
                                                <div class="mb-3">
                                                    <label class="form-label">Closing Cash</label>
                                                    <input type="number" name="closing_cash" class="form-control" step="0.01" min="0" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Notes</label>
                                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-warning">Close Shift</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No shifts found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $shifts->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection


