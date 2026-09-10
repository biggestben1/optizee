<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MobileAppController extends Controller
{
    public function loginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->homeRedirect(Auth::user());
        }

        return view('mobile.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return $this->homeRedirect(Auth::user());
    }

    public function quickLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'login_code' => ['required', 'string', 'size:4', 'regex:/^[0-9]{4}$/'],
        ]);

        $user = User::where('login_code', $request->login_code)->first();

        if (!$user || !$user->is_active) {
            return back()->withErrors([
                'login_code' => 'Invalid or inactive login code.',
            ])->onlyInput('login_code');
        }

        $allowed = $user->is_admin
            || $user->isManager()
            || $user->isSupervisor()
            || $user->isCashier()
            || $user->isKitchen();

        if (!$allowed) {
            return back()->withErrors([
                'login_code' => 'This app is only for cashiers and kitchen staff.',
            ])->onlyInput('login_code');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->homeRedirect($user);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('app.login');
    }

    public function home(): RedirectResponse
    {
        return $this->homeRedirect(Auth::user());
    }

    public function pos(): View
    {
        $user = Auth::user();

        if ($user->isKitchen() && !$user->canAccessPOS()) {
            abort(403, 'Kitchen staff cannot access the cashier POS.');
        }

        if (!$user->is_admin && !$user->canAccessPOS()) {
            abort(403, 'You do not have permission to access the POS.');
        }

        $categories = Category::active()
            ->with(['products' => fn ($q) => $q->active()->where('stock_quantity', '>', 0)->orderBy('name')])
            ->orderBy('name')
            ->get();

        $tables = Table::active()->get()->sortBy(function ($table) {
            preg_match('/(\d+)/', $table->number, $matches);
            return [(int) ($matches[1] ?? 999999), $table->number];
        })->values();

        $customers = Customer::active()->withCredit()->get();

        $readyKitchenOrders = Sale::with(['items.product', 'table', 'tableGuest', 'customer'])
            ->completed()
            ->kitchenReady()
            ->today()
            ->orderBy('kitchen_ready_at', 'desc')
            ->take(20)
            ->get();

        return view('mobile.cashier.pos', compact('categories', 'tables', 'customers', 'readyKitchenOrders'));
    }

    public function kitchen(): View
    {
        $user = Auth::user();

        if (!$user->canAccessKitchen()) {
            abort(403, 'You do not have permission to access the kitchen.');
        }

        return view('mobile.kitchen.index', [
            'canControl' => $user->canControlKitchenOrders(),
            'userName' => $user->name,
        ]);
    }

    protected function homeRedirect($user): RedirectResponse
    {
        if (!$user) {
            return redirect()->route('app.login');
        }

        if ($user->isKitchen()) {
            return redirect()->route('app.kitchen');
        }

        if ($user->isCashier() || $user->canAccessPOS()) {
            return redirect()->route('app.pos');
        }

        if ($user->canAccessKitchen()) {
            return redirect()->route('app.kitchen');
        }

        return redirect()->route('app.login')->withErrors([
            'email' => 'This app is only for cashiers and kitchen staff.',
        ]);
    }
}
