{{-- Admin Sidebar Menu - Sector Based (Flux UI Refactored) --}}
@php
    use App\Helpers\MenuHelper;
    $menuItems = MenuHelper::getSectorMenuItems();
@endphp

<div class="space-y-6">
    {{-- Dashboard (always visible) --}}
    <flux:sidebar.item href="{{ route('admin.dashboard') }}" wire:navigate :current="request()->routeIs('admin.dashboard')">
        <x-slot name="icon">
            <iconify-icon icon="heroicons:squares-2x2-solid" class="h-[18px] w-[18px]"></iconify-icon>
        </x-slot>
        Dashboard
    </flux:sidebar.item>

    {{-- Render sectors from MenuHelper --}}
    @foreach($menuItems as $sectorKey => $sector)
        @php
            $isActive = MenuHelper::isSectorActive($sector['items'] ?? []);
        @endphp

        <div>
            {{-- Sector Header --}}
            <div class="px-3 pt-3 pb-2 flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                <iconify-icon icon="{{ $sector['icon'] }}" class="h-[14px] w-[14px]"></iconify-icon>
                <span>{{ $sector['label'] }}</span>
            </div>

            {{-- Sector Items --}}
            <div class="space-y-0.5">
                @foreach($sector['items'] ?? [] as $item)
                    @php
                        $hasAccess = isset($item['permission']) 
                            ? MenuHelper::hasPermission($item['permission'])
                            : (isset($item['permissions']) ? MenuHelper::hasAnyPermission($item['permissions']) : true);
                        $itemActive = MenuHelper::isMenuItemActive($item);
                    @endphp

                    @if($hasAccess)
                        @if(isset($item['children']) && count($item['children']) > 0)
                            {{-- Parent with children (collapsible group) --}}
                            <flux:sidebar.group heading="{{ $item['label'] }}" expandable :expanded="$itemActive">
                                <x-slot name="icon">
                                    <iconify-icon icon="{{ $item['icon'] }}" class="h-[18px] w-[18px]"></iconify-icon>
                                </x-slot>
                                @foreach($item['children'] as $child)
                                    @php
                                        $childHasAccess = isset($child['permission']) ? MenuHelper::hasPermission($child['permission']) : true;
                                        $childActive = MenuHelper::isMenuItemActive($child);
                                    @endphp

                                    @if($childHasAccess)
                                        <flux:sidebar.item 
                                            href="{{ isset($child['params']) ? route($child['route'], $child['params']) : route($child['route']) }}"
                                            wire:navigate
                                            :current="$childActive"
                                        >
                                            {{ $child['label'] }}
                                        </flux:sidebar.item>
                                    @endif
                                @endforeach
                            </flux:sidebar.group>
                        @else
                            {{-- Single item (no children) --}}
                            <flux:sidebar.item 
                                href="{{ route($item['route']) }}"
                                wire:navigate
                                :current="$itemActive"
                            >
                                <x-slot name="icon">
                                    <iconify-icon icon="{{ $item['icon'] }}" class="h-[18px] w-[18px]"></iconify-icon>
                                </x-slot>
                                {{ $item['label'] }}
                            </flux:sidebar.item>
                        @endif
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach
</div>
