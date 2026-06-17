<?php
use Livewire\Volt\Component;

new class extends Component {
    //
};
?>

<section class="relative overflow-hidden" id="newsletter-section">
    <!-- Background -->
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800"></div>
    <!-- Abstract Decorations -->
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-400/10 rounded-full blur-3xl"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 relative z-10">
        <div class="text-center max-w-3xl mx-auto">
            <!-- Trust indicators -->
            <div class="flex items-center justify-center gap-4 mb-8">
                <div class="flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full border border-white/15">
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <span class="text-white text-sm font-medium">4.9/5 Satisfacción</span>
                </div>
                <div class="hidden sm:flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full border border-white/15">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="text-white text-sm font-medium">Envío Gratuito</span>
                </div>
            </div>

            <h2 class="text-3xl md:text-5xl font-bold tracking-tight text-white mb-6 leading-tight">
                Únete a nuestro club<br class="hidden sm:block"> <span class="text-indigo-200">exclusivo</span>
            </h2>
            <p class="text-lg md:text-xl text-indigo-100/80 mb-10 max-w-xl mx-auto">
                Regístrate hoy y obtén un <strong class="text-white">15% de descuento</strong> en tu primera compra, además de acceso anticipado a nuevas colecciones y ofertas especiales.
            </p>
            <form class="flex flex-col sm:flex-row gap-3 justify-center max-w-lg mx-auto" onsubmit="event.preventDefault(); alert('¡Gracias por unirte!');">
                <input type="email" placeholder="Tu correo electrónico" class="flex-1 rounded-full px-6 py-4 text-zinc-900 border-0 focus:ring-4 focus:ring-indigo-300 focus:outline-none shadow-lg placeholder:text-zinc-400" required>
                <button type="submit" class="bg-zinc-900 text-white rounded-full px-8 py-4 font-semibold hover:bg-black transition-all shadow-lg hover:shadow-xl hover:scale-105 active:scale-95">
                    Obtener mi 15% off
                </button>
            </form>
            <p class="mt-4 text-xs text-indigo-200/50">Cancelar la suscripción en cualquier momento. No compartimos tu información.</p>
        </div>
    </div>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
    </style>
</section>
