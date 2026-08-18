@extends('layouts.admin')

@section('title', 'Split Bill - Table ' . $table->number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.tables.index') }}">Tables</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.tables.show', $table) }}">{{ $table->number }}</a></li>
<li class="breadcrumb-item active" aria-current="page">Split Bill</li>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info-transparent">
                <h3 class="card-title text-info mb-0">
                    <i class="fe fe-dollar-sign me-2"></i>
                    Split Bill - Table {{ $table->number }}
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Each guest can pay for their own orders separately. Select a guest to process their individual payment.
                </p>
                
                <div class="row">
                    @foreach($guestBills as $bill)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary-transparent">
                                <h5 class="card-title mb-0 text-primary">
                                    <i class="fe fe-user me-2"></i>
                                    {{ $bill['guest']->guest_name }}
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($bill['guest']->customer)
                                <p class="mb-2">
                                    <small class="text-muted">Customer:</small><br>
                                    <strong>{{ $bill['guest']->customer->name }}</strong>
                                </p>
                                @endif
                                
                                <div class="mb-3">
                                    <h3 class="text-primary mb-0">₦{{ number_format($bill['total'], 2) }}</h3>
                                    <small class="text-muted">Total Bill</small>
                                </div>
                                
                                @if($bill['sales']->count() > 0)
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-2">Orders:</small>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($bill['sales'] as $sale)
                                        <li class="mb-1">
                                            <a href="{{ route('admin.pos.show', $sale) }}" class="text-decoration-none">
                                                {{ $sale->invoice_number }}
                                            </a>
                                            <span class="float-end">₦{{ number_format($sale->total, 2) }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                
                                <a href="{{ route('admin.pos.index', ['table_id' => $table->id, 'guest_id' => $bill['guest']->id]) }}" 
                                   class="btn btn-primary w-100">
                                    <i class="fe fe-shopping-cart me-1"></i> Add Order
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @if($guestBills->isEmpty())
                <div class="text-center py-5">
                    <i class="fe fe-users text-muted" style="font-size: 48px;"></i>
                    <p class="mt-3 text-muted">No guests at this table</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection









