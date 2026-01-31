<?php

namespace App\Livewire;

use App\Models\Plan;
use Livewire\Component;

class LandingPlans extends Component
{
    public function render()
    {
        return view('livewire.landing-plans', [
            'plans' => Plan::query()
                ->where('is_active', true)
                ->orderBy('price_cents')
                ->with('items')
                ->get(),
        ]);
    }
}
