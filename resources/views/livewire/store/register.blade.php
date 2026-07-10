<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use App\Models\Customer;
use App\Models\User;
use App\Services\UsernameGeneratorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

new #[Layout('layouts.app', ['meta_robots' => 'noindex, nofollow'])] #[Title('Registrarse - Abastos Los Trinis')] class extends Component {
    #[Validate('required|min:2|max:100')]
    public string $nombre = '';

    #[Validate('required|min:2|max:100')]
    public string $apellido = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    #[Validate('required')]
    public string $country_code = '58';

    #[Validate('required|min:6|max:30')]
    public string $telefono = '';

    #[Validate('required|min:8|same:password_confirmation')]
    public string $password = '';

    public string $password_confirmation = '';

    public function register(): void
    {
        $this->validate();

        // Format phone: remove leading zero if any, then prepend country code
        $phoneFormatted = null;
        if ($this->telefono) {
            $cleanPhone = ltrim(preg_replace('/[^0-9]/', '', $this->telefono), '0');
            $phoneFormatted = $this->country_code . $cleanPhone;
        }

        $otp = sprintf('%06d', mt_rand(100000, 999999));

        DB::beginTransaction();
        try {
            // Find Clientes group
            $group = \App\Models\Group::where('name', 'Clientes')->first();
            $groupId = $group ? $group->id : null;

            // Generate username
            $usernameGenerator = app(UsernameGeneratorService::class);
            $fullName = $this->nombre . ' ' . $this->apellido;
            $username = $usernameGenerator->generate($fullName);
            
            // Create user
            $user = User::create([
                'name' => $fullName,
                'username' => $username,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'telefono' => $phoneFormatted,
                'whatsapp_otp' => $otp,
                'empresa_id' => 1,
                'group_id' => $groupId,
            ]);

            // Assign role 'cliente'
            $user->assignRole('cliente');

            // Create customer record
            Customer::create([
                'user_id' => $user->id,
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'email' => $this->email,
                'telefono' => $phoneFormatted,
                'activo' => true,
                'empresa_id' => 1,
                'sucursal_id' => 1,
            ]);

            DB::commit();

            try {
                $whatsappService = app(\App\Services\WhatsAppService::class);
                $message = "Hola {$this->nombre}, tu codigo de verificacion para Abastos Los Trinis es: *{$otp}*. No lo compartas con nadie.";
                $whatsappService->sendMessage($phoneFormatted, $message, true);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error enviando OTP via WhatsApp: " . $e->getMessage());
            }

            \Illuminate\Support\Facades\Log::info("Enviando OTP {$otp} al WhatsApp {$phoneFormatted}");

            Auth::login($user);
            session()->regenerate();

            $this->redirect('/verificar-telefono', navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Error en el registro de usuario/cliente: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->addError('email', 'Ocurrió un error al crear tu cuenta. Intenta de nuevo.');
        }
    }
};
?>

<div class="min-h-[60vh] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-lg">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-zinc-900">Crear Cuenta</h1>
            <p class="mt-2 text-zinc-500">Regístrate para disfrutar de una mejor experiencia de compra.</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-8">
            <form wire:submit="register" class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input wire:model="nombre" label="Nombre *" placeholder="Juan" autofocus />
                    <flux:input wire:model="apellido" label="Apellido *" placeholder="Pérez" />
                </div>

                <flux:input wire:model="email" type="email" label="Correo electrónico *" placeholder="tu@email.com" />
                
                <div class="flex gap-3">
                    <div class="w-1/3">
                        <flux:select wire:model="country_code" label="País">
                            <option value="58">Venezuela</option>
                        </flux:select>
                    </div>
                    <div class="w-2/3">
                        <flux:input wire:model="telefono" type="tel" label="Teléfono *" placeholder="4241234567" required />
                    </div>
                </div>

                <flux:input wire:model="password" type="password" label="Contraseña *" placeholder="Mínimo 8 caracteres" />

                <flux:input wire:model="password_confirmation" type="password" label="Confirmar contraseña *" placeholder="Repite tu contraseña" />

                <flux:button type="submit" variant="primary" class="w-full">
                    Crear Cuenta
                </flux:button>
            </form>
        </div>

        <!-- Login Link -->
        <p class="mt-6 text-center text-sm text-zinc-500">
            ¿Ya tienes cuenta?
            <a href="/acceso" wire:navigate class="text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">Inicia sesión</a>
        </p>
    </div>
</div>