<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json(['message' => 'Perfil actualizado con éxito']);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Contraseña actualizada con éxito']);
    }

    public function generate2faSecret(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        // Generar un nuevo secreto
        $secret = $google2fa->generateSecretKey();
        
        // Guardar el secreto temporalmente en cache o mandarlo al frontend para validación.
        // Lo mandaremos al frontend para que lo mande de vuelta al confirmar (con validación fuerte) o guardarlo en sesión.
        // Pero Laravel Sanctum (SPA) permite guardar en la sesión:
        $request->session()->put('2fa_secret_temp', $secret);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($qrCodeUrl);

        return response()->json([
            'qr_code_svg' => $svg,
            'secret' => $secret
        ]);
    }

    public function enable2fa(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'secret' => 'required|string'
        ]);

        $google2fa = new Google2FA();
        $user = $request->user();
        
        // Verificar el código provisto
        $valid = $google2fa->verifyKey($request->secret, $request->code);

        if ($valid) {
            $user->update([
                'google2fa_secret' => $request->secret,
                'google2fa_enabled' => true,
            ]);
            $request->session()->forget('2fa_secret_temp');

            return response()->json(['message' => 'Autenticación de Dos Pasos habilitada correctamente.']);
        }

        return response()->json(['message' => 'El código proporcionado no es válido.'], 400);
    }

    public function disable2fa(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => 'required|string'
        ]);

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['La contraseña es incorrecta.'],
            ]);
        }

        $user->update([
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
        ]);

        return response()->json(['message' => 'Autenticación de Dos Pasos deshabilitada correctamente.']);
    }
}
