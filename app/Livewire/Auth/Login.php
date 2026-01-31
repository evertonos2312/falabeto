<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $login = '';
    public string $password = '';

    public function authenticate(): void
    {
        $this->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = str_contains($this->login, '@') ? 'email' : 'phone_e164';

        $credentials = [
            $field => $this->login,
            'password' => $this->password,
        ];

        if (Auth::guard('client')->attempt($credentials)) {
            if (request()->hasSession()) {
                request()->session()->regenerate();
            }

            $this->redirectRoute('whatsapp.verify');
            return;
        }

        $this->addError('login', 'Credenciais inválidas.');
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.app', ['title' => 'Entrar']);
    }
}
