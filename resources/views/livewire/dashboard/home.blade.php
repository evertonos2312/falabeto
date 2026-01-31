<div class="mx-auto min-h-screen w-full max-w-5xl px-6 py-16">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-semibold text-white">Resumo financeiro</h1>
        <a href="{{ route('subscription.show') }}" class="text-sm text-emerald-200 hover:text-emerald-100">Ver assinatura</a>
    </div>

    <div class="mt-8 grid gap-6 md:grid-cols-3">
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <p class="text-sm text-slate-300">Entradas</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-200">R$ {{ number_format($summary['income'] / 100, 2, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <p class="text-sm text-slate-300">Saídas</p>
            <p class="mt-2 text-2xl font-semibold text-rose-200">R$ {{ number_format($summary['expense'] / 100, 2, ',', '.') }}</p>
        </div>
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <p class="text-sm text-slate-300">Saldo</p>
            <p class="mt-2 text-2xl font-semibold text-white">R$ {{ number_format($summary['balance'] / 100, 2, ',', '.') }}</p>
        </div>
    </div>

    @if ($reportsLevel >= 1)
        <div class="mt-10 rounded-3xl border border-white/10 bg-slate-900/60 p-6">
            <h2 class="text-lg font-semibold text-white">Comparativo mês anterior</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-3 text-sm text-slate-300">
                <div>Entradas: R$ {{ number_format($previousSummary['income'] / 100, 2, ',', '.') }}</div>
                <div>Saídas: R$ {{ number_format($previousSummary['expense'] / 100, 2, ',', '.') }}</div>
                <div>Saldo: R$ {{ number_format($previousSummary['balance'] / 100, 2, ',', '.') }}</div>
            </div>
        </div>
    @endif

    @if ($reportsLevel >= 2)
        <div class="mt-10 rounded-3xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">Top categorias</h2>
            <div class="mt-4 space-y-3 text-sm text-slate-300">
                @forelse ($topCategories as $row)
                    <div class="flex items-center justify-between">
                        <span>{{ $row['category'] }}</span>
                        <span>R$ {{ number_format($row['total_cents'] / 100, 2, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Sem dados suficientes.</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
