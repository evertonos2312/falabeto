<?php

namespace App\Livewire;

use App\Models\Subscription;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SubscriptionShow extends Component
{
    use AuthorizesRequests;

    public ?Subscription $subscription = null;

    public function mount(): void
    {
        $client = Auth::guard('client')->user();

        $this->subscription = Subscription::query()
            ->where('client_id', $client->id)
            ->latest('started_at')
            ->first();

        if ($this->subscription) {
            $this->authorize('view', $this->subscription);
        }
    }

    public function cancel(): void
    {
        if (! $this->subscription) {
            return;
        }

        $this->authorize('cancel', $this->subscription);

        $this->subscription->forceFill([
            'status' => 'canceled',
            'cancel_at_period_end' => true,
            'next_renewal_at' => null,
        ])->save();
    }

    public function render()
    {
        return view('livewire.subscription-show')
            ->layout('layouts.app', ['title' => 'Assinatura']);
    }
}
