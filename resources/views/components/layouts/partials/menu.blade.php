{{-- Admin Sidebar Menu - Sector Based --}}
@php
    use App\Helpers\MenuHelper;
    $menuItems = MenuHelper::getSectorMenuItems();
@endphp

<div class="space-y-1">
    {{-- Dashboard (always visible) --}}
    <a
        href="{{ route('admin.dashboard') }}"
        wire:navigate
        @class([
            'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition',
            request()->routeIs('admin.dashboard')
                ? 'bg-emerald-50 text-emerald-700'
                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
        ])
    >
        <iconify-icon icon="heroicons:squares-2x2-solid" class="h-[18px] w-[18px]"></iconify-icon>
        <span class="flex-1">Dashboard</span>
        @if(request()->routeIs('admin.dashboard'))
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
        @endif
    </a>

    {{-- Render sectors from MenuHelper --}}
    @foreach($menuItems as $sectorKey => $sector)
        @php
            $isActive = MenuHelper::isSectorActive($sector['items'] ?? []);
            $sectorColor = MenuHelper::getSectorColor($sectorKey);
        @endphp

        {{-- Sector Header --}}
        <div class="pt-4 pb-2">
            <button
                type="button"
                class="sector-toggle flex w-full items-center gap-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400 transition hover:text-gray-600"
                data-sector="{{ $sectorKey }}"
            >
                <iconify-icon icon="{{ $sector['icon'] }}" class="h-4 w-4"></iconify-icon>
                <span class="flex-1 text-left">{{ $sector['label'] }}</span>
                <iconify-icon icon="heroicons:chevron-down" class="sector-chevron h-3 w-3 transition-transform" data-sector="{{ $sectorKey }}"></iconify-icon>
            </button>
        </div>

        {{-- Sector Items --}}
        <div class="sector-items space-y-0.5" id="sector-{{ $sectorKey }}">
            @foreach($sector['items'] ?? [] as $item)
                @php
                    $hasAccess = isset($item['permission']) 
                        ? MenuHelper::hasPermission($item['permission'])
                        : (isset($item['permissions']) ? MenuHelper::hasAnyPermission($item['permissions']) : true);
                    $itemActive = MenuHelper::isMenuItemActive($item);
                @endphp

                @if($hasAccess)
                    @if(isset($item['children']) && count($item['children']) > 0)
                        {{-- Parent with children (accordion style) --}}
                        <div class="menu-parent" x-data="{ expanded: {{ $itemActive ? 'true' : 'false' }} }">
                            <button
                                type="button"
                                @click="expanded = !expanded"
                                @class([
                                    'menu-parent-btn group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition',
                                    $itemActive 
                                        ? 'bg-emerald-50 text-emerald-700' 
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ])
                            >
                                <iconify-icon icon="{{ $item['icon'] }}" class="h-[18px] w-[18px]"></iconify-icon>
                                <span class="flex-1 text-left">{{ $item['label'] }}</span>
                                <iconify-icon 
                                    icon="heroicons:chevron-down" 
                                    class="h-3 w-3 transition-transform"
                                    :class="{ 'rotate-180': expanded }"
                                ></iconify-icon>
                            </button>

                            {{-- Children --}}
                            <div x-show="expanded" x-collapse class="ml-4 mt-1 space-y-0.5 border-l-2 border-gray-100 pl-3">
                                @foreach($item['children'] as $child)
                                    @php
                                        $childHasAccess = isset($child['permission']) ? MenuHelper::hasPermission($child['permission']) : true;
                                        $childActive = MenuHelper::isMenuItemActive($child);
                                    @endphp

                                    @if($childHasAccess)
                                        <a
                                            href="{{ isset($child['params']) ? route($child['route'], $child['params']) : route($child['route']) }}"
                                            wire:navigate
                                            @class([
                                                'group flex items-center gap-3 rounded-lg px-3 py-2 text-[12px] font-medium transition',
                                                $childActive 
                                                    ? 'bg-emerald-50 text-emerald-700' 
                                                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                            ])
                                        >
                                            <span class="flex-1">{{ $child['label'] }}</span>
                                            @if($childActive)
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            @endif
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @else
                        {{-- Single item (no children) --}}
                        <a
                            href="{{ route($item['route']) }}"
                            wire:navigate
                            @class([
                                'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-medium transition',
                                $itemActive 
                                    ? 'bg-emerald-50 text-emerald-700' 
                                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                            ])
                        >
                            <iconify-icon icon="{{ $item['icon'] }}" class="h-[18px] w-[18px]"></iconify-icon>
                            <span class="flex-1">{{ $item['label'] }}</span>
                            @if($itemActive)
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            @endif
                        </a>
                    @endif
                @endif
            @endforeach
        </div>
    @endforeach
</div>

{{-- Alpine.js for accordion functionality --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sector toggle
        document.querySelectorAll('.sector-toggle').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const sector = this.dataset.sector;
                const items = document.getElementById('sector-' + sector);
                const chevron = this.querySelector('.sector-chevron');
                
                if (items) {
                    if (items.classList.contains('hidden')) {
                        items.classList.remove('hidden');
                        chevron.classList.add('rotate-180');
                    } else {
                        items.classList.add('hidden');
                        chevron.classList.remove('rotate-180');
                    }
                }
            });
        });
    });
</script>