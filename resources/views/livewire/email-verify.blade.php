<div class="min-h-screen bg-slate-950">
    <div class="mx-auto grid min-h-screen w-full max-w-6xl gap-6 md:grid-cols-[1.2fr_0.8fr] md:gap-4">
        <section class="relative flex items-center overflow-hidden px-6 py-12 md:px-8 md:py-16">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.18),_transparent_55%)]"></div>
            <div class="relative mx-auto flex w-full max-w-xl flex-col gap-8 text-slate-200 md:max-w-2xl">
                <div>
                    <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-200">
                        WhatsApp primeiro
                    </p>
                    <h1 class="text-3xl font-semibold tracking-tight text-white md:text-4xl">
                        Seu controle financeiro no WhatsApp
                    </h1>
                    <p class="mt-4 text-sm text-slate-300">
                        Registre gastos/receitas, veja pendências e acompanhe o mês. Em poucos minutos.
                    </p>
                </div>

                <div class="space-y-3 text-sm text-slate-300">
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span>Anote por mensagem: “gastei 12 no mercado”</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span>Veja o total do mês e o que está pendente</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span>Lembretes e agendamentos (conforme plano)</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-slate-900/60 p-5 shadow-2xl shadow-emerald-500/10">
                    <div class="space-y-3 text-sm text-slate-200">
                        <div class="rounded-2xl bg-slate-950/60 p-3">
                            Quer registrar um gasto ou ver pendências?
                        </div>
                        <div class="ml-auto w-[85%] rounded-2xl bg-emerald-500/15 p-3 text-emerald-100">
                            coloca 12 no mercado
                        </div>
                        <div class="rounded-2xl bg-slate-950/60 p-3">
                            Anotado! Mercado R$ 12. No mês você já gastou R$ 327.
                        </div>
                    </div>
                </div>

                <p class="text-xs text-slate-400">
                    Criptografia em dados sensíveis.
                </p>
            </div>
        </section>

        <section class="flex items-center justify-center px-6 py-12 md:px-8 md:py-16">
            <div class="w-full max-w-md rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl shadow-emerald-500/10">
                @php
                    $logoPath = settings('branding.logo_path');
                    $logoUrl = $logoPath ? asset('storage/' . $logoPath) : asset('images/logo.png');
                @endphp
                <a href="{{ url('/') }}" class="mb-6 flex items-center justify-center">
                    <img src="{{ $logoUrl }}" alt="Fala, Beto!" class="h-10">
                </a>

                @if (session('warning'))
                    <div class="mb-4 rounded-2xl border border-amber-400/40 bg-amber-500/10 px-4 py-3 text-xs text-amber-100">
                        {{ session('warning') }}
                    </div>
                @endif

                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-white">Confirme seu email</h1>
                    <p class="mt-2 text-sm text-slate-300">Enviamos um código de 6 dígitos para {{ $email }}.</p>
                </div>

                <div wire:poll.1s="updateCooldown"></div>
                <form wire:submit="verifyEmailCode" class="space-y-4">
                    <div>
                        <label class="text-sm text-slate-300">Código</label>
                        <input type="text" wire:model.defer="email_code" maxlength="6" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-white focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/20">
                        @error('email_code') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="w-full cursor-pointer rounded-full bg-emerald-500 px-4 py-3 text-sm font-semibold text-slate-950 shadow-lg shadow-emerald-500/20">
                        Continuar
                    </button>
                    <button type="button" wire:click="resendEmailCode" class="w-full cursor-pointer rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white hover:border-emerald-300" @disabled($cooldownRemaining > 0)>
                        Reenviar código
                    </button>
                    @if ($cooldownRemaining > 0)
                        <p class="text-xs text-slate-400">Aguarde {{ $cooldownRemaining }}s para reenviar.</p>
                    @endif
                </form>

                <p class="mt-6 text-center text-sm text-slate-400">
                    Quer voltar?
                    <a href="{{ route('login') }}" class="text-emerald-200 hover:text-emerald-100">Entrar</a>
                </p>
            </div>
        </section>
    </div>
</div>
