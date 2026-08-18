<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class CloudFooterController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin && !auth()->user()->isManager()) {
                abort(403, 'You do not have permission to manage the footer.');
            }
            return $next($request);
        });
    }

    public function edit()
    {
        return view('admin.cloud-footer.edit', [
            'footer_body' => Setting::getValue('site.footer_body', ''),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'footer_body' => 'nullable|string',
        ]);

        Setting::setValue('site.footer_body', $validated['footer_body'] ?? '');

        return redirect()->route('admin.cloud-footer.edit')->with('success', 'Footer updated successfully.');
    }
}

