@extends('layouts.admin')

@section('title', 'New Hall Booking')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.hall-rentals.index') }}">Hall Rentals</a></li>
<li class="breadcrumb-item active" aria-current="page">New Booking</li>
@endsection

@push('styles')
<style>
    .occasion-card { cursor: pointer; transition: all .15s; }
    .occasion-card:hover { transform: translateY(-2px); }
    .occasion-card.selected { border-color: #5e72e4 !important; box-shadow: 0 0 0 3px rgba(94, 114, 228, .25); }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('admin.hall-rentals.store') }}" id="hall-form">
    @csrf
    <input type="hidden" name="hall_occasion_id" id="hall_occasion_id" value="{{ old('hall_occasion_id') }}">

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header bg-primary-transparent">
                    <h4 class="card-title mb-0"><i class="fe fe-star me-2"></i>Select Occasion</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($occasions as $occasion)
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="card occasion-card border h-100 mb-0" data-id="{{ $occasion->id }}" data-price="{{ (float) $occasion->price }}" onclick="selectOccasion(this)">
                                <div class="card-body text-center">
                                    <h5 class="mb-2">{{ $occasion->name }}</h5>
                                    <h4 class="text-primary mb-0">₦{{ number_format($occasion->price, 2) }}</h4>
                                    @if($occasion->description)
                                    <small class="text-muted d-block mt-1">{{ $occasion->description }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <p class="text-muted mb-0">No active occasions. Ask an admin to add occasions on the Hall Rentals page.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="fe fe-user me-2"></i>Customer &amp; Event Details</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expected Guests</label>
                            <input type="number" name="expected_guests" class="form-control" min="1" value="{{ old('expected_guests') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Event Date <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" class="form-control" value="{{ old('event_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="fe fe-credit-card me-2"></i>Payment</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Hall Price (₦) <span class="text-danger">*</span></label>
                        <input type="number" name="price" id="price" class="form-control" min="0" step="0.01" value="{{ old('price', 0) }}" oninput="recalc()" required>
                        <small class="text-muted">Fills from the occasion; you can adjust it.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount (₦)</label>
                        <input type="number" name="discount" id="discount" class="form-control" min="0" step="0.01" value="{{ old('discount', 0) }}" oninput="recalc()">
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2 mb-3">
                        <strong>Total:</strong>
                        <strong class="text-primary fs-5" id="total-display">₦0.00</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-select" onchange="togglePayment()">
                            @foreach(['cash' => 'Cash', 'transfer' => 'Transfer', 'pos' => 'POS', 'mixed' => 'Mixed (Cash + Transfer / POS)'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method', 'cash') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="single-payment">
                        <label class="form-label">Amount Paid (₦) <span class="text-danger">*</span></label>
                        <input type="number" name="amount_paid" id="amount_paid" class="form-control" min="0" step="0.01" value="{{ old('amount_paid') }}" oninput="recalc()">
                    </div>

                    <div class="mb-3 p-3 border border-primary rounded bg-light" id="mixed-payment" style="display: none;">
                        <label class="form-label fw-semibold">Split Payment</label>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Cash Paid (₦)</label>
                            <input type="number" name="mixed_cash" id="mixed_cash" class="form-control" min="0" step="0.01" value="{{ old('mixed_cash', 0) }}" oninput="recalc()">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Transfer Made (₦)</label>
                            <input type="number" name="mixed_transfer" id="mixed_transfer" class="form-control" min="0" step="0.01" value="{{ old('mixed_transfer', 0) }}" oninput="recalc()">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">POS (₦)</label>
                            <input type="number" name="mixed_pos" id="mixed_pos" class="form-control" min="0" step="0.01" value="{{ old('mixed_pos', 0) }}" oninput="recalc()">
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Balance Remaining:</span>
                            <strong id="mixed-remaining" class="text-danger">₦0.00</strong>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span>Change:</span>
                        <strong id="change-display">₦0.00</strong>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fe fe-check me-2"></i>Complete Hall Booking
                    </button>
                    <a href="{{ route('admin.hall-rentals.index') }}" class="btn btn-secondary w-100 mt-2">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let amountPaidTouched = {{ old('amount_paid') ? 'true' : 'false' }};

function money(value) {
    return '₦' + value.toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function num(id) {
    return parseFloat(document.getElementById(id).value) || 0;
}

function getTotal() {
    return Math.max(0, num('price') - num('discount'));
}

function selectOccasion(card) {
    document.querySelectorAll('.occasion-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    document.getElementById('hall_occasion_id').value = card.dataset.id;
    document.getElementById('price').value = card.dataset.price;
    amountPaidTouched = false;
    recalc();
}

function togglePayment() {
    const isMixed = document.getElementById('payment_method').value === 'mixed';
    document.getElementById('mixed-payment').style.display = isMixed ? 'block' : 'none';
    document.getElementById('single-payment').style.display = isMixed ? 'none' : 'block';
    recalc();
}

function recalc() {
    const total = getTotal();
    document.getElementById('total-display').textContent = money(total);

    let paid;
    if (document.getElementById('payment_method').value === 'mixed') {
        paid = num('mixed_cash') + num('mixed_transfer') + num('mixed_pos');
        const remaining = Math.max(0, total - paid);
        const remainingEl = document.getElementById('mixed-remaining');
        remainingEl.textContent = money(remaining);
        remainingEl.classList.toggle('text-danger', remaining > 0);
        remainingEl.classList.toggle('text-success', remaining === 0);
    } else {
        const amountInput = document.getElementById('amount_paid');
        if (document.activeElement === amountInput) {
            amountPaidTouched = true;
        }
        if (!amountPaidTouched) {
            amountInput.value = total ? total.toFixed(2) : '';
        }
        paid = num('amount_paid');
    }

    document.getElementById('change-display').textContent = money(Math.max(0, paid - total));
}

document.getElementById('hall-form').addEventListener('submit', function (e) {
    if (!document.getElementById('hall_occasion_id').value) {
        e.preventDefault();
        alert('Please select an occasion.');
        return;
    }

    const total = getTotal();
    if (document.getElementById('payment_method').value === 'mixed') {
        const parts = [num('mixed_cash'), num('mixed_transfer'), num('mixed_pos')];
        const paid = parts.reduce((a, b) => a + b, 0);
        if (parts.filter(v => v > 0).length < 2) {
            e.preventDefault();
            alert('For a mixed payment, enter at least two amounts (e.g. Cash and Transfer).');
            return;
        }
        if (paid < total) {
            e.preventDefault();
            alert('Cash + Transfer + POS is less than the total. Balance remaining: ' + money(total - paid));
            return;
        }
    } else if (num('amount_paid') < total) {
        e.preventDefault();
        alert('Amount paid is less than the total.');
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const preselected = document.getElementById('hall_occasion_id').value;
    if (preselected) {
        const card = document.querySelector(`.occasion-card[data-id="${preselected}"]`);
        if (card) {
            card.classList.add('selected');
        }
    }
    togglePayment();
});
</script>
@endpush
