<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        $user->load('roles.permissions', 'permissions');
        $user->roles_list = $user->roles->pluck('name');
        $user->permissions_list = $user->getAllPermissions()->pluck('name');

        return response()->json($user, 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->google2fa_enabled) {
                $request->session()->put('2fa:user:id', $user->id);
                $request->session()->put('2fa:remember', $request->boolean('remember'));
                return response()->json(['requires_2fa' => true]);
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            
            $user->load('roles.permissions', 'permissions');
            $user->roles_list = $user->roles->pluck('name');
            $user->permissions_list = $user->getAllPermissions()->pluck('name');
            
            return response()->json($user);
        }

        throw ValidationException::withMessages([
            'email' => ['Las credenciales proporcionadas son incorrectas.'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = \Illuminate\Support\Facades\Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? response()->json(['status' => __($status)])
            : ValidationException::withMessages(['email' => [__($status)]]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = \Illuminate\Support\Facades\Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(\Illuminate\Support\Str::random(60));

                $user->save();

                \Illuminate\Auth\Events\PasswordReset::dispatch($user);
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? response()->json(['status' => __($status)])
            : ValidationException::withMessages(['email' => [__($status)]]);
    }

    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['status' => 'Email is already verified.']);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'Verification link sent!']);
    }

    public function verify2fa(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = $request->session()->get('2fa:user:id');
        if (!$userId) {
            throw ValidationException::withMessages([
                'code' => ['La sesión de 2FA ha expirado o es inválida.'],
            ]);
        }

        $user = User::find($userId);
        if (!$user) {
            throw ValidationException::withMessages([
                'code' => ['Usuario inválido.'],
            ]);
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);

        if ($valid) {
            $remember = $request->session()->pull('2fa:remember', false);
            $request->session()->forget('2fa:user:id');

            Auth::login($user, $remember);
            $request->session()->regenerate();
            
            $user->load('roles.permissions', 'permissions');
            $user->roles_list = $user->roles->pluck('name');
            $user->permissions_list = $user->getAllPermissions()->pluck('name');
            
            return response()->json($user);
        }

        throw ValidationException::withMessages([
            'code' => ['El código proporcionado no es válido.'],
        ]);
    }
}
