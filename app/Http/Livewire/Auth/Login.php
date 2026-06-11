<?php

namespace App\Http\Livewire\Auth;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use JeffGreco13\FilamentBreezy\Http\Livewire\Auth\Login as BreezyLogin;
use Illuminate\Support\HtmlString;

class Login extends BreezyLogin
{
    protected function getFormSchema(): array
    {
        $loginField = TextInput::make($this->loginColumn)
            ->required()
            ->autocomplete()
            ->columnSpan('full');

        if ($this->loginColumn === 'email') {
            $loginField->label(__('filament::login.fields.email.label'))
                ->email();
        } else {
            $loginField->label(__('filament-breezy::default.fields.login'));
        }

        return [
            $loginField,
            TextInput::make('password')
                ->label(__('filament::login.fields.password.label'))
                ->password()
                ->required()
                ->columnSpan('full')
                ->view('partials.filament.password-input'),
            Grid::make(2)
                ->schema([
                    Checkbox::make('remember')
                        ->label(__('filament::login.fields.remember.label'))
                        ->extraAttributes(['class' => 'mt-1']),
                    Placeholder::make('forgot_password')
                        ->label('')
                        ->content(new HtmlString('
                            <div class="text-right h-full flex items-center justify-end" style="margin-top: 2px;">
                                <a class="text-sm text-primary-600 hover:text-primary-500 font-medium transition-colors" href="' . route(config('filament-breezy.route_group_prefix').'password.request') . '">
                                    ' . __('filament-breezy::default.login.forgot_password_link') . '
                                </a>
                            </div>
                        ')),
                ])
                ->columnSpan('full'),
        ];
    }
}
