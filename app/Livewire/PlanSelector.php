<?php

namespace App\Livewire;

use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PlanSelector extends Component
{
    public function mount(): void
    {
        $client = Auth::guard('client')->user();

        if (! $client?->whatsapp_verified_at) {
            $this->redirectRoute('whatsapp.verify');
        }
    }

    public function render()
    {
        return view('livewire.plan-selector', [
            'plans' => Plan::query()
                ->where('is_active', true)
                ->orderBy('price_cents')
                ->get(),
        ])->layout('layouts.app', ['title' => 'Escolha seu plano']);
    }
}
