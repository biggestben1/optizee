@extends('layouts.admin')

@section('title', 'Add Printer')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.printers.index') }}">Printer Settings</a></li>
<li class="breadcrumb-item active" aria-current="page">Add Printer</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Add Printer</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.printers.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Printer Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', 'XPrinter') }}" required>
                        <small class="text-muted">e.g., "XPrinter Receipt", "Kitchen Printer"</small>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Connection Type <span class="text-danger">*</span></label>
                        <select name="type" id="printer-type" class="form-select @error('type') is-invalid @enderror" required onchange="toggleConnectionFields()">
                            <option value="browser" {{ old('type') == 'browser' ? 'selected' : '' }}>Browser Print (Default)</option>
                            <option value="network" {{ old('type') == 'network' ? 'selected' : '' }}>Network/Ethernet (IP Address)</option>
                            <option value="usb" {{ old('type') == 'usb' ? 'selected' : '' }}>USB</option>
                            <option value="bluetooth" {{ old('type') == 'bluetooth' ? 'selected' : '' }}>Bluetooth</option>
                        </select>
                        @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="network-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">IP Address <span class="text-danger">*</span></label>
                                <input type="text" name="ip_address" id="ip_address" class="form-control @error('ip_address') is-invalid @enderror" 
                                       value="{{ old('ip_address') }}" placeholder="192.168.1.100">
                                <small class="text-muted">Find this on your printer's test receipt or router admin panel</small>
                                @error('ip_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Port</label>
                                <input type="number" name="port" class="form-control @error('port') is-invalid @enderror" 
                                       value="{{ old('port', 9100) }}" min="1" max="65535">
                                <small class="text-muted">Default: 9100 (ESC/POS)</small>
                                @error('port')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div id="usb-fields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">USB Port <span class="text-danger">*</span></label>
                            <input type="text" name="usb_port" id="usb_port" class="form-control @error('usb_port') is-invalid @enderror" 
                                   value="{{ old('usb_port') }}" placeholder="COM3, /dev/ttyUSB0, etc.">
                            <small class="text-muted">Check Device Manager (Windows) or /dev/ (Linux) for port name</small>
                            @error('usb_port')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Paper Width <span class="text-danger">*</span></label>
                        <select name="paper_width" class="form-select @error('paper_width') is-invalid @enderror" required>
                            <option value="58" {{ old('paper_width') == '58' ? 'selected' : '' }}>58mm (2 inches)</option>
                            <option value="80" {{ old('paper_width', '80') == '80' ? 'selected' : '' }}>80mm (3 inches)</option>
                        </select>
                        @error('paper_width')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="auto_cut" class="form-check-input" id="auto_cut" value="1" {{ old('auto_cut', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_cut">
                                Auto Cut Paper (if supported)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-2"></i> Save Printer
                        </button>
                        <a href="{{ route('admin.printers.index') }}" class="btn btn-secondary">
                            <i class="fe fe-x me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Quick Guide</h4>
            </div>
            <div class="card-body">
                <h6>Finding XPrinter IP Address:</h6>
                <ol class="small">
                    <li>Print a test receipt from your XPrinter</li>
                    <li>The IP address is usually printed on the receipt</li>
                    <li>Or check your router's admin panel for connected devices</li>
                    <li>Common XPrinter IPs: 192.168.1.100, 192.168.0.100</li>
                </ol>
                <hr>
                <h6>Testing Connection:</h6>
                <p class="small">After saving, you can test the printer connection from the printer list page.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleConnectionFields() {
    const type = document.getElementById('printer-type').value;
    const networkFields = document.getElementById('network-fields');
    const usbFields = document.getElementById('usb-fields');
    
    networkFields.style.display = type === 'network' ? 'block' : 'none';
    usbFields.style.display = type === 'usb' ? 'block' : 'none';
    
    // Set required attributes
    if (type === 'network') {
        document.getElementById('ip_address').required = true;
        document.getElementById('usb_port').required = false;
    } else if (type === 'usb') {
        document.getElementById('ip_address').required = false;
        document.getElementById('usb_port').required = true;
    } else {
        document.getElementById('ip_address').required = false;
        document.getElementById('usb_port').required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', toggleConnectionFields);
</script>
@endpush
















