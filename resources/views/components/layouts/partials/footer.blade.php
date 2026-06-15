{{-- Admin Footer --}}
<footer class="border-t border-zinc-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <p class="text-sm text-zinc-500">
                &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
            </p>
            <div class="flex items-center gap-4">
                <a href="#" class="text-sm text-zinc-500 transition hover:text-zinc-700">Privacy</a>
                <a href="#" class="text-sm text-zinc-500 transition hover:text-zinc-700">Terms</a>
                <span class="text-sm text-zinc-400">v1.0.0</span>
            </div>
        </div>
    </div>
</footer>
