<x-filament-breezy::auth-card action="register">
    <div class="w-full flex justify-center mb-2">
        <x-filament::brand />
    </div>

    <div>
        <h2 class="font-bold tracking-tight text-center text-black dark:text-white text-3xl bg-gradient-to-r from-slate-950 to-indigo-950 dark:from-white dark:to-slate-300 bg-clip-text text-transparent">
            {{ __('filament-breezy::default.registration.heading') }}
        </h2>
       
    </div>

    <div class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" class="w-full py-3 bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-100 dark:text-zinc-950 font-medium rounded-xl shadow-lg transition-all duration-300">
            {{ __('filament-breezy::default.registration.submit.label') }}
        </x-filament::button>
        @if(config("filament-breezy.enable_registration"))
        <p class="mt-2 text-sm text-center text-slate-500 dark:text-slate-400">
            {{ __('filament-breezy::default.or') }}
            <a class="text-primary-600 hover:text-primary-500 font-medium" href="{{route('filament.auth.login')}}">
                {{ strtolower(__('filament::login.heading')) }}
            </a>
        </p>
        @endif
    </div>
</x-filament-breezy::auth-card>
