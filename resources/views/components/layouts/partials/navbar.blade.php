{{-- Admin Top Navbar --}}
<nav class="sticky top-0 z-20 bg-gray-50">
    <div class="flex h-[72px] items-center justify-between px-6 lg:px-8">
        {{-- Left: Mobile menu toggle + page title --}}
        <div class="flex items-center gap-3">
            <button
                onclick="toggleSidebar()"
                class="rounded-lg p-2 text-gray-400 transition hover:bg-white hover:text-gray-700 lg:hidden"
            >
                <iconify-icon icon="heroicons:bars-3" class="h-5 w-5"></iconify-icon>
            </button>
            <h1 class="text-xl font-bold text-gray-900">{{ $pageTitle ?? 'Dashboard' }}</h1>
        </div>

        {{-- Right: Actions --}}
        <div class="flex items-center gap-1">
            {{-- Notification bell --}}
            <button class="relative rounded-xl p-2.5 text-gray-400 transition hover:bg-white hover:text-gray-600">
                <iconify-icon icon="heroicons:bell" class="h-5 w-5"></iconify-icon>
                <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-gray-50"></span>
            </button>

            {{-- Messages --}}
            <button class="relative rounded-xl p-2.5 text-gray-400 transition hover:bg-white hover:text-gray-600">
                <iconify-icon icon="heroicons:chat-bubble-left-right" class="h-5 w-5"></iconify-icon>
            </button>

            {{-- Divider --}}
            <div class="mx-2 h-6 w-px bg-gray-200"></div>

            {{-- User avatar --}}
            <flux:dropdown position="bottom" align="end">
                <button class="flex items-center gap-2.5 rounded-xl p-1.5 transition hover:bg-white">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">
                        {{ Auth::user()->initials() }}
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-sm font-semibold text-gray-900">{{ explode(' ', Auth::user()->name)[0] }}</p>
                    </div>
                    <iconify-icon icon="heroicons:chevron-down" class="h-4 w-4 text-gray-400"></iconify-icon>
                </button>

                <flux:menu>
                    <flux:menu.heading>
                        <div class="px-1">
                            <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                        </div>
                    </flux:menu.heading>
                    <flux:menu.separator />
                    <flux:menu.item icon="user" href="{{ route('admin.profile') }}" wire:navigate>My Profile</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item
                        icon="arrow-right-end-on-rectangle"
                        href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    >
                        Log out
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>
</nav>
