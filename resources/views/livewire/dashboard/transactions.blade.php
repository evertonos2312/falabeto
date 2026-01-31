<div class="mx-auto min-h-screen w-full max-w-6xl px-6 py-16">
    <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h1 class="text-3xl font-semibold text-white">Transações</h1>
            <p class="mt-2 text-sm text-slate-300">Controle entradas e saídas com segurança.</p>
        </div>
        <div class="flex items-center gap-3">
            @if ($canExport)
                <button class="rounded-full border border-emerald-400/40 px-4 py-2 text-xs text-emerald-200">Exportar CSV</button>
            @endif
            <a href="{{ route('dashboard') }}" class="text-sm text-slate-300 hover:text-white">Dashboard</a>
        </div>
    </div>

    <div class="mt-8 grid gap-6 md:grid-cols-[1fr_1fr]">
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">{{ $editingId ? 'Editar' : 'Nova' }} transação</h2>
            <form wire:submit.prevent="save" class="mt-4 space-y-4 text-sm text-slate-300">
                <div>
                    <label>Tipo</label>
                    <select wire:model.defer="type" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-white">
                        <option value="expense">Despesa</option>
                        <option value="income">Receita</option>
                    </select>
                    @error('type') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>Valor (centavos)</label>
                    <input type="number" wire:model.defer="amount_cents" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-white">
                    @error('amount_cents') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>Data/hora</label>
                    <input type="datetime-local" wire:model.defer="occurred_at" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-white">
                    @error('occurred_at') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>Categoria</label>
                    <input type="text" wire:model.defer="category" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-white">
                </div>
                <div>
                    <label>Descrição</label>
                    <input type="text" wire:model.defer="description" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-white">
                    @error('description') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>Notas</label>
                    <textarea wire:model.defer="notes" rows="3" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-white"></textarea>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded-full bg-emerald-500 px-4 py-2 text-xs font-semibold text-slate-950">
                        Salvar
                    </button>
                    @if ($editingId)
                        <button type="button" wire:click="cancelEdit" class="rounded-full border border-white/10 px-4 py-2 text-xs text-slate-300">
                            Cancelar
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <div class="rounded-3xl border border-white/10 bg-slate-900/60 p-6">
            <h2 class="text-lg font-semibold text-white">Últimas transações</h2>
            <div class="mt-4 space-y-3 text-sm text-slate-300">
                @forelse ($transactions as $transaction)
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-white">{{ $transaction['description'] }}</span>
                            <span class="{{ $transaction['type'] === 'income' ? 'text-emerald-200' : 'text-rose-200' }}">
                                R$ {{ number_format($transaction['amount_cents'] / 100, 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="mt-2 text-xs text-slate-400">
                            {{ $transaction['occurred_at'] }} · {{ $transaction['category'] ?? 'Sem categoria' }}
                        </div>
                        <div class="mt-3 flex items-center gap-3">
                            <button wire:click="edit('{{ $transaction['id'] }}')" class="text-xs text-emerald-200">Editar</button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Nenhuma transação registrada.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
