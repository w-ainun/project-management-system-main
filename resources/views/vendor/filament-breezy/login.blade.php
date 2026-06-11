<x-filament-breezy::auth-card action="authenticate">

    <div class="w-full flex justify-center mb-2">
        <x-filament::brand />
    </div>

    <div>
        <h2 class="font-bold tracking-tight text-center text-black dark:text-white text-3xl bg-gradient-to-r from-slate-950 to-indigo-950 dark:from-white dark:to-slate-300 bg-clip-text text-transparent">
            {{ __('filament::login.heading') }}
        </h2>
    </div>

    <div class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" class="w-full py-3 bg-zinc-900 hover:bg-zinc-800 text-zinc-900 dark:bg-white dark:hover:bg-zinc-100 dark:text-zinc-950 font-medium rounded-xl shadow-lg transition-all duration-300">
            {{ __('filament::login.buttons.submit.label') }}
        </x-filament::button>
    </div>

    @if(config("filament-breezy.enable_registration"))
        <div class="text-center mt-4 pt-4 border-t border-gray-100 dark:border-gray-800/50">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ __('Belum punya akun?') }}
                <a class="text-primary-600 hover:text-primary-500 font-semibold transition-colors" href="{{route(config('filament-breezy.route_group_prefix').'register')}}">
                    {{ __('Daftar sekarang') }}
                </a>
            </p>
        </div>
    @endif

</x-filament-breezy::auth-card>
