<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/2fa-verify', [AuthController::class, 'verify2fa']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.store');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $user->load('roles.permissions', 'permissions');
        
        $user->roles_list = $user->roles->pluck('name');
        $user->permissions_list = $user->getAllPermissions()->pluck('name');
        
        return $user;
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])->middleware('throttle:6,1')->name('verification.send');
    
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);
    
    // Perfil
    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'updateProfile']);
    Route::post('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword']);
    Route::get('/profile/2fa/generate', [\App\Http\Controllers\ProfileController::class, 'generate2faSecret']);
    Route::post('/profile/2fa/enable', [\App\Http\Controllers\ProfileController::class, 'enable2fa']);
    Route::post('/profile/2fa/disable', [\App\Http\Controllers\ProfileController::class, 'disable2fa']);
    
    // Integraciones / WhatsApp
    Route::prefix('admin/integraciones/whatsapp')->group(function () {
        Route::get('/config', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'getConfig']);
        Route::post('/config', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'saveConfig']);
        Route::get('/status', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'getStatus']);
        Route::post('/connect', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'connect']);
        Route::post('/disconnect', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'disconnect']);
        Route::post('/reconnect', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'reconnect']);
        Route::post('/remove-session', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'removeSession']);
    });

    // Monitoreo
    Route::prefix('admin/monitoreo')->middleware('permission:monitoreo.view')->group(function () {
        Route::get('/database', [\App\Http\Controllers\Admin\MonitoreoController::class, 'getDatabaseMetrics']);
        Route::post('/database/export', [\App\Http\Controllers\Admin\MonitoreoController::class, 'exportDatabase']);
        Route::post('/database/import', [\App\Http\Controllers\Admin\MonitoreoController::class, 'importDatabase']);
        Route::get('/server', [\App\Http\Controllers\Admin\MonitoreoController::class, 'getServerMetrics']);
        Route::get('/sesiones', [\App\Http\Controllers\Admin\MonitoreoController::class, 'getSessions']);
        Route::get('/auditoria', [\App\Http\Controllers\Admin\MonitoreoController::class, 'getAuditoria']);
    });

    // Chat Routes
    Route::get('/chat/users', [\App\Http\Controllers\ChatController::class, 'users']);
    Route::get('/chat/messages/{user}', [\App\Http\Controllers\ChatController::class, 'messages']);
    Route::post('/chat/messages', [\App\Http\Controllers\ChatController::class, 'send']);
    
    // Módulo Base de Estudiantes
    Route::apiResource('estudiantes', \App\Http\Controllers\EstudianteController::class);
    Route::apiResource('representantes', \App\Http\Controllers\RepresentanteController::class);
});
