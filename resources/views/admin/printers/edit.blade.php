@extends('layouts.admin')

@section('title', 'Edit Printer')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.printers.index') }}">Printer Settings</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Edit Printer</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.printers.update', $printerSetting) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Printer Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $printerSetting->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Connection Type <span class="text-danger">*</span></label>
                        <select name="type" id="printer-type" class="form-select @error('type') is-invalid @enderror" required onchange="toggleConnectionFields()">
                            <option value="browser" {{ old('type', $printerSetting->type) == 'browser' ? 'selected' : '' }}>Browser Print</option>
                            <option value="network" {{ old('type', $printerSetting->type) == 'network' ? 'selected' : '' }}>Network/Ethernet</option>
                            <option value="usb" {{ old('type', $printerSetting->type) == 'usb' ? 'selected' : '' }}>USB</option>
                            <option value="bluetooth" {{ old('type', $printerSetting->type) == 'bluetooth' ? 'selected' : '' }}>Bluetooth</option>
                        </select>
                        @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="network-fields" style="display: {{ old('type', $printerSetting->type) == 'network' ? 'block' : 'none' }};">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">IP Address <span class="text-danger">*</span></label>
                                <input type="text" name="ip_address" id="ip_address" class="form-control @error('ip_address') is-invalid @enderror" 
                                       value="{{ old('ip_address', $printerSetting->ip_address) }}">
                                @error('ip_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Port</label>
                                <input type="number" name="port" class="form-control @error('port') is-invalid @enderror" 
                                       value="{{ old('port', $printerSetting->port) }}" min="1" max="65535">
                                @error('port')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div id="usb-fields" style="display: {{ old('type', $printerSetting->type) == 'usb' ? 'block' : 'none' }};">
                        <div class="mb-3">
                            <label class="form-label">USB Port <span class="text-danger">*</span></label>
                            <input type="text" name="usb_port" id="usb_port" class="form-control @error('usb_port') is-invalid @enderror" 
                                   value="{{ old('usb_port', $printerSetting->usb_port) }}">
                            @error('usb_port')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Paper Width <span class="text-danger">*</span></label>
                        <select name="paper_width" class="form-select @error('paper_width') is-invalid @enderror" required>
                            <option value="58" {{ old('paper_width', $printerSetting->paper_width) == '58' ? 'selected' : '' }}>58mm</option>
                            <option value="80" {{ old('paper_width', $printerSetting->paper_width) == '80' ? 'selected' : '' }}>80mm</option>
                        </select>
                        @error('paper_width')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="auto_cut" class="form-check-input" id="auto_cut" value="1" {{ old('auto_cut', $printerSetting->auto_cut) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_cut">
                                Auto Cut Paper
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', $printerSetting->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $printerSetting->notes) }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-save me-2"></i> Update Printer
                        </button>
                        <a href="{{ route('admin.printers.index') }}" class="btn btn-secondary">
                            <i class="fe fe-x me-2"></i> Cancel
                        </a>
                    </div>
                </form>
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
</script>
@endpush
















