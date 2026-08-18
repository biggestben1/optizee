@extends('layouts.admin')

@section('title', 'Printer Troubleshooting')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.printers.index') }}">Printer Settings</a></li>
<li class="breadcrumb-item active" aria-current="page">Troubleshooting</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">XPrinter Not Showing in Browser Print Dialog?</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <strong>💡 Important:</strong> The browser print dialog only shows printers that are installed and configured in Windows. You must add your XPrinter to Windows first!
        </div>

        <h4>Solution: Add XPrinter to Windows</h4>

        <div class="accordion" id="troubleshootAccordion">
            <!-- USB Printer -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#usb-printer">
                        <strong>For USB XPrinter</strong>
                    </button>
                </h2>
                <div id="usb-printer" class="accordion-collapse collapse show" data-bs-parent="#troubleshootAccordion">
                    <div class="accordion-body">
                        <ol>
                            <li><strong>Connect the printer:</strong>
                                <ul>
                                    <li>Plug the USB cable into your computer</li>
                                    <li>Power on the XPrinter</li>
                                    <li>Wait for Windows to detect it</li>
                                </ul>
                            </li>
                            <li><strong>Install drivers:</strong>
                                <ul>
                                    <li>Download XPrinter drivers from <a href="https://www.xprinter.net" target="_blank">xprinter.net</a></li>
                                    <li>Or search for your specific model (e.g., "XP-80C drivers")</li>
                                    <li>Run the installer and follow the setup wizard</li>
                                </ul>
                            </li>
                            <li><strong>Add to Windows:</strong>
                                <ul>
                                    <li>Press <kbd>Windows Key + I</kbd> to open Settings</li>
                                    <li>Go to <strong>Devices</strong> → <strong>Printers & scanners</strong></li>
                                    <li>Click <strong>"Add a printer or scanner"</strong></li>
                                    <li>Windows should detect your XPrinter automatically</li>
                                    <li>If not, click <strong>"The printer that I want isn't listed"</strong></li>
                                    <li>Select <strong>"Add a local printer"</strong></li>
                                    <li>Choose the USB port (usually COM3, COM4, etc.)</li>
                                    <li>Select the XPrinter driver you installed</li>
                                </ul>
                            </li>
                            <li><strong>Test:</strong>
                                <ul>
                                    <li>Right-click the printer → <strong>"Printer properties"</strong></li>
                                    <li>Click <strong>"Print Test Page"</strong></li>
                                    <li>If it prints, you're done!</li>
                                </ul>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Network Printer -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#network-printer">
                        <strong>For Network/Ethernet XPrinter</strong>
                    </button>
                </h2>
                <div id="network-printer" class="accordion-collapse collapse" data-bs-parent="#troubleshootAccordion">
                    <div class="accordion-body">
                        <ol>
                            <li><strong>Connect to network:</strong>
                                <ul>
                                    <li>Connect XPrinter to router via Ethernet cable</li>
                                    <li>Power on the printer</li>
                                    <li>Wait for network connection (check lights on printer)</li>
                                </ul>
                            </li>
                            <li><strong>Find IP address:</strong>
                                <ul>
                                    <li>Print a test receipt - IP is usually printed on it</li>
                                    <li>Or check your router admin panel (192.168.1.1 or 192.168.0.1)</li>
                                    <li>Look for "Connected Devices" or "DHCP Client List"</li>
                                    <li>Find "XPrinter" or the MAC address</li>
                                </ul>
                            </li>
                            <li><strong>Add to Windows:</strong>
                                <ul>
                                    <li>Press <kbd>Windows Key + I</kbd> to open Settings</li>
                                    <li>Go to <strong>Devices</strong> → <strong>Printers & scanners</strong></li>
                                    <li>Click <strong>"Add a printer or scanner"</strong></li>
                                    <li>Click <strong>"The printer that I want isn't listed"</strong></li>
                                    <li>Select <strong>"Add a printer using an IP address or hostname"</strong></li>
                                    <li>Enter the IP address (e.g., 192.168.1.100)</li>
                                    <li>Port: <strong>9100</strong> (Raw)</li>
                                    <li>Device type: <strong>"Generic Network Card"</strong> or install XPrinter drivers</li>
                                    <li>Click Next and complete setup</li>
                                </ul>
                            </li>
                            <li><strong>Test connection:</strong>
                                <ul>
                                    <li>Open Command Prompt</li>
                                    <li>Type: <code>ping 192.168.1.100</code> (use your printer's IP)</li>
                                    <li>If you get replies, the printer is reachable</li>
                                    <li>Print a test page from Windows</li>
                                </ul>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Verify Printer -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#verify-printer">
                        <strong>Verify Printer is Working</strong>
                    </button>
                </h2>
                <div id="verify-printer" class="accordion-collapse collapse" data-bs-parent="#troubleshootAccordion">
                    <div class="accordion-body">
                        <ol>
                            <li><strong>Check Windows Printers List:</strong>
                                <ul>
                                    <li>Go to Settings → Devices → Printers & scanners</li>
                                    <li>Your XPrinter should appear in the list</li>
                                    <li>Status should be "Ready" (not "Offline")</li>
                                </ul>
                            </li>
                            <li><strong>If printer shows "Offline":</strong>
                                <ul>
                                    <li>Right-click the printer → <strong>"See what's printing"</strong></li>
                                    <li>Check for stuck print jobs and delete them</li>
                                    <li>Right-click printer → <strong>"Use printer offline"</strong> (uncheck if checked)</li>
                                    <li>Restart the printer</li>
                                </ul>
                            </li>
                            <li><strong>Test print from Windows:</strong>
                                <ul>
                                    <li>Right-click printer → <strong>"Printer properties"</strong></li>
                                    <li>Click <strong>"Print Test Page"</strong></li>
                                    <li>If it prints, the printer is working!</li>
                                </ul>
                            </li>
                            <li><strong>Test in browser:</strong>
                                <ul>
                                    <li>Refresh your browser</li>
                                    <li>Click "Print Order Preview" in POS</li>
                                    <li>Your XPrinter should now appear in the print dialog</li>
                                </ul>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Common Issues -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#common-issues">
                        <strong>Common Issues & Solutions</strong>
                    </button>
                </h2>
                <div id="common-issues" class="accordion-collapse collapse" data-bs-parent="#troubleshootAccordion">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <strong>❌ Printer not detected by Windows:</strong>
                            <ul>
                                <li>Check USB cable connection</li>
                                <li>Try a different USB port</li>
                                <li>Install/update XPrinter drivers</li>
                                <li>Restart computer and printer</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <strong>❌ Printer shows "Offline":</strong>
                            <ul>
                                <li>Check printer is powered on</li>
                                <li>Check network cable (for network printers)</li>
                                <li>Clear stuck print jobs</li>
                                <li>Right-click → "Use printer offline" (uncheck)</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <strong>❌ Can't find IP address:</strong>
                            <ul>
                                <li>Print a test receipt from printer buttons</li>
                                <li>Check router admin panel (192.168.1.1)</li>
                                <li>Use network scanner tools</li>
                                <li>Check printer's LCD display (if available)</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <strong>❌ Browser still doesn't show printer:</strong>
                            <ul>
                                <li>Close and reopen browser</li>
                                <li>Clear browser cache</li>
                                <li>Try a different browser (Chrome recommended)</li>
                                <li>Check Windows default printer settings</li>
                                <li>Restart Windows Print Spooler service</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('admin.printers.index') }}" class="btn btn-primary">
                <i class="fe fe-arrow-left me-2"></i> Back to Printer Settings
            </a>
        </div>
    </div>
</div>
@endsection
















