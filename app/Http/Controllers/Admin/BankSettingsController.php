<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class BankSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin && !auth()->user()->isManager()) {
                abort(403, 'You do not have permission to manage bank settings.');
            }
            return $next($request);
        });
    }

    public function edit()
    {
        return view('admin.bank-settings.edit', [
            'bank_name' => Setting::getValue('bank.name', ''),
            'account_name' => Setting::getValue('bank.account_name', ''),
            'account_number' => Setting::getValue('bank.account_number', ''),
            'footer_body' => Setting::getValue('site.footer_body', ''),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'footer_body' => 'nullable|string',
        ]);

        Setting::setValue('bank.name', $validated['bank_name']);
        Setting::setValue('bank.account_name', $validated['account_name']);
        Setting::setValue('bank.account_number', $validated['account_number']);
        Setting::setValue('site.footer_body', $validated['footer_body'] ?? '');

        return redirect()->route('admin.bank-settings.edit')->with('success', 'Bank settings updated successfully.');
    }
}

