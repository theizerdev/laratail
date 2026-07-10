<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

new #[Layout('layouts.app')] #[Title('Verificar Teléfono - Abastos Los Trinis')] class extends Component {
    #[Validate('required|string|size:6')]
    public string $otp = '';

    public function mount()
    {
        if (!Auth::check()) {
            return $this->redirect('/acceso', navigate: true);
        }

        if (Auth::user()->phone_verified_at !== null) {
            return $this->redirect('/mi-cuenta', navigate: true);
        }
    }

    public function verify(): void
    {
        $this->validate();

        $user = Auth::user();

        if ($user->whatsapp_otp === $this->otp) {
            $user->update([
                'phone_verified_at' => now(),
                'whatsapp_otp' => null,
            ]);

            session()->flash('status', 'Teléfono verificado correctamente.');
            $this->redirect('/mi-cuenta', navigate: true);
        } else {
            $this->addError('otp', 'El código ingresado es incorrecto.');
        }
    }

    public function resend(): void
    {
        $user = Auth::user();
        
        $newOtp = sprintf('%06d', mt_rand(100000, 999999));
        
        $user->update([
            'whatsapp_otp' => $newOtp
        ]);

        try {
            $whatsappService = new \App\Services\WhatsAppService($user->empresa_id);
            $message = "Tu nuevo codigo de verificacion es: *{$newOtp}*. No lo compartas con nadie.";
            $whatsappService->sendMessage($user->telefono, $message, true);
        } catch (\Throwable $e) {
            Log::error("Error enviando OTP via WhatsApp: " . $e->getMessage());
        }

        Log::info("Re-enviando OTP {$newOtp} al WhatsApp {$user->telefono}");

        session()->flash('status', 'Un nuevo código ha sido enviado a tu WhatsApp.');
    }
};
?>

<div class="min-h-[60vh] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-zinc-900">Verifica tu WhatsApp</h1>
            <p class="mt-2 text-zinc-500">Hemos enviado un código de 6 dígitos al número terminando en ****{{ substr(Auth::user()->telefono ?? '', -4) }}</p>
        </div>

        @if (session('status'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-600 rounded-xl p-4 text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        <!-- Form -->
        <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-8">
            <form wire:submit="verify" class="space-y-5">
                <flux:input 
                    wire:model="otp" 
                    label="Código de Verificación *" 
                    placeholder="123456" 
                    maxlength="6"
                    autofocus 
                />

                <flux:button type="submit" variant="primary" class="w-full">
                    Verificar Código
                </flux:button>
            </form>

            <div class="mt-6 text-center">
                <button wire:click="resend" type="button" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                    ¿No recibiste el código? Enviar de nuevo
                </button>
            </div>
        </div>
    </div>
</div>