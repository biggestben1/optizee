<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = User::with('role')->where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        return $this->issueToken($user, $validated['device_name'] ?? 'flutter-staff');
    }

    public function loginWithCode(Request $request)
    {
        $validated = $request->validate([
            'login_code' => 'required|string|size:4|regex:/^[0-9]{4}$/',
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = User::with('role')->where('login_code', $validated['login_code'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'login_code' => ['Invalid login code.'],
            ]);
        }

        return $this->issueToken($user, $validated['device_name'] ?? 'flutter-staff');
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('role');

        return response()->json([
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    protected function issueToken(User $user, string $deviceName)
    {
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        $allowed = $user->is_admin
            || $user->isManager()
            || $user->isSupervisor()
            || $user->isCashier()
            || $user->isKitchen();

        if (!$allowed) {
            throw ValidationException::withMessages([
                'email' => ['This app is only for cashiers and kitchen staff.'],
            ]);
        }

        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    protected function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->name,
            'role_label' => $user->role?->display_name ?? $user->role?->name,
            'is_admin' => (bool) $user->is_admin,
            'is_cashier' => $user->isCashier(),
            'is_kitchen' => $user->isKitchen(),
            'can_access_pos' => $user->is_admin || $user->canAccessPOS(),
            'can_access_kitchen' => $user->is_admin || $user->canAccessKitchen(),
            'can_control_kitchen' => $user->canControlKitchenOrders(),
            'home' => ($user->isKitchen() && !$user->canAccessPOS()) ? 'kitchen' : 'pos',
        ];
    }
}
