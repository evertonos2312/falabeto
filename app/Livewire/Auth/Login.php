<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use App\Support\OnboardingResolver;
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
        ], [
            'login.required' => 'O campo email ou celular é obrigatório.',
            'login.string' => 'O campo email ou celular é inválido.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.string' => 'O campo senha é inválido.',
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

            $this->redirect(OnboardingResolver::nextRouteFor(Auth::guard('client')->user()));
            return;
        }

        $this->addError('login', 'Email/celular ou senha inválidos.');
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.app', ['title' => 'Entrar']);
    }
}
