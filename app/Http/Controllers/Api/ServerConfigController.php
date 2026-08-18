<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServerConfigController extends Controller
{
    /**
     * Get server configuration including IP and URL
     * This endpoint can be used by mobile apps to auto-detect server IP
     */
    public function getConfig(Request $request)
    {
        // Get the current request's IP and URL
        $serverIp = $request->ip();
        $serverUrl = $request->getSchemeAndHttpHost();
        $appUrl = config('app.url');
        
        // Auto-detect if APP_URL is localhost and we have a real IP
        if (str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
            // Use the request's host if it's not localhost
            if (!str_contains($serverUrl, 'localhost') && !str_contains($serverUrl, '127.0.0.1')) {
                $appUrl = $serverUrl;
            }
        }
        
        return response()->json([
            'server_ip' => $serverIp,
            'server_url' => $serverUrl,
            'api_url' => $serverUrl . '/api',
            'app_url' => $appUrl,
            'auto_detected' => true,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
    
    /**
     * Update server configuration (if needed for manual override)
     */
    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'server_url' => 'nullable|url',
            'auto_update' => 'nullable|boolean',
        ]);
        
        // Store in cache for quick access
        if (isset($validated['server_url'])) {
            Cache::put('mobile_server_url', $validated['server_url'], now()->addDays(30));
        }
        
        if (isset($validated['auto_update'])) {
            Cache::put('mobile_auto_update_ip', $validated['auto_update'], now()->addDays(30));
        }
        
        return response()->json([
            'message' => 'Configuration updated successfully',
            'server_url' => $validated['server_url'] ?? Cache::get('mobile_server_url'),
            'auto_update' => $validated['auto_update'] ?? Cache::get('mobile_auto_update_ip', true),
        ]);
    }
    
    /**
     * Get current server IP (simple endpoint for IP detection)
     */
    public function getIp(Request $request)
    {
        return response()->json([
            'ip' => $request->ip(),
            'url' => $request->getSchemeAndHttpHost(),
            'api_base_url' => $request->getSchemeAndHttpHost() . '/api',
        ]);
    }
}


















