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
                        Acesse seu controle financeiro
                    </h1>
                    <p class="mt-4 text-sm text-slate-300">
                        Continue registrando gastos/receitas, veja pendências e acompanhe o mês.
                    </p>
                </div>

                <div class="space-y-3 text-sm text-slate-300">
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span>Histórico organizado e sempre atualizado</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span>Resumo do mês e pendências em segundos</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span>Alertas e agendamentos quando você precisa</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-slate-900/60 p-5 shadow-2xl shadow-emerald-500/10">
                    <div class="space-y-3 text-sm text-slate-200">
                        <div class="rounded-2xl bg-slate-950/60 p-3">
                            Quer ver o resumo do mês ou registrar algo?
                        </div>
                        <div class="ml-auto w-[85%] rounded-2xl bg-emerald-500/15 p-3 text-emerald-100">
                            recebi 7500 salario
                        </div>
                        <div class="rounded-2xl bg-slate-950/60 p-3">
                            Anotado! Salário R$ 7.500. No mês você já recebeu R$ 9.200.
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
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-white">Bem-vindo de volta</h1>
                    <p class="mt-2 text-sm text-slate-300">Acesse com email ou telefone.</p>
                </div>

                <form wire:submit="authenticate" class="space-y-4">
                    <div>
                        <label class="text-sm text-slate-300">Email ou telefone</label>
                        <input type="text" wire:model.defer="login" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-white focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/20">
                        @error('login') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm text-slate-300">Senha</label>
                        <input type="password" wire:model.defer="password" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-white focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/20">
                        @error('password') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="w-full rounded-full bg-emerald-500 px-4 py-3 text-sm font-semibold text-slate-950 shadow-lg shadow-emerald-500/20">
                        Entrar
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-400">
                    Ainda não tem conta?
                    <a href="{{ route('register') }}" class="text-emerald-200 hover:text-emerald-100">Criar conta</a>
                </p>
            </div>
        </section>
    </div>
</div>
