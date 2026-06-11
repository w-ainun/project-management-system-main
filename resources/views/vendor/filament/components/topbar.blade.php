@props([
    'breadcrumbs' => [],
])

<header
    {{
        $attributes->class([
            'filament-main-topbar sticky top-0 z-10 flex h-16 w-full shrink-0 items-center border-b bg-white',
            'dark:border-gray-700 dark:bg-gray-800' => config('filament.dark_mode'),
        ])
    }}
>
    <div class="flex w-full items-center px-2 sm:px-4 md:px-6 lg:px-8">
        <button
            x-cloak
            x-data="{}"
            x-bind:aria-label="
                $store.sidebar.isOpen
                    ? '{{ __('filament::layout.buttons.sidebar.collapse.label') }}'
                    : '{{ __('filament::layout.buttons.sidebar.expand.label') }}'
            "
            x-on:click="$store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()"
            @class([
                'filament-sidebar-open-button flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-primary-500 outline-none hover:bg-gray-500/5 focus:bg-primary-500/10',
                'lg:mr-4 rtl:lg:ml-4 rtl:lg:mr-0' => config('filament.layout.sidebar.is_collapsible_on_desktop'),
                'lg:hidden' => ! (config('filament.layout.sidebar.is_collapsible_on_desktop') && (config('filament.layout.sidebar.collapsed_width') === 0)),
            ])
        >
            <svg
                class="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                />
            </svg>
        </button>

        <div class="flex flex-1 items-center justify-between">
            <x-filament::layouts.app.topbar.breadcrumbs
                :breadcrumbs="$breadcrumbs"
            />

            <div class="flex items-center gap-4 ml-auto">
                @livewire('filament.core.global-search')

                <!-- Dark/Light Theme Toggle Button -->
                @if (config('filament.dark_mode'))
                    <div x-data="{
                        theme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
                        toggleTheme(event) {
                            const newTheme = this.theme === 'dark' ? 'light' : 'dark';
                            
                            // Check for View Transition support or user motion preference
                            if (!document.startViewTransition || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                                this.theme = newTheme;
                                document.documentElement.classList.toggle('dark', newTheme === 'dark');
                                localStorage.setItem('theme', newTheme);
                                window.dispatchEvent(new CustomEvent('dark-mode-toggled', { detail: newTheme }));
                                return;
                            }

                            // Capture mouse position for origin
                            const x = event ? event.clientX : window.innerWidth / 2;
                            const y = event ? event.clientY : window.innerHeight / 2;
                            const endRadius = Math.hypot(
                                Math.max(x, window.innerWidth - x),
                                Math.max(y, window.innerHeight - y)
                            );

                            document.documentElement.style.setProperty('--x', `${x}px`);
                            document.documentElement.style.setProperty('--y', `${y}px`);
                            document.documentElement.style.setProperty('--r', `${endRadius}px`);

                            document.startViewTransition(() => {
                                this.theme = newTheme;
                                document.documentElement.classList.toggle('dark', newTheme === 'dark');
                                localStorage.setItem('theme', newTheme);
                                window.dispatchEvent(new CustomEvent('dark-mode-toggled', { detail: newTheme }));
                            });
                        }
                    }" class="flex items-center">
                        <button 
                            type="button" 
                            @click="toggleTheme($event)" 
                            class="flex items-center justify-center rounded-xl text-gray-500 hover:bg-gray-500/5 focus:bg-primary-500/10 dark:text-gray-400 dark:hover:bg-gray-500/5 dark:focus:bg-gray-500/10 h-10 w-10 shrink-0 transition-colors duration-200"
                            x-tooltip.raw="Ganti Tema"
                        >
                            <!-- Sun icon (shows in dark mode) -->
                            <svg x-show="theme === 'dark'" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21M4.958 4.958l1.591 1.591m10.899 10.899l1.591 1.591M3 12h2.25m13.5 0H21M4.958 19.042l1.591-1.591m10.899-10.899l1.591-1.591M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                            <!-- Moon icon (shows in light mode) -->
                            <svg x-show="theme === 'light'" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                        </button>
                    </div>
                @endif

                @livewire('filament.core.notifications')

                <x-filament::layouts.app.topbar.user-menu />
            </div>
        </div>
    </div>
</header>
