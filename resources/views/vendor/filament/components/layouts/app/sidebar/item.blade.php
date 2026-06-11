@props([
    'active' => false,
    'activeIcon',
    'badge' => null,
    'badgeColor' => null,
    'icon',
    'iconColor' => null,
    'shouldOpenUrlInNewTab' => false,
    'url',
])

<li
    @class([
        'filament-sidebar-item overflow-hidden',
        'filament-sidebar-item-active' => $active,
    ])
>
    <a
        href="{{ $url }}"
        {!! $shouldOpenUrlInNewTab ? 'target="_blank"' : '' !!}
        x-on:click="window.matchMedia(`(max-width: 1024px)`).matches && $store.sidebar.close()"
        @if (config('filament.layout.sidebar.is_collapsible_on_desktop'))
            x-data="{ tooltip: {} }"
            x-init="
                Alpine.effect(() => {
                    if (Alpine.store('sidebar').isOpen) {
                        tooltip = false
                    } else {
                        tooltip = {
                            content: {{ \Illuminate\Support\Js::from($slot->toHtml()) }},
                            theme: Alpine.store('theme') === 'light' ? 'dark' : 'light',
                            placement: document.dir === 'rtl' ? 'left' : 'right',
                        }
                    }
                })
            "
            x-tooltip.html="tooltip"
        @endif
        @class([
            'flex items-center justify-center gap-3 rounded-xl px-3 py-2.5 font-medium transition-all duration-200',
            'text-white/70 hover:bg-white/10 hover:text-white focus:bg-white/10' => ! $active,
            'bg-white/20 text-white shadow-lg shadow-indigo-900/30 backdrop-blur-sm' => $active,
        ])
    >
        <x-dynamic-component
            :component="($active && $activeIcon) ? $activeIcon : $icon"
            :class="
                \Illuminate\Support\Arr::toCssClasses([
                    'h-5 w-5 shrink-0 transition-colors duration-200',
                    'text-white' => $active,
                    'text-indigo-300' => ! $active,
                ])
            "
        />

        <div
            class="flex flex-1"
            @if (config('filament.layout.sidebar.is_collapsible_on_desktop'))
                x-show="$store.sidebar.isOpen"
            @endif
        >
            <span>
                {{ $slot }}
            </span>
        </div>

        @if (filled($badge))
            <x-filament::layouts.app.sidebar.badge
                :badge="$badge"
                :badge-color="$badgeColor"
                :active="$active"
            />
        @endif
    </a>
</li>
