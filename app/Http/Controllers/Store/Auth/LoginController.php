<?php

namespace App\Http\Controllers\Store\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Muestra la vista del formulario de login de la tienda.
     */
    public function create()
    {
        return view('store.auth.login');
    }

    /**
     * Maneja el intento de autenticación para la tienda.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'login' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validated = $validator->validated();

            $key = Str::transliterate(Str::lower($validated['login']) . '|' . $request->ip());

            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);
                throw ValidationException::withMessages([
                    'login' => __('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => ceil($seconds / 60),
                    ]),
                ]);
            }

            $credentials = [
                'password' => $validated['password'],
            ];

            $loginField = filter_var($validated['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            $credentials[$loginField] = $validated['login'];

            if (!Auth::attempt($credentials, $request->boolean('remember'))) {
                RateLimiter::hit($key);

                throw ValidationException::withMessages([
                    'login' => __('auth.failed'),
                ]);
            }

            RateLimiter::clear($key);

            $request->session()->regenerate();

            $user = Auth::user();

            // Specific logic for store can be added here.
            // For now, we assume email verification is still needed.
            if (!$user->hasVerifiedEmail()) {
                // We use the main verification notice route
                $verificationUrl = route('verification.notice');
                if ($request->expectsJson()) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return response()->json(['redirect_url' => $verificationUrl]);
                }
                return redirect($verificationUrl);
            }

            // Redirect to the store's home page after login
            $redirectUrl = route('home'); // Assuming a 'home' route exists for the store

            if ($request->expectsJson()) {
                return response()->json(['redirect_url' => $redirectUrl]);
            }

            return redirect()->intended($redirectUrl);

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Throwable $e) {
            Log::error($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Ha ocurrido un error en el servidor.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            throw $e;
        }
    }
}