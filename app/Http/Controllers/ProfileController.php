<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Don't allow email updates - remove email from validated data
        $validated = $request->validated();
        unset($validated['email']);
        
        $request->user()->fill($validated);
        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update user's login code and password.
     */
    public function updateCredentials(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'login_code' => ['nullable', 'string', 'size:4', 'regex:/^[0-9]{4}$/', 'unique:users,login_code,' . auth()->id()],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $updated = false;

        // Update login code if provided
        if ($request->filled('login_code')) {
            $user->login_code = $request->login_code;
            $updated = true;
        }

        // Update password if provided
        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
            $updated = true;
        }

        if ($updated) {
            $user->save();
            return Redirect::route('profile.edit')->with('status', 'credentials-updated');
        }

        return Redirect::route('profile.edit')->with('error', 'No changes made.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
