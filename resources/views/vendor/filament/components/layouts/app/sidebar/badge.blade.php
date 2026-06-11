@props([
    'active' => false,
    'badge' => null,
    'badgeColor' => null,
])

<span
    @if (config('filament.layout.sidebar.is_collapsible_on_desktop'))
        x-show="$store.sidebar.isOpen"
    @endif
    @class(array_merge(
        [
            'min-h-4 ml-auto inline-flex items-center justify-center whitespace-normal rounded-full px-2 py-0.5 text-xs font-semibold tracking-tight rtl:ml-0 rtl:mr-auto',
            'bg-white/30 text-white' => $active,
        ],
        match ($badgeColor) {
            'danger' => [
                'bg-red-400/20 text-red-300' => ! $active,
            ],
            'secondary' => [
                'bg-white/10 text-white/70' => ! $active,
            ],
            'success' => [
                'bg-emerald-400/20 text-emerald-300' => ! $active,
            ],
            'warning' => [
                'bg-amber-400/20 text-amber-300' => ! $active,
            ],
            'primary', null => [
                'bg-indigo-300/20 text-indigo-200' => ! $active,
            ],
            default => [
                $badgeColor => ! $active,
            ],
        },
    ))
>
    {{ $badge }}
</span>
