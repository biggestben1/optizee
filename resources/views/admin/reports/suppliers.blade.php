@extends('layouts.admin')

@section('title', 'Suppliers Report')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active" aria-current="page">Suppliers Report</li>
@endsection

@section('content')
<!-- Export Button -->
<div class="mb-4 text-end">
    <a href="{{ route('admin.reports.export.suppliers') }}" class="btn btn-success">
        <i class="fe fe-download me-1"></i> Download Excel
    </a>
</div>

<!-- Summary -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-danger-transparent">
            <div class="card-body text-center">
                <h3 class="text-danger mb-1">₦{{ number_format($totalDebt, 2) }}</h3>
                <p class="text-muted mb-0">Total Amount Owed to Suppliers</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning-transparent">
            <div class="card-body text-center">
                <h3 class="text-warning mb-1">{{ $suppliersWithDebt->count() }}</h3>
                <p class="text-muted mb-0">Suppliers with Outstanding Balance</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info-transparent">
            <div class="card-body text-center">
                <h3 class="text-info mb-1">{{ $topSuppliers->count() }}</h3>
                <p class="text-muted mb-0">Active Suppliers</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Suppliers with Debt -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-danger-transparent">
                <h4 class="card-title mb-0 text-danger">
                    <i class="fe fe-alert-circle me-2"></i>
                    Outstanding Balances
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover datatable">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Contact</th>
                                <th class="text-end">Amount Owed</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliersWithDebt as $supplier)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.suppliers.show', $supplier) }}">
                                        {{ $supplier->name }}
                                    </a>
                                </td>
                                <td>{{ $supplier->phone }}</td>
                                <td class="text-end text-danger">
                                    <strong>₦{{ number_format($supplier->balance_owed, 2) }}</strong>
                                </td>
                                <td>
                                    <a href="{{ route('admin.suppliers.show', $supplier) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fe fe-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <i class="fe fe-check-circle text-success"></i> No outstanding balances
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($suppliersWithDebt->count() > 0)
                        <tfoot>
                            <tr class="bg-light">
                                <th colspan="2">Total</th>
                                <th class="text-end text-danger">₦{{ number_format($totalDebt, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Suppliers -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-success-transparent">
                <h4 class="card-title mb-0 text-success">
                    <i class="fe fe-truck me-2"></i>
                    Top Suppliers by Purchase History
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Supplier</th>
                                <th class="text-end">Total Purchases</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSuppliers as $index => $supplier)
                            @if($supplier->supplies_sum_total_amount > 0)
                            <tr>
                                <td>
                                    @if($index < 3)
                                    <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'danger') }}">
                                        {{ $index + 1 }}
                                    </span>
                                    @else
                                    {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.suppliers.show', $supplier) }}">
                                        {{ $supplier->name }}
                                    </a>
                                </td>
                                <td class="text-end text-success">
                                    <strong>₦{{ number_format($supplier->supplies_sum_total_amount, 2) }}</strong>
                                </td>
                            </tr>
                            @endif
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No supplier data recorded</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.datatable').DataTable({
        order: [[2, 'desc']],
        pageLength: 25
    });
});
</script>
@endpush

