<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            'correo' => ['required', 'string', 'email'],
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

        $credentials = array_merge($this->only('correo', 'password'), ['activo' => 1]);

        if (! Auth::attempt($credentials, false)) {
            if (config('rate_limits.login.enabled', true)) {
                $decaySeconds = max(1, (int) config('rate_limits.login.decay_seconds', 60));
                RateLimiter::hit($this->throttleKey(), $decaySeconds);
            }

            throw ValidationException::withMessages([
                'correo' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();
        if (! $user || ! $user->hasAnyRole(['administrador', 'operador', 'invitado'])) {
            Auth::logout();
            throw ValidationException::withMessages([
                'correo' => 'La cuenta no tiene rol asignado.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! config('rate_limits.login.enabled', true)) {
            return;
        }

        $maxAttempts = max(1, (int) config('rate_limits.login.max_attempts', 5));

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'correo' => trans('auth.throttle', [
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
        return Str::transliterate(Str::lower($this->string('correo')).'|'.$this->ip());
    }
}
