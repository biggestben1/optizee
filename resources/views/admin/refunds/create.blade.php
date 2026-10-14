@extends('layouts.admin')

@section('title', 'Record Refund')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.refunds.index') }}">Refunds</a></li>
<li class="breadcrumb-item active" aria-current="page">Record Refund</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 col-xl-6 mx-auto">
        <div class="card">
            <div class="card-header bg-danger-transparent">
                <h4 class="card-title mb-0 text-danger"><i class="fe fe-rotate-ccw me-2"></i>Record Refund</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.refunds.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Area of Work <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($areas as $key => $label)
                            <input type="radio" class="btn-check" name="area" id="area-{{ $key }}" value="{{ $key }}"
                                   @checked(old('area', $selectedArea) === $key) required>
                            <label class="btn btn-outline-primary" for="area-{{ $key }}">{{ $label }}</label>
                            @endforeach
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Refund Date <span class="text-danger">*</span></label>
                            <input type="date" name="refund_date" class="form-control" value="{{ old('refund_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reference</label>
                            <input type="text" name="reference" class="form-control" value="{{ old('reference', $reference) }}" placeholder="Booking / invoice number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Phone</label>
                            <input type="tel" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount Refunded (₦) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" min="0.01" step="0.01" value="{{ old('amount') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Paid Back By <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                @foreach($methods as $key => $label)
                                <option value="{{ $key }}" @selected(old('payment_method', 'cash') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required>{{ old('reason') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger"><i class="fe fe-save me-2"></i>Save Refund</button>
                        <a href="{{ route('admin.refunds.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
