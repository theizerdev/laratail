<div>
    {{-- Reports Section --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        {{-- Received Card --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Received</p>
                <span class="text-[11px] font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">This month</span>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900">$67,129</p>
            <div class="mt-4">
                <button class="w-full rounded-xl bg-emerald-500 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:bg-emerald-600">
                    Transfer
                </button>
            </div>
        </div>

        {{-- Expenses Card --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Expenses</p>
                <span class="text-[11px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">This month</span>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900">$31,191</p>
            <div class="mt-4">
                <button class="w-full rounded-xl bg-gray-100 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-200">
                    Receive
                </button>
            </div>
        </div>

        {{-- Total Transaction Card --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-400">Total Transaction</p>
                <button class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <iconify-icon icon="heroicons:bars-3" class="h-4 w-4"></iconify-icon>
                </button>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-900">517<span class="text-lg text-gray-400">+</span></p>
            <p class="mt-2 text-xs text-gray-400">Across all platforms</p>
            <div class="mt-3 flex -space-x-1">
                <span class="inline-block h-2 rounded-full bg-emerald-400" style="width: 40%"></span>
                <span class="inline-block h-2 rounded-full bg-blue-400" style="width: 25%"></span>
                <span class="inline-block h-2 rounded-full bg-yellow-400" style="width: 35%"></span>
            </div>
        </div>

        {{-- Incoming Donut Chart Card --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-400">Incoming</p>
            <div class="mt-3 flex items-center gap-5">
                {{-- Donut chart --}}
                <div class="relative h-24 w-24 shrink-0">
                    <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e5e7eb" stroke-width="3"></circle>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#10b981" stroke-width="3" stroke-dasharray="38 62" stroke-dashoffset="0" stroke-linecap="round"></circle>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#1e3a5f" stroke-width="3" stroke-dasharray="28 72" stroke-dashoffset="-38" stroke-linecap="round"></circle>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#3b82f6" stroke-width="3" stroke-dasharray="22 78" stroke-dashoffset="-66" stroke-linecap="round"></circle>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#eab308" stroke-width="3" stroke-dasharray="12 88" stroke-dashoffset="-88" stroke-linecap="round"></circle>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-xs font-bold text-gray-900">$30.7k</span>
                    </div>
                </div>
                {{-- Legend --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-gray-500">Fiverr</span>
                        <span class="ml-auto font-semibold text-gray-700">$12,548</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#1e3a5f]"></span>
                        <span class="text-gray-500">Upwork</span>
                        <span class="ml-auto font-semibold text-gray-700">$9,163</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                        <span class="text-gray-500">Razorpay</span>
                        <span class="ml-auto font-semibold text-gray-700">$7,415</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                        <span class="text-gray-500">Freelancer</span>
                        <span class="ml-auto font-semibold text-gray-700">$1,596</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Connected Accounts Section --}}
    <div class="mt-8">
        <h2 class="mb-4 text-base font-bold text-gray-900">Connected Accounts</h2>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            {{-- Account Card 1 (dark green) --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-700 to-emerald-900 p-6 text-white shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-sm font-bold">F</div>
                        <span class="text-sm font-semibold">Flux</span>
                    </div>
                    <iconify-icon icon="heroicons:signal" class="h-5 w-5 text-white/60"></iconify-icon>
                </div>
                <div class="mt-6">
                    <p class="text-xs text-white/60">Account Holder</p>
                    <p class="mt-0.5 text-sm font-semibold">{{ Auth::user()->name }}</p>
                </div>
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/5"></div>
            </div>

            {{-- Account Card 2 (bright green) --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 p-6 text-white shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-sm font-bold">F</div>
                        <span class="text-sm font-semibold">Flux</span>
                    </div>
                    <iconify-icon icon="heroicons:signal" class="h-5 w-5 text-white/60"></iconify-icon>
                </div>
                <div class="mt-6">
                    <p class="text-xs text-white/60">Account Holder</p>
                    <p class="mt-0.5 text-sm font-semibold">{{ Auth::user()->name }}</p>
                </div>
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
            </div>

            {{-- Add Account Card --}}
            <div class="flex cursor-pointer items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 p-6 transition hover:border-emerald-300 hover:bg-emerald-50/50">
                <div class="text-center">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-gray-100">
                        <iconify-icon icon="heroicons:plus" class="h-5 w-5 text-gray-400"></iconify-icon>
                    </div>
                    <p class="mt-2 text-sm font-medium text-gray-500">Add Account</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Section: Chart + Exchange Rate --}}
    <div class="mt-8 grid grid-cols-1 gap-5 xl:grid-cols-5">
        {{-- Transaction Report (line chart) --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm xl:col-span-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900">Transaction Report</h2>
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1.5 text-xs text-gray-500">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Income
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-gray-500">
                        <span class="h-2 w-2 rounded-full bg-emerald-200"></span> Expense
                    </span>
                </div>
            </div>

            {{-- Simple SVG line chart --}}
            <div class="mt-6 relative h-52">
                <svg class="h-full w-full" viewBox="0 0 600 200" preserveAspectRatio="none">
                    {{-- Grid lines --}}
                    <line x1="0" y1="50" x2="600" y2="50" stroke="#f3f4f6" stroke-width="1"/>
                    <line x1="0" y1="100" x2="600" y2="100" stroke="#f3f4f6" stroke-width="1"/>
                    <line x1="0" y1="150" x2="600" y2="150" stroke="#f3f4f6" stroke-width="1"/>
                    {{-- Area fill --}}
                    <path d="M0,160 C50,140 100,80 150,100 C200,120 250,60 300,50 C350,40 400,90 450,70 C500,50 550,30 600,40 L600,200 L0,200 Z" fill="url(#greenGradient)" opacity="0.3"/>
                    {{-- Line --}}
                    <path d="M0,160 C50,140 100,80 150,100 C200,120 250,60 300,50 C350,40 400,90 450,70 C500,50 550,30 600,40" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round"/>
                    <defs>
                        <linearGradient id="greenGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#10b981" stop-opacity="0.4"/>
                            <stop offset="100%" stop-color="#10b981" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                </svg>
                {{-- X-axis labels --}}
                <div class="mt-2 flex justify-between text-[11px] text-gray-400">
                    <span>1</span><span>5</span><span>10</span><span>15</span><span>20</span><span>25</span><span>30</span>
                </div>
            </div>
        </div>

        {{-- Exchange Rate --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900">Exchange Rate</h2>
                <a href="#" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">View All &rsaquo;</a>
            </div>

            <div class="mt-5 space-y-4">
                {{-- Dollar --}}
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-lg">
                        &#127482;&#127480;
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Dollar</p>
                        <p class="text-xs text-gray-400">USD</p>
                    </div>
                    <p class="text-sm font-bold text-gray-900">$1 = &#8377;84.14</p>
                </div>

                {{-- Pound --}}
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-lg">
                        &#127468;&#127463;
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Pound</p>
                        <p class="text-xs text-gray-400">GBP</p>
                    </div>
                    <p class="text-sm font-bold text-gray-900">$1 = &#8377;84.14</p>
                </div>

                {{-- Yuan --}}
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-yellow-50 text-lg">
                        &#127464;&#127475;
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Yuan</p>
                        <p class="text-xs text-gray-400">CNY</p>
                    </div>
                    <p class="text-sm font-bold text-gray-900">$1 = &#8377;84.14</p>
                </div>

                {{-- Euro --}}
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-lg">
                        &#127466;&#127482;
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Euro</p>
                        <p class="text-xs text-gray-400">EUR</p>
                    </div>
                    <p class="text-sm font-bold text-gray-900">$1 = &#8377;84.14</p>
                </div>
            </div>
        </div>
    </div>
</div>
