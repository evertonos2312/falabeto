<div class="mx-auto min-h-screen w-full max-w-4xl px-6 py-16">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-semibold text-white">Assinatura</h1>
        <a href="{{ route('plans') }}" class="text-sm text-emerald-200 hover:text-emerald-100">Trocar plano</a>
    </div>

    @if (! $subscription)
        <div class="mt-8 rounded-3xl border border-white/10 bg-white/5 p-8 text-sm text-slate-300">
            Você ainda não possui assinatura ativa.
        </div>
    @else
        <div class="mt-8 grid gap-6 md:grid-cols-2">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <p class="text-sm text-emerald-200">Plano</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">{{ $subscription->plan->name }}</h2>
                <p class="mt-2 text-sm text-slate-300">Status: {{ ucfirst($subscription->status) }}</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6 text-sm text-slate-300">
                <div class="flex items-center justify-between">
                    <span>Início</span>
                    <span>{{ optional($subscription->started_at)->format('d/m/Y') }}</span>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <span>Trial até</span>
                    <span>{{ optional($subscription->trial_ends_at)->format('d/m/Y') ?? '—' }}</span>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <span>Próxima renovação</span>
                    <span>{{ optional($subscription->next_renewal_at)->format('d/m/Y') ?? '—' }}</span>
                </div>
            </div>
        </div>

        <div class="mt-8 rounded-3xl border border-white/10 bg-slate-900/60 p-6">
            <h3 class="text-lg font-semibold text-white">Ações</h3>
            <p class="mt-2 text-sm text-slate-300">Você pode cancelar a renovação a qualquer momento.</p>
            <button wire:click="cancel" class="mt-4 rounded-full border border-rose-400/40 px-5 py-2 text-sm text-rose-200 hover:border-rose-300">
                Cancelar assinatura
            </button>
        </div>
    @endif
</div>
