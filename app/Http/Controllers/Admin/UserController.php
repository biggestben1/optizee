<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct()
    {
        // Only admins and managers can access user management
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin && !auth()->user()->isManager()) {
                abort(403, 'You do not have permission to manage staff.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('role', fn($q) => $q->where('name', $request->role));
        }

        $users = $query->latest()->paginate(15);
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();

        // Managers cannot add other managers - only admins can
        if (!auth()->user()->is_admin) {
            $roles = $roles->filter(fn($role) => $role->name !== 'manager' && $role->name !== 'supervisor');
        }

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(6)],
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        // Prevent managers from creating other managers
        $selectedRole = Role::find($validated['role_id']);
        if (!auth()->user()->is_admin && $selectedRole && $selectedRole->name === 'manager') {
            return back()->with('error', 'Only administrators can create manager accounts.')->withInput();
        }

        // Prevent non-admins from creating supervisors
        if (!auth()->user()->is_admin && $selectedRole && $selectedRole->name === 'supervisor') {
            return back()->with('error', 'Only administrators can create supervisor accounts.')->withInput();
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_admin'] = false;

        $user = User::create($validated);

        AuditLog::log('user_created', "Created user: {$user->name}", $user);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        if ($user->is_admin && !auth()->user()->is_admin) {
            return back()->with('error', 'You cannot edit admin users.');
        }

        // Managers cannot edit other managers
        if (!auth()->user()->is_admin && $user->role && $user->role->name === 'manager') {
            return back()->with('error', 'Only administrators can edit manager accounts.');
        }

        $roles = Role::all();

        // Managers cannot assign manager or supervisor role
        if (!auth()->user()->is_admin) {
            $roles = $roles->filter(fn($role) => $role->name !== 'manager' && $role->name !== 'supervisor');
        }

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->is_admin && !auth()->user()->is_admin) {
            return back()->with('error', 'You cannot edit admin users.');
        }

        // Managers cannot edit other managers
        if (!auth()->user()->is_admin && $user->role && $user->role->name === 'manager') {
            return back()->with('error', 'Only administrators can edit manager accounts.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(6)],
            'role_id' => 'required|exists:roles,id',
            'login_code' => ['nullable', 'string', 'size:4', 'regex:/^[0-9]{4}$/', 'unique:users,login_code,' . $user->id],
            'is_active' => 'boolean',
        ]);

        // Prevent managers from assigning manager role
        $selectedRole = Role::find($validated['role_id']);
        if (!auth()->user()->is_admin && $selectedRole && $selectedRole->name === 'manager') {
            return back()->with('error', 'Only administrators can assign manager role.')->withInput();
        }

        // Prevent non-admins from assigning supervisor role
        if (!auth()->user()->is_admin && $selectedRole && $selectedRole->name === 'supervisor') {
            return back()->with('error', 'Only administrators can assign supervisor role.')->withInput();
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle login code - set to null if empty
        if (empty($validated['login_code'])) {
            $validated['login_code'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $oldValues = $user->toArray();
        $user->update($validated);

        AuditLog::log('user_updated', "Updated user: {$user->name}", $user, $oldValues, $user->toArray());

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'Cannot delete admin users.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }

        if ($user->sales()->count() > 0) {
            // Deactivate instead of delete
            $user->update(['is_active' => false]);
            AuditLog::log('user_deactivated', "Deactivated user: {$user->name}", $user);
            return redirect()->route('admin.users.index')
                ->with('success', 'User deactivated successfully (has sales history).');
        }

        AuditLog::log('user_deleted', "Deleted user: {$user->name}", $user);
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function generateCode(User $user)
    {
        if ($user->is_admin && !auth()->user()->is_admin) {
            return back()->with('error', 'You cannot generate codes for admin users.');
        }

        // Generate unique 4-digit code
        $code = $this->generateUniqueCode();

        $oldCode = $user->login_code;
        $user->login_code = $code;
        $user->save();

        AuditLog::log('login_code_generated', "Generated login code for: {$user->name} (Code: {$code})", $user);

        return redirect()->route('admin.users.index')
            ->with('success', "Login code generated for {$user->name}: <strong>{$code}</strong>");
    }

    private function generateUniqueCode()
    {
        $maxAttempts = 100;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $code = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

            $exists = User::where('login_code', $code)->exists();
            if (!$exists) {
                return $code;
            }

            $attempt++;
        }

        // If we can't find a unique code, try sequential
        for ($i = 1000; $i <= 9999; $i++) {
            $code = str_pad($i, 4, '0', STR_PAD_LEFT);
            $exists = User::where('login_code', $code)->exists();
            if (!$exists) {
                return $code;
            }
        }

        throw new \Exception("Could not generate unique login code!");
    }
}


