@extends('layouts.admin')

@section('title', 'Hotel POS - Room Booking')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Hotel POS</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fe fe-home me-2"></i>Hotel Room Booking
                    </h4>
                    @if(!auth()->user()->isReceptionist())
                    <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-primary">
                        <i class="fe fe-arrow-left me-2"></i>Back to Rooms
                    </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Customer Category -->
                <div class="mb-4">
                    <label class="form-label fw-semibold mb-2">Customer Category</label>
                    <div class="d-flex flex-wrap gap-3 mb-3 p-2 bg-light rounded shadow-sm">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="customer_category" id="category-walkin" value="walkin" checked onchange="toggleCustomerCategory()">
                            <label class="form-check-label fw-semibold" for="category-walkin">Walk-in</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="customer_category" id="category-credit" value="credit" onchange="toggleCustomerCategory()">
                            <label class="form-check-label fw-semibold" for="category-credit">Credit Account</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="customer_category" id="category-online" value="online" onchange="toggleCustomerCategory()">
                            <label class="form-check-label fw-semibold" for="category-online">Online Booking</label>
                        </div>
                    </div>

                    <div id="credit-customer-select-container" style="display: none;" class="p-3 border rounded border-primary bg-light-primary">
                        <label class="form-label fw-semibold mb-2 text-primary">Customer Account (for credit bookings)</label>
                        <select id="customer-select" class="form-select select2">
                            <option value="">-- Choose Customer --</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}"
                                    data-credit-limit="{{ $customer->credit_limit }}"
                                    data-credit-balance="{{ $customer->credit_balance }}"
                                    data-credit-enabled="{{ $customer->credit_enabled ? 'true' : 'false' }}">
                                {{ $customer->name }} (Credit: ₦{{ number_format($customer->getAvailableCredit(), 2) }})
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-2">Required for credit bookings. Payment method will default to Credit.</small>
                    </div>

                    <div id="online-booking-hint" style="display: none;" class="mt-2">
                        <small class="text-info"><i class="fe fe-globe me-1"></i>This booking will be marked as an online reservation.</small>
                    </div>
                </div>

                <!-- Room Booking Section -->
                <div class="card border-info mb-3">
                    <div class="card-header bg-info-transparent">
                        <h5 class="card-title mb-0 text-info">
                            <i class="fe fe-home me-2"></i>Select Room & Booking Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold mb-2">Select Room Category</label>
                            <select id="room-category-select" class="form-select form-select-lg" onchange="loadRooms()">
                                <option value="">-- Select Category --</option>
                                @foreach($roomCategories as $category)
                                <option value="{{ $category->id }}" data-is-suite="{{ $category->is_suite ? '1' : '0' }}" data-full-suite-price="{{ $category->full_suite_price ?? 0 }}">
                                    {{ $category->name }}
                                    @if($category->is_suite)
                                    (Full Suite: ₦{{ number_format($category->full_suite_price, 2) }})
                                    @else
                                    (₦{{ number_format($category->price_per_room, 2) }}/night
                                    @if($category->hourly_rate)
                                    · ₦{{ number_format($category->hourly_rate, 2) }}/hr
                                    @else
                                    · ~₦{{ number_format($category->price_per_room / 24, 2) }}/hr
                                    @endif
                                    )
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="rooms-container" class="mb-3" style="display: none;">
                            <label class="form-label fw-bold mb-2">Select Room</label>
                            <div id="rooms-list" class="row">
                                <!-- Rooms will be loaded here -->
                            </div>
                        </div>

                        <div id="room-booking-form" style="display: none;">
                            <hr class="my-4">
                            <h5 class="mb-3">Booking Details</h5>
                            
                            <!-- Guest Information -->
                            <div class="card border-secondary mb-3">
                                <div class="card-header bg-secondary-transparent">
                                    <h6 class="card-title mb-0 text-secondary">Guest Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                            <input type="text" id="guest-name" class="form-control" placeholder="Guest full name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                            <input type="tel" id="guest-phone" class="form-control" placeholder="Guest phone number" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Email</label>
                                            <input type="email" id="guest-email" class="form-control" placeholder="Guest email address">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">ID Upload</label>
                                            <input type="file" id="guest-id-upload" class="form-control" accept="image/*">
                                            <small class="text-muted">Upload ID document (image only)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Booking Type <span class="text-danger">*</span></label>
                                <select id="booking-type" class="form-select" onchange="toggleBookingType()">
                                    <option value="overnight">Overnight Stay</option>
                                    <option value="short_stay">Short Stay (Hourly)</option>
                                </select>
                            </div>

                            <!-- Overnight Booking Fields -->
                            <div id="overnight-fields">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Check-in Date <span class="text-danger">*</span></label>
                                        <input type="date" id="check-in-date" class="form-control" min="{{ date('Y-m-d') }}" onchange="calculateNights()">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Check-in Time <span class="text-danger">*</span></label>
                                        <input type="time" id="overnight-check-in-time" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Check-out Date <span class="text-danger">*</span></label>
                                        <input type="date" id="check-out-date" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" onchange="calculateNights()">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Number of Nights</label>
                                        <input type="number" id="number-of-nights" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Price per Night</label>
                                        <input type="text" id="room-price-per-night" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Short Stay Booking Fields -->
                            <div id="short-stay-fields" style="display: none;">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Date <span class="text-danger">*</span></label>
                                        <input type="date" id="short-stay-date" class="form-control" min="{{ date('Y-m-d') }}" onchange="calculateHours()">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Check-in Time <span class="text-danger">*</span></label>
                                        <input type="time" id="check-in-time" class="form-control" onchange="calculateHours()">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Check-out Time <span class="text-danger">*</span></label>
                                        <input type="time" id="check-out-time" class="form-control" onchange="calculateHours()">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Hours Stayed</label>
                                        <input type="number" id="hours-stayed" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Hourly Rate (₦) <span class="text-danger">*</span></label>
                                        <input type="number" id="hourly-rate" class="form-control" min="0" step="0.01" placeholder="0.00" oninput="onHourlyRateChange()">
                                        <small class="text-muted">You can edit this price for this booking.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Discount (₦)</label>
                                    <input type="number" id="room-discount" class="form-control" value="0" min="0" step="0.01" onchange="calculateRoomTotal()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tax/VAT (₦)</label>
                                    <input type="number" id="room-tax" class="form-control" value="0" min="0" step="0.01" onchange="calculateRoomTotal()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Service Charge (₦)</label>
                                    <input type="number" id="room-service-charge" class="form-control" value="0" min="0" step="0.01" onchange="calculateRoomTotal()">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select id="room-payment-method" class="form-select">
                                    <option value="cash">Cash</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="pos">POS</option>
                                    <option value="credit">Credit</option>
                                    <option value="mixed">Mixed</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Amount Paid <span class="text-danger">*</span></label>
                                <input type="text" id="room-amount-paid" class="form-control" placeholder="Auto-fills with total" oninput="formatAmountPaid(this)" onblur="formatAmountPaid(this)" onclick="autoFillRoomAmountPaid(true)" required>
                            </div>

                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">Billing Summary</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span id="room-subtotal">₦0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Discount:</span>
                                        <span id="room-discount-display">₦0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax:</span>
                                        <span id="room-tax-display">₦0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Service Charge:</span>
                                        <span id="room-service-charge-display">₦0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                        <strong>Total:</strong>
                                        <strong id="room-total" class="text-primary">₦0.00</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span>Change:</span>
                                        <span id="room-change" class="fw-bold">₦0.00</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes (Optional)</label>
                                <textarea id="room-notes" class="form-control" rows="2" placeholder="Additional notes about this booking..."></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary btn-lg" onclick="processRoomBooking()">
                                    <i class="fe fe-check me-2"></i>Complete Booking
                                </button>
                                <button type="button" class="btn btn-secondary btn-lg" onclick="clearRoomBooking()">
                                    <i class="fe fe-x me-2"></i>Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedRoomId = null;
let selectedRoomPrice = 0;
let selectedHourlyRate = 0;
let isFullSuiteBooking = false;
let amountPaidManuallyEdited = false;

function setCurrentTimeInput(inputId) {
    const input = document.getElementById(inputId);
    if (!input) {
        return;
    }
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    input.value = `${hours}:${minutes}`;
}

function getSelectedCustomerCategory() {
    const selected = document.querySelector('input[name="customer_category"]:checked');
    return selected ? selected.value : 'walkin';
}

function toggleCustomerCategory() {
    const category = getSelectedCustomerCategory();
    const creditContainer = document.getElementById('credit-customer-select-container');
    const onlineHint = document.getElementById('online-booking-hint');
    const paymentMethod = document.getElementById('room-payment-method');
    const amountPaidGroup = document.getElementById('room-amount-paid')?.closest('.mb-3');
    const customerSelect = document.getElementById('customer-select');

    if (creditContainer) {
        creditContainer.style.display = category === 'credit' ? 'block' : 'none';
    }
    if (onlineHint) {
        onlineHint.style.display = category === 'online' ? 'block' : 'none';
    }

    if (category !== 'credit' && customerSelect) {
        customerSelect.value = '';
        if (window.jQuery && jQuery(customerSelect).hasClass('select2-hidden-accessible')) {
            jQuery(customerSelect).val(null).trigger('change');
        }
    }

    if (paymentMethod) {
        if (category === 'credit') {
            paymentMethod.value = 'credit';
        } else if (paymentMethod.value === 'credit') {
            paymentMethod.value = 'cash';
        }
        toggleCreditPaymentUi();
    }
}

function toggleCreditPaymentUi() {
    const paymentMethod = document.getElementById('room-payment-method');
    const amountPaidInput = document.getElementById('room-amount-paid');
    const changeEl = document.getElementById('room-change');
    if (!paymentMethod || !amountPaidInput) {
        return;
    }

    const isCredit = paymentMethod.value === 'credit';
    const amountPaidGroup = amountPaidInput.closest('.mb-3');
    if (amountPaidGroup) {
        amountPaidGroup.style.display = isCredit ? 'none' : 'block';
    }

    if (isCredit) {
        amountPaidInput.value = '0';
        amountPaidManuallyEdited = false;
        if (changeEl) {
            changeEl.textContent = '₦0.00';
        }
    } else {
        autoFillRoomAmountPaid();
    }
}

function onHourlyRateChange() {
    selectedHourlyRate = parseFloat(document.getElementById('hourly-rate').value) || 0;
    calculateShortStayTotal();
}

function toggleBookingType() {
    const bookingType = document.getElementById('booking-type').value;
    const overnightFields = document.getElementById('overnight-fields');
    const shortStayFields = document.getElementById('short-stay-fields');
    
    if (bookingType === 'short_stay') {
        overnightFields.style.display = 'none';
        shortStayFields.style.display = 'block';
        // Set today's date and current time
        document.getElementById('short-stay-date').value = new Date().toISOString().split('T')[0];
        setCurrentTimeInput('check-in-time');
        calculateHours();
    } else {
        overnightFields.style.display = 'block';
        shortStayFields.style.display = 'none';
        setCurrentTimeInput('overnight-check-in-time');
        calculateNights();
    }
}

function calculateHours() {
    const date = document.getElementById('short-stay-date').value;
    const checkInTime = document.getElementById('check-in-time').value;
    const checkOutTime = document.getElementById('check-out-time').value;
    
    if (date && checkInTime && checkOutTime) {
        const checkIn = new Date(`${date}T${checkInTime}`);
        const checkOut = new Date(`${date}T${checkOutTime}`);
        
        // If checkout is before check-in, assume next day
        if (checkOut < checkIn) {
            checkOut.setDate(checkOut.getDate() + 1);
        }
        
        const diffMs = checkOut - checkIn;
        const diffHours = Math.max(1, Math.ceil(diffMs / (1000 * 60 * 60))); // Minimum 1 hour
        
        document.getElementById('hours-stayed').value = diffHours;
        calculateShortStayTotal();
    }
}

function calculateShortStayTotal() {
    const hours = parseInt(document.getElementById('hours-stayed').value) || 0;
    const hourlyRate = selectedHourlyRate;
    const subtotal = hours * hourlyRate;
    const discount = parseFloat(document.getElementById('room-discount').value) || 0;
    const tax = parseFloat(document.getElementById('room-tax').value) || 0;
    const serviceCharge = parseFloat(document.getElementById('room-service-charge').value) || 0;
    const total = subtotal - discount + tax + serviceCharge;
    
    document.getElementById('room-subtotal').textContent = `₦${subtotal.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
    document.getElementById('room-discount-display').textContent = `₦${discount.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
    document.getElementById('room-tax-display').textContent = `₦${tax.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
    document.getElementById('room-service-charge-display').textContent = `₦${serviceCharge.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
    document.getElementById('room-total').textContent = `₦${total.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
    
    autoFillRoomAmountPaid();
}

// Check if room_id is in URL
const urlParams = new URLSearchParams(window.location.search);
const roomIdParam = urlParams.get('room_id');
if (roomIdParam) {
    console.log('Room ID found in URL:', roomIdParam);
    setTimeout(() => {
        fetch(`{{ route('admin.hotel-pos.rooms') }}?room_id=${roomIdParam}`)
            .then(response => response.json())
            .then(data => {
                console.log('Room data received:', data);
                if (data.success && data.rooms && data.rooms.length > 0) {
                    const room = data.rooms[0];
                    console.log('Room found:', room);
                    
                    const categorySelect = document.getElementById('room-category-select');
                    if (categorySelect) {
                        categorySelect.value = room.category.id;
                        console.log('Category selected:', room.category.id);
                        
                        loadRooms();
                        
                        setTimeout(() => {
                            selectRoom(parseInt(roomIdParam), room.price_per_night, room.hourly_rate || 0);
                            console.log('Room selected:', roomIdParam);
                            
                            document.querySelectorAll('.room-card').forEach(card => {
                                if (card.textContent.includes(room.room_number)) {
                                    card.classList.add('border-primary', 'bg-primary-transparent');
                                }
                            });
                        }, 1200);
                    }
                } else {
                    console.error('Room not found in response');
                    alert('Room not found. Please select a room manually.');
                }
            })
            .catch(error => {
                console.error('Error loading room:', error);
                alert('Error loading room. Please select a room manually.');
            });
    }, 500);
}

function loadRooms() {
    const categoryId = document.getElementById('room-category-select').value;
    if (!categoryId) {
        document.getElementById('rooms-container').style.display = 'none';
        document.getElementById('room-booking-form').style.display = 'none';
        return;
    }

    const categorySelect = document.getElementById('room-category-select');
    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
    isFullSuiteBooking = selectedOption.dataset.isSuite === '1';

    fetch(`{{ route('admin.hotel-pos.rooms') }}?category_id=${categoryId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = document.getElementById('rooms-list');
                container.innerHTML = '';
                
                data.rooms.forEach(room => {
                    const roomCard = document.createElement('div');
                    roomCard.className = 'col-lg-3 col-md-4 col-sm-6 mb-3';
                    const cardDiv = document.createElement('div');
                    cardDiv.className = `card room-card h-100 ${room.is_available ? 'border-success' : 'border-secondary'}`;
                    cardDiv.style.cursor = room.is_available ? 'pointer' : 'not-allowed';
                    cardDiv.innerHTML = `
                        <div class="card-body text-center">
                            <h5 class="mb-2">${room.room_number}</h5>
                            ${room.room_name ? `<p class="text-muted mb-2">${room.room_name}</p>` : ''}
                            <h4 class="text-primary mb-2">₦${parseFloat(room.price_per_night).toLocaleString('en-NG', {minimumFractionDigits: 2})}</h4>
                            <small class="text-muted">per night</small>
                            <br><small class="text-info">₦${parseFloat(room.hourly_rate || 0).toLocaleString('en-NG', {minimumFractionDigits: 2})}/hour</small>
                            <br><br>
                            <span class="badge ${room.is_available ? 'bg-success' : 'bg-secondary'}">${room.status}</span>
                        </div>
                    `;
                    if (room.is_available) {
                        cardDiv.onclick = function(e) {
                            selectRoom(room.id, room.price_per_night, room.hourly_rate || 0, e);
                        };
                    }
                    roomCard.appendChild(cardDiv);
                    container.appendChild(roomCard);
                });
                
                document.getElementById('rooms-container').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading rooms:', error);
            alert('Error loading rooms. Please try again.');
        });
}

function selectRoom(roomId, price, hourlyRate = 0, event) {
    selectedRoomId = roomId;
    selectedRoomPrice = price;
    selectedHourlyRate = hourlyRate;
    
    document.querySelectorAll('.room-card').forEach(card => {
        card.classList.remove('border-primary', 'bg-primary-transparent', 'shadow');
    });
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('border-primary', 'bg-primary-transparent', 'shadow');
    }
    
    document.getElementById('room-price-per-night').value = `₦${parseFloat(price).toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
    document.getElementById('hourly-rate').value = parseFloat(hourlyRate || 0).toFixed(2);
    document.getElementById('room-booking-form').style.display = 'block';
    
    const checkInInput = document.getElementById('check-in-date');
    const checkOutInput = document.getElementById('check-out-date');
    if (checkInInput && !checkInInput.value) {
        checkInInput.value = new Date().toISOString().split('T')[0];
    }
    if (checkOutInput) {
        const checkInDate = new Date(checkInInput.value || new Date().toISOString().split('T')[0]);
        checkInDate.setDate(checkInDate.getDate() + 1);
        checkOutInput.min = checkInDate.toISOString().split('T')[0];
        if (!checkOutInput.value) {
            checkOutInput.value = checkInDate.toISOString().split('T')[0];
        }
    }
    setCurrentTimeInput('overnight-check-in-time');
    
    // Calculate based on current booking type
    const bookingType = document.getElementById('booking-type').value;
    if (bookingType === 'short_stay') {
        calculateHours();
    } else {
        calculateNights();
    }
}

function calculateNights() {
    const checkIn = document.getElementById('check-in-date').value;
    const checkOut = document.getElementById('check-out-date').value;
    
    if (checkIn && checkOut) {
        const checkInDate = new Date(checkIn);
        const checkOutDate = new Date(checkOut);
        const diffTime = Math.abs(checkOutDate - checkInDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        document.getElementById('number-of-nights').value = diffDays;
        calculateRoomTotal();
    }
}

function calculateRoomTotal() {
    const nights = parseInt(document.getElementById('number-of-nights').value) || 0;
    const pricePerNight = selectedRoomPrice;
    const subtotal = nights * pricePerNight;
    const discount = parseFloat(document.getElementById('room-discount').value) || 0;
    const tax = parseFloat(document.getElementById('room-tax').value) || 0;
    const serviceCharge = parseFloat(document.getElementById('room-service-charge').value) || 0;
    const total = subtotal - discount + tax + serviceCharge;
    
    document.getElementById('room-subtotal').textContent = `₦${subtotal.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
    document.getElementById('room-discount-display').textContent = `₦${discount.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
    document.getElementById('room-tax-display').textContent = `₦${tax.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
    document.getElementById('room-service-charge-display').textContent = `₦${serviceCharge.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
    document.getElementById('room-total').textContent = `₦${total.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
    
    autoFillRoomAmountPaid();
}

function formatPaidNumber(numValue) {
    if (numValue % 1 === 0) {
        return numValue.toLocaleString('en-NG', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }
    return numValue.toLocaleString('en-NG', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
}

function autoFillRoomAmountPaid(forceEmptyOnly = false) {
    const input = document.getElementById('room-amount-paid');
    if (!input) {
        return;
    }

    const total = parseFloat(document.getElementById('room-total').textContent.replace(/[₦,]/g, '')) || 0;
    const isEmpty = !input.value || input.value.trim() === '';

    if (forceEmptyOnly && !isEmpty) {
        calculateRoomChange();
        return;
    }

    if (amountPaidManuallyEdited && !isEmpty && !forceEmptyOnly) {
        calculateRoomChange();
        return;
    }

    if (total > 0) {
        input.value = formatPaidNumber(total);
        amountPaidManuallyEdited = false;
    }

    calculateRoomChange();
}

function formatAmountPaid(input) {
    amountPaidManuallyEdited = true;
    const cursorPosition = input.selectionStart;
    const originalLength = input.value.length;
    
    let value = input.value.replace(/[^\d.]/g, '');
    
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    if (parts.length === 2 && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    
    const numValue = parseFloat(value) || 0;
    
    // Format without forcing .00 - only show decimals if they exist and are not .00
    let formatted;
    if (numValue % 1 === 0) {
        // Whole number - no decimals
        formatted = numValue.toLocaleString('en-NG', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    } else {
        // Has decimals - show up to 2 decimal places
        formatted = numValue.toLocaleString('en-NG', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }
    
    input.value = formatted;
    calculateRoomChange();
    
    setTimeout(() => {
        const newLength = formatted.length;
        const lengthDiff = newLength - originalLength;
        const newPosition = Math.max(0, Math.min(cursorPosition + lengthDiff, formatted.length));
        input.setSelectionRange(newPosition, newPosition);
    }, 0);
}

function calculateRoomChange() {
    const total = parseFloat(document.getElementById('room-total').textContent.replace(/[₦,]/g, '')) || 0;
    const amountPaidInput = document.getElementById('room-amount-paid').value;
    const amountPaid = parseFloat(amountPaidInput.replace(/[,]/g, '')) || 0;
    const change = Math.max(0, amountPaid - total);
    
    document.getElementById('room-change').textContent = `₦${change.toLocaleString('en-NG', {minimumFractionDigits: 2})}`;
}

function processRoomBooking() {
    if (!selectedRoomId) {
        alert('Please select a room');
        return;
    }
    
    // Validate guest information
    const guestName = document.getElementById('guest-name').value.trim();
    const guestPhone = document.getElementById('guest-phone').value.trim();
    
    if (!guestName) {
        alert('Please enter guest name');
        return;
    }
    
    if (!guestPhone) {
        alert('Please enter guest phone number');
        return;
    }
    
    const bookingType = document.getElementById('booking-type').value;
    const customerCategory = getSelectedCustomerCategory();
    
    // Use FormData for file upload support
    const formDataObj = new FormData();
    formDataObj.append('room_id', selectedRoomId);
    formDataObj.append('booking_type', bookingType);
    formDataObj.append('booking_source', customerCategory);

    if (customerCategory === 'credit') {
        const customerId = document.getElementById('customer-select')?.value;
        if (!customerId) {
            alert('Please select a customer account for credit bookings');
            return;
        }
        const selectedOption = document.getElementById('customer-select').selectedOptions[0];
        const creditEnabled = selectedOption?.getAttribute('data-credit-enabled');
        if (creditEnabled !== 'true' && creditEnabled !== '1') {
            alert('This customer does not have credit enabled.');
            return;
        }
        formDataObj.append('customer_id', customerId);
    }

    formDataObj.append('guest_name', guestName);
    formDataObj.append('guest_phone', guestPhone);

    const guestEmail = document.getElementById('guest-email').value.trim();
    if (guestEmail) {
        formDataObj.append('guest_email', guestEmail);
    }

    let paymentMethod = document.getElementById('room-payment-method').value;
    if (customerCategory === 'credit') {
        paymentMethod = 'credit';
    }

    formDataObj.append('discount', parseFloat(document.getElementById('room-discount').value) || 0);
    formDataObj.append('tax', parseFloat(document.getElementById('room-tax').value) || 0);
    formDataObj.append('service_charge', parseFloat(document.getElementById('room-service-charge').value) || 0);
    formDataObj.append('payment_method', paymentMethod);

    const amountPaidValue = paymentMethod === 'credit'
        ? 0
        : (parseFloat(document.getElementById('room-amount-paid').value.replace(/[,]/g, '')) || 0);
    formDataObj.append('amount_paid', amountPaidValue);
    formDataObj.append('notes', document.getElementById('room-notes').value);
    const suiteSelected = document.getElementById('room-category-select').selectedOptions[0]?.dataset.isSuite === '1';
    formDataObj.append('is_full_suite_booking', (isFullSuiteBooking && suiteSelected) ? '1' : '0');
    
    // Append ID upload file if present
    const idUploadInput = document.getElementById('guest-id-upload');
    if (idUploadInput.files.length > 0) {
        formDataObj.append('guest_id_upload', idUploadInput.files[0]);
    }
    
    if (bookingType === 'short_stay') {
        const date = document.getElementById('short-stay-date').value;
        const checkInTime = document.getElementById('check-in-time').value;
        const checkOutTime = document.getElementById('check-out-time').value;
        const hourlyRate = parseFloat(document.getElementById('hourly-rate').value) || selectedHourlyRate;
        
        if (!date || !checkInTime || !checkOutTime) {
            alert('Please fill in all short-stay booking details');
            return;
        }

        if (!hourlyRate || hourlyRate <= 0) {
            alert('Please enter a valid hourly rate');
            return;
        }
        
        formDataObj.append('check_in_date', date);
        formDataObj.append('check_in_time', checkInTime);
        formDataObj.append('check_out_date', date);
        formDataObj.append('check_out_time', checkOutTime);
        formDataObj.append('hours_stayed', parseInt(document.getElementById('hours-stayed').value) || 0);
        formDataObj.append('hourly_rate', hourlyRate);
    } else {
        const checkIn = document.getElementById('check-in-date').value;
        const checkOut = document.getElementById('check-out-date').value;
        const checkInTime = document.getElementById('overnight-check-in-time').value;
        
        if (!checkIn || !checkOut) {
            alert('Please select check-in and check-out dates');
            return;
        }

        if (!checkInTime) {
            alert('Please select check-in time');
            return;
        }
        
        formDataObj.append('check_in_date', checkIn);
        formDataObj.append('check_in_time', checkInTime);
        formDataObj.append('check_out_date', checkOut);
    }
    
    if (paymentMethod !== 'credit' && amountPaidValue <= 0) {
        alert('Please enter the amount paid');
        return;
    }
    
    // Show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fe fe-loader me-2"></i>Processing...';
    
    fetch('{{ route('admin.room-bookings.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formDataObj
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                let message = data.message || data.error || 'Failed to create booking';
                if (data.errors) {
                    message = Object.values(data.errors).flat().join('\n');
                }
                throw new Error(message);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success || data.redirect) {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else if (data.booking_id) {
                window.location.href = '{{ route('admin.room-bookings.receipt', ':id') }}'.replace(':id', data.booking_id);
            } else {
                alert('Booking created but redirect failed. Please check bookings list.');
                window.location.href = '{{ route('admin.room-bookings.index') }}';
            }
        } else {
            let errorMsg = data.message || 'Failed to create booking';
            if (data.errors) {
                errorMsg += '\n\nErrors:\n' + Object.values(data.errors).flat().join('\n');
            }
            alert(errorMsg);
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating booking: ' + (error.message || 'Please try again.'));
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function clearRoomBooking() {
    selectedRoomId = null;
    selectedRoomPrice = 0;
    selectedHourlyRate = 0;
    document.getElementById('room-category-select').value = '';
    document.getElementById('rooms-container').style.display = 'none';
    document.getElementById('room-booking-form').style.display = 'none';
    document.getElementById('guest-name').value = '';
    document.getElementById('guest-phone').value = '';
    document.getElementById('guest-email').value = '';
    document.getElementById('guest-id-upload').value = '';
    document.getElementById('check-in-date').value = '';
    document.getElementById('check-out-date').value = '';
    document.getElementById('overnight-check-in-time').value = '';
    document.getElementById('short-stay-date').value = '';
    document.getElementById('check-in-time').value = '';
    document.getElementById('check-out-time').value = '';
    document.getElementById('hourly-rate').value = '';
    document.getElementById('room-amount-paid').value = '';
    amountPaidManuallyEdited = false;
    document.getElementById('room-discount').value = '0';
    document.getElementById('room-tax').value = '0';
    document.getElementById('room-service-charge').value = '0';
    document.getElementById('room-notes').value = '';

    const walkinRadio = document.getElementById('category-walkin');
    if (walkinRadio) {
        walkinRadio.checked = true;
        toggleCustomerCategory();
    }
    
    const url = new URL(window.location);
    url.searchParams.delete('room_id');
    window.history.replaceState({}, '', url);
}

document.addEventListener('DOMContentLoaded', function () {
    const paymentMethod = document.getElementById('room-payment-method');
    if (paymentMethod) {
        paymentMethod.addEventListener('change', toggleCreditPaymentUi);
    }
    toggleCustomerCategory();
});
</script>
@endpush

