<?php

namespace App\Livewire\Dashboard;

use App\Models\Debt;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Debts extends Component
{
    public array $debts = [];
    public ?string $editingId = null;

    public string $creditor_name = '';
    public int $amount_cents = 0;
    public string $due_date = '';
    public string $status = 'pending';
    public ?string $notes = null;

    public function mount(): void
    {
        $this->due_date = now()->addWeek()->format('Y-m-d');
        $this->loadDebts();
    }

    public function loadDebts(): void
    {
        $client = Auth::guard('client')->user();
        $this->debts = Debt::query()
            ->where('client_id', $client->id)
            ->orderBy('due_date')
            ->limit(50)
            ->get()
            ->map(fn (Debt $debt) => [
                'id' => $debt->id,
                'creditor_name' => $debt->creditor_name,
                'amount_cents' => $debt->amount_cents,
                'due_date' => $debt->due_date->format('Y-m-d'),
                'status' => $debt->status,
                'notes' => $debt->notes,
            ])
            ->all();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'creditor_name' => ['required', 'string', 'max:255'],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'due_date' => ['required', 'date'],
            'status' => ['required', 'in:pending,paid,overdue'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $client = Auth::guard('client')->user();

        $data = array_merge($validated, [
            'client_id' => $client->id,
        ]);

        if ($this->editingId) {
            $debt = Debt::query()
                ->where('client_id', $client->id)
                ->where('id', $this->editingId)
                ->firstOrFail();
            $debt->update($data);
        } else {
            Debt::create($data);
        }

        $this->resetForm();
        $this->loadDebts();
    }

    public function edit(string $id): void
    {
        $client = Auth::guard('client')->user();
        $debt = Debt::query()
            ->where('client_id', $client->id)
            ->where('id', $id)
            ->firstOrFail();

        $this->editingId = $debt->id;
        $this->creditor_name = $debt->creditor_name ?? '';
        $this->amount_cents = $debt->amount_cents;
        $this->due_date = $debt->due_date->format('Y-m-d');
        $this->status = $debt->status;
        $this->notes = $debt->notes;
    }

    public function markPaid(string $id): void
    {
        $client = Auth::guard('client')->user();
        $debt = Debt::query()
            ->where('client_id', $client->id)
            ->where('id', $id)
            ->firstOrFail();

        $debt->update(['status' => 'paid']);

        $this->loadDebts();
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->creditor_name = '';
        $this->amount_cents = 0;
        $this->due_date = now()->addWeek()->format('Y-m-d');
        $this->status = 'pending';
        $this->notes = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.dashboard.debts')
            ->layout('layouts.app', ['title' => 'Dívidas']);
    }
}
