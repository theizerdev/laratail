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
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Reset your password</h1>
        <p class="mt-2 text-sm text-zinc-500">
            Enter your new password below to regain access to your account.
        </p>
    </div>

    {{-- Form --}}
    <form wire:submit="resetPassword" class="space-y-5">
        <flux:input
            wire:model="email"
            label="Email"
            type="email"
            placeholder="email@example.com"
        />

        <flux:input
            wire:model="password"
            label="New password"
            type="password"
            placeholder="Enter new password"
        />

        <flux:input
            wire:model="password_confirmation"
            label="Confirm password"
            type="password"
            placeholder="Confirm new password"
        />

        <flux:button type="submit" variant="primary" class="w-full">
            Reset password
        </flux:button>
    </form>
</div>
