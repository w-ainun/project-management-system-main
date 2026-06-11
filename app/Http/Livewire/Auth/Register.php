<?php

namespace App\Http\Livewire\Auth;

use JeffGreco13\FilamentBreezy\Http\Livewire\Auth\Register as BreezyRegister;

class Register extends BreezyRegister
{
    protected function getFormSchema(): array
    {
        $schema = parent::getFormSchema();

        if (isset($schema[2])) {
            $schema[2]->view('partials.filament.password-input');
        }
        if (isset($schema[3])) {
            $schema[3]->view('partials.filament.password-input');
        }

        return $schema;
    }
}
