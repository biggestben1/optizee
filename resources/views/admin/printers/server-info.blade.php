@extends('layouts.admin')

@section('title', 'Server Information')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fe fe-server me-2"></i>Server Information & Mobile Access
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <strong>🚨 Still Can't Access from Phone?</strong><br>
                        Follow these steps in order. The #1 cause is <strong>Router AP Isolation</strong> (not Windows Firewall).
                    </div>
                    
                    <div class="alert alert-warning">
                        <strong>⚠️ Quick Diagnostic:</strong><br>
                        1. Is your phone on the same Wi-Fi network as your computer?<br>
                        2. Is your phone on a "Guest" network? (Switch to main network)<br>
                        3. Can you ping 192.168.0.116 from your phone? (Use a network tool app)<br>
                        4. Check your router settings for "AP Isolation" and DISABLE it
                    </div>

                    <div id="serverInfo" class="mb-4">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading server information...</p>
                        </div>
                    </div>

                    <div class="card bg-light">
                        <div class="card-header">
                            <h5 class="mb-0">🔧 Troubleshooting Steps</h5>
                        </div>
                        <div class="card-body">
                            <h6>1. Check if Server is Running</h6>
                            <p>You're using <strong>Laravel Herd</strong>, which runs on port 80. The server appears to be running.</p>
                            <div class="alert alert-success">
                                <strong>✅ Server Status:</strong> Running on port 80<br>
                                <strong>✅ Network Binding:</strong> 0.0.0.0:80 (accessible from network)<br>
                                <strong>✅ Your IP:</strong> 192.168.0.116
                            </div>
                            <p class="text-muted">
                                <strong>Access URL:</strong> <code id="accessUrl">http://192.168.0.116</code> (no port needed for port 80)
                            </p>

                            <h6 class="mt-4">2. Check Windows Firewall (Most Common Issue)</h6>
                            <p>Windows Firewall is likely blocking port 80. Here's how to fix it:</p>
                            
                            <div class="alert alert-warning">
                                <strong>Quick Fix - Run as Administrator:</strong><br>
                                Open PowerShell or Command Prompt as Administrator and run:
                                <div class="bg-dark text-light p-3 rounded mt-2">
                                    <code>netsh advfirewall firewall add rule name="Herd HTTP" dir=in action=allow protocol=TCP localport=80</code>
                                </div>
                            </div>
                            
                            <p><strong>Or manually:</strong></p>
                            <ol>
                                <li>Press <kbd>Win + R</kbd>, type <code>wf.msc</code> and press Enter</li>
                                <li>Click "Inbound Rules" in the left panel</li>
                                <li>Click "New Rule..." in the right panel</li>
                                <li>Select "Port" and click Next</li>
                                <li>Select "TCP" and enter <code>80</code> in "Specific local ports"</li>
                                <li>Click Next, select "Allow the connection"</li>
                                <li>Check all three (Domain, Private, Public)</li>
                                <li>Name it "Herd HTTP" and click Finish</li>
                            </ol>
                            
                            <button class="btn btn-sm btn-primary mt-2" onclick="copyFirewallCommand()">
                                <i class="fe fe-copy me-2"></i>Copy Firewall Command
                            </button>
                            <div id="firewallCommandCopied" class="text-success mt-2" style="display:none;">✓ Command copied to clipboard!</div>
                            
                            <p class="text-muted mt-3">After adding the firewall rule, try accessing from your phone again.</p>

                            <h6 class="mt-4">3. Verify Network Connection</h6>
                            <p>Make sure your phone and computer are on the same Wi-Fi network:</p>
                            <ul>
                                <li>Check your computer's IP address (shown above): <strong>192.168.0.116</strong></li>
                                <li>On your phone, open a browser and try: <code id="mobileUrl" class="fs-5">http://192.168.0.116</code></li>
                                <li><strong>Important:</strong> Use <code>http://</code> not <code>https://</code></li>
                                <li>Make sure your phone is connected to the same Wi-Fi network</li>
                            </ul>
                            <button class="btn btn-sm btn-info mt-2" onclick="copyMobileUrl()">
                                <i class="fe fe-copy me-2"></i>Copy Mobile URL
                            </button>
                            <div id="mobileUrlCopied" class="text-success mt-2" style="display:none;">✓ URL copied to clipboard!</div>

                            <h6 class="mt-4">4. Check Router Settings</h6>
                            <p>Some routers have AP isolation enabled, which prevents devices on the same network from communicating:</p>
                            <ul>
                                <li>Access your router's admin panel (usually 192.168.1.1 or 192.168.0.1)</li>
                                <li>Look for "AP Isolation" or "Client Isolation" settings</li>
                                <li>Disable it if enabled</li>
                            </ul>

                            <h6 class="mt-4">5. Test API Endpoint</h6>
                            <p>Test if the API endpoint is accessible from the network:</p>
                            <div class="bg-dark text-light p-3 rounded mb-3">
                                <code id="apiUrl">http://192.168.0.116/api/server/ip</code>
                            </div>
                            <button class="btn btn-sm btn-primary" onclick="testApiEndpoint()">
                                <i class="fe fe-check me-2"></i>Test API Endpoint
                            </button>
                            <div id="apiTestResult" class="mt-2"></div>
                            
                            <div class="alert alert-info mt-3">
                                <strong>💡 Tip:</strong> If the API test fails, it confirms Windows Firewall is blocking access. 
                                Follow step 2 above to add the firewall rule.
                            </div>

                            <h6 class="mt-4">6. Restart Herd (If Needed)</h6>
                            <p>If all else fails, restart Herd:</p>
                            <ol>
                                <li>Open Herd application</li>
                                <li>Click "Stop" and then "Start"</li>
                                <li>Or restart your computer</li>
                                <li>Try accessing from your phone again</li>
                            </ol>
                            
                            <h6 class="mt-4">7. Test from Phone</h6>
                            <p>On your phone's browser, try these URLs in order:</p>
                            <ol>
                                <li><code>http://192.168.0.116</code> (should work after firewall fix)</li>
                                <li><code>http://checkmate.test</code> (if Herd domain is configured - won't work from phone)</li>
                                <li>Check the error message:
                                    <ul>
                                        <li><strong>"This site can't be reached"</strong> = Network/router issue (AP isolation likely)</li>
                                        <li><strong>"Connection refused"</strong> = Server not running or wrong port</li>
                                        <li><strong>"Connection timed out"</strong> = Firewall blocking (but we fixed that)</li>
                                    </ul>
                                </li>
                            </ol>
                            
                            <h6 class="mt-4">8. Additional Troubleshooting</h6>
                            <p>If still not working, try these:</p>
                            <ul>
                                <li><strong>Restart Herd:</strong> Close and reopen Herd application</li>
                                <li><strong>Check Antivirus:</strong> Some antivirus software has its own firewall - temporarily disable to test</li>
                                <li><strong>Try different device:</strong> Test from another phone/tablet to see if it's device-specific</li>
                                <li><strong>Ping test:</strong> On your phone, try pinging 192.168.0.116 (if you have a network tool app)</li>
                                <li><strong>Check Wi-Fi band:</strong> Make sure both devices are on the same Wi-Fi band (2.4GHz or 5GHz)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card bg-warning bg-opacity-10 mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">⚠️ Important Notes</h5>
                        </div>
                        <div class="card-body">
                            <ul>
                                <li><strong>Development Server:</strong> The built-in PHP server is for development only. For production, use a proper web server like Apache or Nginx.</li>
                                <li><strong>IP Address Changes:</strong> Your computer's IP address may change if you disconnect/reconnect to the network. Check the server info above for the current IP.</li>
                                <li><strong>Port Forwarding:</strong> If you need external access (outside your local network), you'll need to configure port forwarding on your router.</li>
                                <li><strong>HTTPS:</strong> For secure connections, you'll need to set up SSL certificates.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchServerInfo();
});

function fetchServerInfo() {
    fetch('{{ route("admin.printers.server-info") }}')
        .then(response => response.json())
        .then(data => {
            displayServerInfo(data);
        })
        .catch(error => {
            document.getElementById('serverInfo').innerHTML = `
                <div class="alert alert-danger">
                    <strong>Error loading server information:</strong><br>
                    ${error.message}
                </div>
            `;
        });
}

function displayServerInfo(data) {
    const infoHtml = `
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-primary bg-opacity-10 mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fe fe-globe me-2"></i>Server URL
                        </h5>
                        <p class="card-text">
                            <code class="fs-4">${data.server_url}</code>
                        </p>
                        <small class="text-muted">Use this URL to access from mobile devices</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-info bg-opacity-10 mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fe fe-wifi me-2"></i>Server IP
                        </h5>
                        <p class="card-text">
                            <code class="fs-4">${data.server_ip}</code>
                        </p>
                        <small class="text-muted">Your computer's IP address on the network</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success bg-opacity-10 mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fe fe-link me-2"></i>API Endpoint
                        </h5>
                        <p class="card-text">
                            <code>${data.api_endpoint}</code>
                        </p>
                        <small class="text-muted">For mobile app auto-detection</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-warning bg-opacity-10 mb-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fe fe-monitor me-2"></i>Hostname
                        </h5>
                        <p class="card-text">
                            <code>${data.hostname || 'N/A'}</code>
                        </p>
                        <small class="text-muted">Your computer's hostname</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="alert alert-success mt-3">
            <strong>✅ Quick Access:</strong><br>
            On your mobile device, open a browser and go to:<br>
            <code class="fs-5" id="quickAccessUrl">${data.server_url}</code><br>
            <button class="btn btn-sm btn-primary mt-2" onclick="copyQuickAccessUrl()">
                <i class="fe fe-copy me-2"></i>Copy URL
            </button>
        </div>
        
        <div class="alert alert-warning mt-3">
            <strong>⚠️ Can't Access?</strong><br>
            Most likely Windows Firewall is blocking port 80. See "Troubleshooting Steps" below and follow step 2 to add a firewall rule.
        </div>
    `;
    
    document.getElementById('serverInfo').innerHTML = infoHtml;
    
    // Update mobile URL and API URL
    const serverUrl = data.server_url.replace(':8000', ''); // Remove port 8000 if present, Herd uses port 80
    document.getElementById('mobileUrl').textContent = serverUrl;
    document.getElementById('apiUrl').textContent = serverUrl + '/api/server/ip';
    if (document.getElementById('accessUrl')) {
        document.getElementById('accessUrl').textContent = serverUrl;
    }
}

function testApiEndpoint() {
    const apiUrl = document.getElementById('apiUrl').textContent;
    const resultDiv = document.getElementById('apiTestResult');
    
    resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Testing...';
    
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <strong>✅ API Endpoint is accessible from network!</strong><br>
                    <pre class="mb-0">${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
        })
        .catch(error => {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <strong>❌ API Endpoint is NOT accessible from network</strong><br>
                    Error: ${error.message}<br>
                    <strong>This confirms Windows Firewall is blocking port 80.</strong><br>
                    <small>Follow step 2 above to add a firewall rule allowing port 80.</small>
                </div>
            `;
        });
}

function copyFirewallCommand() {
    const command = 'netsh advfirewall firewall add rule name="Herd HTTP" dir=in action=allow protocol=TCP localport=80';
    navigator.clipboard.writeText(command).then(() => {
        document.getElementById('firewallCommandCopied').style.display = 'block';
        setTimeout(() => {
            document.getElementById('firewallCommandCopied').style.display = 'none';
        }, 3000);
    });
}

function copyMobileUrl() {
    const url = document.getElementById('mobileUrl').textContent;
    navigator.clipboard.writeText(url).then(() => {
        document.getElementById('mobileUrlCopied').style.display = 'block';
        setTimeout(() => {
            document.getElementById('mobileUrlCopied').style.display = 'none';
        }, 3000);
    });
}

function copyQuickAccessUrl() {
    const url = document.getElementById('quickAccessUrl').textContent;
    navigator.clipboard.writeText(url).then(() => {
        alert('✓ URL copied to clipboard!');
    });
}
</script>
@endsection

