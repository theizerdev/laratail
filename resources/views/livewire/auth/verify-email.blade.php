<div>
    {{-- Heading --}}
    <div class="mb-8">
        <div class="mb-4 flex justify-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-50">
                <iconify-icon icon="heroicons:envelope" class="h-7 w-7 text-amber-500"></iconify-icon>
            </div>
        </div>
        <h1 class="text-center text-2xl font-bold tracking-tight text-zinc-900">Verify your email</h1>
        <p class="mt-2 text-center text-sm text-zinc-500">
            We've sent a verification link to
            <span class="font-medium text-zinc-700">{{ Auth::user()->email }}</span>.
            Please check your inbox and click the link to verify your email address.
        </p>
    </div>

    {{-- Status message --}}
    @if ($status === 'verification-link-sent')
        <div class="mb-6 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
            A new verification link has been sent to your email address.
        </div>
    @endif

    {{-- Actions --}}
    <div class="space-y-3">
        <flux:button
            wire:click="resendVerification"
            variant="primary"
            class="w-full"
        >
            Resend verification email
        </flux:button>

        <button
            wire:click="logout"
            class="w-full text-center text-sm text-zinc-500 hover:text-zinc-700 transition py-2"
        >
            Log out
        </button>
    </div>

    {{-- Help text --}}
    <div class="mt-8 rounded-lg bg-zinc-50 border border-zinc-100 p-4">
        <p class="text-xs text-zinc-500 leading-relaxed">
            If you didn't receive the email, check your spam folder or
            <button wire:click="resendVerification" class="font-medium text-zinc-700 underline underline-offset-2 hover:text-zinc-900">
                click here
            </button>
            to resend.
        </p>
    </div>
</div>
