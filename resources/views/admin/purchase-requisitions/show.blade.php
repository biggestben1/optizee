@extends('layouts.admin')

@section('title', 'PR - ' . $purchaseRequisition->pr_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.purchase-requisitions.index') }}">Purchase Requisitions</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ $purchaseRequisition->pr_number }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Purchase Requisition Details</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">PR Number</label>
                        <p class="mb-0 fw-bold">{{ $purchaseRequisition->pr_number }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Date</label>
                        <p class="mb-0">{{ $purchaseRequisition->pr_date->format('F d, Y') }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Requested By</label>
                        <p class="mb-0">{{ $purchaseRequisition->requestedBy ? $purchaseRequisition->requestedBy->name : 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Status</label>
                        <p class="mb-0">
                            @if($purchaseRequisition->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                            @elseif($purchaseRequisition->status === 'approved')
                            <span class="badge bg-success">Approved</span>
                            @elseif($purchaseRequisition->status === 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                            @elseif($purchaseRequisition->status === 'completed')
                            <span class="badge bg-info">Completed</span>
                            @endif
                        </p>
                    </div>
                </div>

                @if($purchaseRequisition->approvedBy)
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Approved By</label>
                        <p class="mb-0">{{ $purchaseRequisition->approvedBy ? $purchaseRequisition->approvedBy->name : 'N/A' }}</p>
                    </div>
                    @if($purchaseRequisition->approved_at)
                    <div class="col-md-6">
                        <label class="text-muted small">Approved At</label>
                        <p class="mb-0">{{ $purchaseRequisition->approved_at ? $purchaseRequisition->approved_at->format('F d, Y H:i') : 'N/A' }}</p>
                    </div>
                    @endif
                </div>
                @endif

                <div class="mb-3">
                    <label class="text-muted small">Description</label>
                    <p class="mb-0">{{ $purchaseRequisition->description }}</p>
                </div>

                @if($purchaseRequisition->items_description)
                <div class="mb-3">
                    <label class="text-muted small">Items Description</label>
                    <div class="mb-0">{!! str_replace(['&lt;br /&gt;', '&lt;br&gt;', '&lt;br/&gt;'], '<br />', nl2br(e($purchaseRequisition->items_description))) !!}</div>
                </div>
                @endif

                <div class="mb-3">
                    <label class="text-muted small">Amount</label>
                    <p class="mb-0 fw-bold h4 text-primary">₦{{ number_format($purchaseRequisition->amount, 2) }}</p>
                </div>

                @if($purchaseRequisition->notes)
                <div class="mb-3">
                    <label class="text-muted small">Notes</label>
                    <p class="mb-0">{{ nl2br(e($purchaseRequisition->notes)) }}</p>
                </div>
                @endif

                @if($purchaseRequisition->rejection_reason)
                <div class="mb-3">
                    <label class="text-muted small">Rejection Reason</label>
                    <p class="mb-0 text-danger">{{ $purchaseRequisition->rejection_reason }}</p>
                </div>
                @endif
            </div>
            <div class="card-footer">
                <div class="d-flex gap-2">
                    @if($purchaseRequisition->status === 'pending')
                        @if(auth()->user()->is_admin || auth()->user()->isManager())
                        <form method="POST" action="{{ route('admin.purchase-requisitions.approve', $purchaseRequisition) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Approve this PR?')">
                                <i class="fe fe-check me-2"></i> Approve
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fe fe-x me-2"></i> Reject
                        </button>
                        @endif
                        @if($purchaseRequisition->requested_by === auth()->id())
                        <a href="{{ route('admin.purchase-requisitions.edit', $purchaseRequisition) }}" class="btn btn-primary">
                            <i class="fe fe-edit me-2"></i> Edit
                        </a>
                        @endif
                    @elseif($purchaseRequisition->status === 'approved')
                        @if(auth()->user()->is_admin || auth()->user()->isManager())
                        <form method="POST" action="{{ route('admin.purchase-requisitions.complete', $purchaseRequisition) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info" onclick="return confirm('Mark this PR as completed?')">
                                <i class="fe fe-check-circle me-2"></i> Mark as Completed
                            </button>
                        </form>
                        @endif
                    @endif
                    <a href="{{ route('admin.purchase-requisitions.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-2"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.purchase-requisitions.reject', $purchaseRequisition) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Purchase Requisition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject PR</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

