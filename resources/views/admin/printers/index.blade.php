@extends('layouts.admin')

@section('title', 'Printer Settings')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Printer Settings</li>
@endsection

@section('actions')
<a href="{{ route('admin.printers.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i> Add Printer
</a>
<a href="{{ route('admin.printers.troubleshoot') }}" class="btn btn-warning">
    <i class="fe fe-help-circle me-2"></i> Troubleshooting
</a>
<a href="{{ route('admin.printers.server-info') }}" class="btn btn-info" target="_blank">
    <i class="fe fe-server me-2"></i> Server Info
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Printer Configuration</h3>
    </div>
    <div class="card-body">
        @if($printers->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Connection</th>
                        <th>Paper Width</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($printers as $printer)
                    <tr>
                        <td><strong>{{ $printer->name }}</strong></td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($printer->type) }}</span>
                        </td>
                        <td>
                            @if($printer->type === 'network')
                                {{ $printer->ip_address }}:{{ $printer->port }}
                            @elseif($printer->type === 'usb')
                                {{ $printer->usb_port }}
                            @elseif($printer->type === 'bluetooth')
                                Bluetooth
                            @else
                                Browser Print
                            @endif
                        </td>
                        <td>{{ $printer->paper_width }}mm</td>
                        <td>
                            @if($printer->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.printers.edit', $printer) }}" class="btn btn-sm btn-primary">
                                <i class="fe fe-edit"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('admin.printers.destroy', $printer) }}" class="d-inline" onsubmit="return confirm('Delete this printer?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fe fe-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert alert-info">
            <i class="fe fe-info me-2"></i>No printers configured. 
            <a href="{{ route('admin.printers.create') }}">Add your first printer</a> to get started.
        </div>
        @endif
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h4 class="card-title mb-0">How to Connect XPrinter</h4>
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <strong>⚠️ Printer Not Showing in Browser?</strong><br>
            The browser print dialog only shows printers that are installed in Windows. You must add the printer to Windows first!
        </div>

        <h5>Step 1: Add XPrinter to Windows (Required for Browser Print)</h5>
        <ol>
            <li><strong>For USB XPrinter:</strong>
                <ul>
                    <li>Connect printer via USB cable</li>
                    <li>Download XPrinter drivers from <a href="https://www.xprinter.net" target="_blank">xprinter.net</a></li>
                    <li>Install the drivers</li>
                    <li>Go to Windows Settings → Devices → Printers & scanners</li>
                    <li>Click "Add a printer or scanner"</li>
                    <li>Windows should detect it automatically</li>
                </ul>
            </li>
            <li><strong>For Network XPrinter:</strong>
                <ul>
                    <li>Connect printer to network via Ethernet cable</li>
                    <li>Find printer's IP address (print test receipt or check router)</li>
                    <li>Go to Windows Settings → Devices → Printers & scanners</li>
                    <li>Click "Add a printer or scanner"</li>
                    <li>Click "The printer that I want isn't listed"</li>
                    <li>Select "Add a printer using an IP address or hostname"</li>
                    <li>Enter the IP address (e.g., 192.168.1.100)</li>
                    <li>Port: 9100 (Raw)</li>
                    <li>Select "Generic / Text Only" or install XPrinter drivers</li>
                    <li>Complete the setup</li>
                </ul>
            </li>
        </ol>

        <h5 class="mt-4">Step 2: Verify Printer in Windows</h5>
        <ol>
            <li>Open Windows Settings → Devices → Printers & scanners</li>
            <li>Your XPrinter should appear in the list</li>
            <li>If it shows "Offline", right-click and select "See what's printing"</li>
            <li>Check printer status and clear any stuck print jobs</li>
        </ol>

        <h5 class="mt-4">Step 3: Test in Browser</h5>
        <ol>
            <li>After adding to Windows, refresh your browser</li>
            <li>Click "Print Order Preview" in POS</li>
            <li>Your XPrinter should now appear in the print dialog</li>
            <li>Select it and print</li>
        </ol>

        <h5 class="mt-4">For Network/Ethernet XPrinter (Alternative - Direct IP Printing):</h5>
        <ol>
            <li>Connect your XPrinter to your network router using an Ethernet cable</li>
            <li>Find the printer's IP address (usually printed on a test receipt or check router admin panel)</li>
            <li>Add a new printer above and select "Network" type</li>
            <li>Enter the IP address (e.g., 192.168.1.100) and port (usually 9100)</li>
            <li>Select paper width (58mm or 80mm)</li>
            <li>Save and test</li>
            <li><strong>Note:</strong> This method requires additional setup for direct network printing</li>
        </ol>

        <h5 class="mt-4">For USB XPrinter:</h5>
        <ol>
            <li>Connect your XPrinter to the computer via USB cable</li>
            <li>Install XPrinter drivers from the manufacturer's website</li>
            <li>Add a new printer above and select "USB" type</li>
            <li>Select the USB port from the dropdown</li>
            <li>Select paper width and save</li>
        </ol>

        <h5 class="mt-4">For Browser Print (Default):</h5>
        <ol>
            <li>No configuration needed - works with any printer</li>
            <li>Uses browser's print dialog</li>
            <li>Select your XPrinter from the print dialog when printing</li>
        </ol>
    </div>
</div>
@endsection

