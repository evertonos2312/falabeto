<?php

namespace App\Livewire\Dashboard;

use App\Models\Transaction;
use App\Support\FeatureGate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Transactions extends Component
{
    public array $transactions = [];
    public ?string $editingId = null;

    public string $type = 'expense';
    public int $amount_cents = 0;
    public string $occurred_at = '';
    public ?string $category = null;
    public string $description = '';
    public ?string $notes = null;

    public bool $canExport = false;

    public function mount(): void
    {
        $client = Auth::guard('client')->user();
        $this->canExport = FeatureGate::hasFeature($client, 'export_csv');
        $this->occurred_at = now()->format('Y-m-d\TH:i');
        $this->loadTransactions();
    }

    public function loadTransactions(): void
    {
        $client = Auth::guard('client')->user();
        $this->transactions = Transaction::query()
            ->where('client_id', $client->id)
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get()
            ->map(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount_cents' => $transaction->amount_cents,
                'occurred_at' => $transaction->occurred_at->format('Y-m-d H:i'),
                'category' => $transaction->category,
                'description' => $transaction->description,
                'notes' => $transaction->notes,
            ])
            ->all();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'type' => ['required', 'in:expense,income'],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'occurred_at' => ['required', 'date'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $client = Auth::guard('client')->user();

        $data = array_merge($validated, [
            'client_id' => $client->id,
        ]);

        if ($this->editingId) {
            $transaction = Transaction::query()
                ->where('client_id', $client->id)
                ->where('id', $this->editingId)
                ->firstOrFail();
            $transaction->update($data);
        } else {
            Transaction::create($data);
        }

        $this->resetForm();
        $this->loadTransactions();
    }

    public function edit(string $id): void
    {
        $client = Auth::guard('client')->user();
        $transaction = Transaction::query()
            ->where('client_id', $client->id)
            ->where('id', $id)
            ->firstOrFail();

        $this->editingId = $transaction->id;
        $this->type = $transaction->type;
        $this->amount_cents = $transaction->amount_cents;
        $this->occurred_at = $transaction->occurred_at->format('Y-m-d\TH:i');
        $this->category = $transaction->category;
        $this->description = $transaction->description ?? '';
        $this->notes = $transaction->notes;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->type = 'expense';
        $this->amount_cents = 0;
        $this->occurred_at = now()->format('Y-m-d\TH:i');
        $this->category = null;
        $this->description = '';
        $this->notes = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.dashboard.transactions')
            ->layout('layouts.app', ['title' => 'Transações']);
    }
}
