<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'], // Can be email, username, or login_code
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->input('login');
        $password = $this->input('password');

        // Try to find user by email, username, or login_code
        $user = User::where('email', $login)
            ->orWhere('name', $login)
            ->orWhere('login_code', $login)
            ->first();

        // If user found, try to authenticate
        if ($user && Auth::attempt(
            ['email' => $user->email, 'password' => $password],
            $this->boolean('remember')
        )) {
            // Check if user has admin access OR has a role
            if (!$user->is_admin && !$user->role) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'login' => 'You do not have access to the system.',
                ]);
            }

            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'login' => 'Your account has been deactivated.',
                ]);
            }

            RateLimiter::clear($this->throttleKey());
            return;
        }

        // Authentication failed
        RateLimiter::hit($this->throttleKey());
        throw ValidationException::withMessages([
            'login' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}
