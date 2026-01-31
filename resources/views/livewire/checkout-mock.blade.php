<div class="flex min-h-screen items-center justify-center px-6 py-16">
    <div class="w-full max-w-lg rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl shadow-emerald-500/10">
        <h1 class="text-2xl font-semibold text-white">Checkout (mock)</h1>
        <p class="mt-2 text-sm text-slate-300">Simulação do Stripe para finalizar seu trial.</p>

        @if ($plan)
            <div class="mt-6 rounded-2xl border border-white/10 bg-slate-900/60 p-4 text-sm text-slate-200">
                <div class="flex items-center justify-between">
                    <span>{{ $plan->name }}</span>
                    <span class="font-semibold">R$ {{ number_format($plan->price_cents / 100, 0, ',', '.') }}/mês</span>
                </div>
                <p class="mt-2 text-xs text-slate-400">Trial de 30 dias incluso.</p>
            </div>
            <div class="mt-4 rounded-2xl border border-white/10 bg-white/5 p-4 text-xs text-slate-300">
                <div class="flex items-center justify-between">
                    <span>Subtotal</span>
                    <span>R$ {{ number_format($plan->price_cents / 100, 0, ',', '.') }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between text-emerald-200">
                    <span>Desconto</span>
                    <span>- R$ {{ number_format($discountCents / 100, 0, ',', '.') }}</span>
                </div>
                <div class="mt-3 flex items-center justify-between text-sm text-white">
                    <span>Total</span>
                    <span>R$ {{ number_format($finalPriceCents / 100, 0, ',', '.') }}</span>
                </div>
                @error('couponCode')
                    <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <button wire:click="pay" class="mt-6 w-full rounded-full bg-emerald-500 px-4 py-3 text-sm font-semibold text-slate-950 shadow-lg shadow-emerald-500/20">
            Pagar com Stripe
        </button>
    </div>
</div>
