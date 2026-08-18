<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrinterSetting;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin && !auth()->user()->isManager()) {
                abort(403, 'You do not have permission to configure printers.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $printers = PrinterSetting::latest()->get();
        return view('admin.printers.index', compact('printers'));
    }

    public function create()
    {
        return view('admin.printers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:usb,network,bluetooth,browser',
            'ip_address' => 'required_if:type,network|nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'usb_port' => 'required_if:type,usb|nullable|string',
            'paper_width' => 'required|integer|in:58,80',
            'auto_cut' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['connection_type'] = $validated['type'];
        $validated['port'] = $validated['port'] ?? 9100;
        $validated['auto_cut'] = $request->boolean('auto_cut', true);
        $validated['is_active'] = true;

        PrinterSetting::create($validated);

        return redirect()->route('admin.printers.index')
            ->with('success', 'Printer configured successfully.');
    }

    public function edit(PrinterSetting $printerSetting)
    {
        return view('admin.printers.edit', compact('printerSetting'));
    }

    public function update(Request $request, PrinterSetting $printerSetting)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:usb,network,bluetooth,browser',
            'ip_address' => 'required_if:type,network|nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'usb_port' => 'required_if:type,usb|nullable|string',
            'paper_width' => 'required|integer|in:58,80',
            'auto_cut' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['connection_type'] = $validated['type'];
        $validated['port'] = $validated['port'] ?? 9100;
        $validated['auto_cut'] = $request->boolean('auto_cut', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        $printerSetting->update($validated);

        return redirect()->route('admin.printers.index')
            ->with('success', 'Printer settings updated successfully.');
    }

    public function destroy(PrinterSetting $printerSetting)
    {
        $printerSetting->delete();
        return redirect()->route('admin.printers.index')
            ->with('success', 'Printer deleted successfully.');
    }

    public function test(PrinterSetting $printerSetting)
    {
        return response()->json([
            'success' => true,
            'message' => 'Test print command sent. Check your printer.',
            'printer' => $printerSetting,
        ]);
    }

    public function troubleshoot()
    {
        return view('admin.printers.troubleshoot');
    }

    public function serverInfo(Request $request)
    {
        $serverIp = $request->ip();
        $serverUrl = $request->getSchemeAndHttpHost();
        $appUrl = config('app.url');
        
        // Get local network IP
        $localIp = gethostbyname(gethostname());
        if ($localIp === gethostname()) {
            // If gethostbyname failed, try alternative method
            $localIp = 'Unable to detect';
        }
        
        // Get all network interfaces (Windows)
        $networkInfo = [];
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('ipconfig 2>&1');
            $networkInfo['ipconfig'] = $output;
        }
        
        return response()->json([
            'server_ip' => $serverIp,
            'server_url' => $serverUrl,
            'app_url' => $appUrl,
            'local_ip' => $localIp,
            'hostname' => gethostname(),
            'network_info' => $networkInfo,
            'accessible_url' => $serverUrl,
            'api_endpoint' => $serverUrl . '/api/server/ip',
        ]);
    }
}
