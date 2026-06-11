@props(['action'])
<div class="flex items-center justify-center min-h-screen premium-auth-bg text-gray-900 dark:text-white transition-colors duration-500 overflow-hidden">
    <style>
        /* Custom premium background mesh & animations */
        .premium-auth-bg {
            background-color: #09090b; /* Elegant Black - Zinc 950 */
            background-image: 
                radial-gradient(at 0% 0%, rgba(63, 63, 70, 0.12) 0, transparent 60%), 
                radial-gradient(at 50% 0%, rgba(39, 39, 42, 0.15) 0, transparent 50%), 
                radial-gradient(at 100% 0%, rgba(82, 82, 91, 0.12) 0, transparent 60%),
                radial-gradient(at 0% 100%, rgba(82, 82, 91, 0.08) 0, transparent 60%),
                radial-gradient(at 100% 100%, rgba(63, 63, 70, 0.12) 0, transparent 60%);
            background-attachment: fixed;
        }

        .glass-card {
            background: rgba(24, 24, 27, 0.65); /* Elegant Dark Zinc */
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.04);
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.8), 0 0 50px -10px rgba(255, 255, 255, 0.01);
        }

        /* Light mode overrides */
        html:not(.dark) .premium-auth-bg, 
        .light .premium-auth-bg {
            background-color: #fafafa; /* Elegant Off-White */
            background-image: 
                radial-gradient(at 0% 0%, rgba(228, 228, 231, 0.5) 0, transparent 60%), 
                radial-gradient(at 50% 0%, rgba(244, 244, 245, 0.7) 0, transparent 50%), 
                radial-gradient(at 100% 0%, rgba(228, 228, 231, 0.4) 0, transparent 60%),
                radial-gradient(at 0% 100%, rgba(228, 228, 231, 0.3) 0, transparent 60%),
                radial-gradient(at 100% 100%, rgba(244, 244, 245, 0.5) 0, transparent 60%);
        }

        html:not(.dark) .glass-card,
        .light .glass-card {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(228, 228, 231, 0.6);
            box-shadow: 0 30px 60px -15px rgba(24, 24, 27, 0.03), 0 0 50px -10px rgba(0, 0, 0, 0.01);
        }

        /* Slide-in & Fade-in animations */
        @keyframes authFadeIn {
            from {
                opacity: 0;
                transform: translateY(16px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .auth-animated-card {
            animation: authFadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>

    <div class="px-6 py-12 max-w-{{ config('filament-breezy.auth_card_max_w') ?? 'md' }} w-full space-y-8 auth-animated-card">
        <form wire:submit.prevent="{{ $action }}" class="p-8 space-y-6 glass-card rounded-3xl relative transition-all duration-300">
            {{ $slot }}
        </form>

        {{ $this->modal }}
        <x-filament::footer />
    </div>

    @livewire('notifications')
</div>
