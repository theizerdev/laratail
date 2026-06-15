<div>
    {{-- Back to login --}}
    <div class="mb-8">
        <a
            href="{{ route('login') }}"
            class="inline-flex items-center gap-1.5 text-sm text-zinc-500 hover:text-zinc-700 transition"
            wire:navigate
        >
            <iconify-icon icon="heroicons:arrow-left" class="h-4 w-4"></iconify-icon>
            Back to login
        </a>
    </div>

    {{-- Heading --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Forgot your password?</h1>
        <p class="mt-2 text-sm text-zinc-500">
            No problem. Enter your email address below and we'll send you a link to reset your password.
        </p>
    </div>

    {{-- Status message --}}
    @if ($status)
        <div class="mb-6 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
            {{ $status }}
        </div>
    @endif

    {{-- Form --}}
    <form wire:submit="sendResetLink" class="space-y-5">
        <flux:input
            wire:model="email"
            label="Email"
            type="email"
            placeholder="email@example.com"
        />

        <flux:button type="submit" variant="primary" class="w-full">
            Send reset link
        </flux:button>
    </form>
</div>
