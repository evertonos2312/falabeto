<div class="mx-auto min-h-screen w-full max-w-6xl px-6 py-16">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-white">Dívidas</h1>
            <p class="mt-2 text-sm text-slate-300">Acompanhe contas a pagar com datas e status.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-sm text-slate-300 hover:text-white">Dashboard</a>
    </div>

    <div class="mt-8 grid gap-6 md:grid-cols-[1fr_1fr]">
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">{{ $editingId ? 'Editar' : 'Nova' }} dívida</h2>
            <form wire:submit.prevent="save" class="mt-4 space-y-4 text-sm text-slate-300">
                <div>
                    <label>Credor</label>
                    <input type="text" wire:model.defer="creditor_name" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-white">
                    @error('creditor_name') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>Valor (centavos)</label>
                    <input type="number" wire:model.defer="amount_cents" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-white">
                    @error('amount_cents') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>Vencimento</label>
                    <input type="date" wire:model.defer="due_date" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-white">
                    @error('due_date') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label>Status</label>
                    <select wire:model.defer="status" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-white">
                        <option value="pending">Pendente</option>
                        <option value="paid">Pago</option>
                        <option value="overdue">Em atraso</option>
                    </select>
                    @error('status') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
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
            <h2 class="text-lg font-semibold text-white">Últimas dívidas</h2>
            <div class="mt-4 space-y-3 text-sm text-slate-300">
                @forelse ($debts as $debt)
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-white">{{ $debt['creditor_name'] }}</span>
                            <span class="text-rose-200">R$ {{ number_format($debt['amount_cents'] / 100, 2, ',', '.') }}</span>
                        </div>
                        <div class="mt-2 text-xs text-slate-400">
                            Vence em {{ $debt['due_date'] }} · {{ ucfirst($debt['status']) }}
                        </div>
                        <div class="mt-3 flex items-center gap-3">
                            <button wire:click="edit('{{ $debt['id'] }}')" class="text-xs text-emerald-200">Editar</button>
                            @if ($debt['status'] !== 'paid')
                                <button wire:click="markPaid('{{ $debt['id'] }}')" class="text-xs text-emerald-200">Marcar pago</button>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Nenhuma dívida registrada.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
