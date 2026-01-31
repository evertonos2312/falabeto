<div class="mx-auto min-h-screen w-full max-w-5xl px-6 py-16">
    <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
        <div>
            <p class="text-sm text-emerald-200">Trial de 30 dias incluído</p>
            <h1 class="mt-2 text-3xl font-semibold text-white">Escolha seu plano</h1>
            <p class="mt-2 text-sm text-slate-300">Comece agora e só pague depois do trial.</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rounded-full border border-white/10 px-4 py-2 text-xs text-slate-300 hover:text-white">Sair</button>
        </form>
    </div>

    <div class="mt-10 grid gap-6 md:grid-cols-3">
        @foreach ($plans as $plan)
            <form method="POST" action="{{ route('checkout.mock.post') }}" class="rounded-3xl border border-white/10 bg-white/5 p-6">
                @csrf
                <input type="hidden" name="plan_code" value="{{ $plan->code }}">
                <p class="text-sm font-semibold text-emerald-200">{{ $plan->name }}</p>
                <div class="mt-4 flex items-end gap-1">
                    <span class="text-3xl font-semibold text-white">R$ {{ number_format($plan->price_cents / 100, 0, ',', '.') }}</span>
                    <span class="text-sm text-slate-400">/mês</span>
                </div>
                <p class="mt-3 text-sm text-slate-300">Tudo para começar com segurança.</p>
                <ul class="mt-6 space-y-3 text-sm text-slate-300">
                    <li>Trial de 30 dias</li>
                    <li>Mensagens automatizadas</li>
                    <li>Suporte dedicado</li>
                </ul>
                <div class="mt-6">
                    <label class="text-xs text-slate-400">Cupom (opcional)</label>
                    <input type="text" name="coupon_code" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-2 text-xs text-white focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/20">
                </div>
                <button type="submit" class="mt-4 inline-flex w-full items-center justify-center rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-emerald-500/20">
                    Selecionar plano
                </button>
            </form>
        @endforeach
    </div>
</div>
