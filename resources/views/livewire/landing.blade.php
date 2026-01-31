<div class="bg-slate-950">
    <header class="border-b border-white/5">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-5">
            <div class="flex items-center gap-3">
                <div class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-500/20 ring-1 ring-emerald-400/40">
                    <svg class="h-5 w-5 text-emerald-200" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <!-- chat bubble custom (não é o do WhatsApp) -->
                        <path
                            d="M7.5 6.5h9A3.5 3.5 0 0 1 20 10v4A3.5 3.5 0 0 1 16.5 17.5H12l-3.5 2v-2H7.5A3.5 3.5 0 0 1 4 14v-4A3.5 3.5 0 0 1 7.5 6.5Z"
                            stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"
                        />
                        <!-- small "spark" / check-ish detail to feel modern -->
                        <path
                            d="M8.8 12.2l1.6 1.6 3.8-4"
                            stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
                        />
                    </svg>
                </div>
                <span class="text-lg font-semibold tracking-tight">Fala, Beto!</span>
            </div>
            <nav class="hidden items-center gap-6 text-sm text-slate-300 md:flex">
                <a href="#como-funciona" class="hover:text-white">Como funciona</a>
                <a href="#beneficios" class="hover:text-white">Benefícios</a>
                <a href="#planos" class="hover:text-white">Planos</a>
                <a href="#faq" class="hover:text-white">FAQ</a>
            </nav>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm text-slate-300 hover:text-white">Entrar</a>
                <a href="{{ route('register') }}"
                   class="rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-emerald-500/20">Começar
                    teste</a>
            </div>
        </div>
    </header>

    <main>
        <section class="relative overflow-hidden">
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.18),_transparent_55%)]"></div>
            <div class="mx-auto w-full max-w-6xl px-6 py-20">
                <div class="grid gap-12 md:grid-cols-[1.2fr_0.8fr] md:items-center">
                    <div>
                        <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-200">
                            WhatsApp primeiro
                        </p>
                        <h1 class="text-4xl font-semibold tracking-tight text-white md:text-5xl">
                            Seu controle financeiro no WhatsApp, do jeito mais simples
                        </h1>
                        <p class="mt-5 text-lg text-slate-300">
                            O Beto registra gastos e receitas, organiza suas contas e mostra o que está pendente — tudo
                            por mensagem, sem planilhas complicadas.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="{{ route('register') }}"
                               class="rounded-full bg-emerald-500 px-6 py-3 text-sm font-semibold text-slate-950 shadow-lg shadow-emerald-500/25">
                                Começar teste de 14 dias
                            </a>
                            <a href="#planos"
                               class="rounded-full border border-white/15 px-6 py-3 text-sm font-semibold text-white hover:border-white/30">
                                Ver planos
                            </a>
                        </div>
                        <div class="mt-8 flex flex-wrap gap-6 text-sm text-slate-400">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                Ativação em minutos
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                14 dias para testar
                            </div>
                        </div>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-emerald-500/10">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-emerald-200">Resumo no WhatsApp</span>
                            <span
                                class="rounded-full border border-white/10 px-3 py-1 text-xs text-slate-300">24/7</span>
                        </div>
                        <div class="mt-6 space-y-4 text-sm text-slate-200">
                            <div class="rounded-2xl bg-slate-900/60 p-4">
                                Oi! Eu sou o Beto 🙂 Quer registrar um gasto, uma receita ou ver o que está pendente?
                            </div>
                            <div class="ml-auto w-[85%] rounded-2xl bg-emerald-500/15 p-4 text-emerald-100">
                                Gastei 12 no mercado
                            </div>
                            <div class="rounded-2xl bg-slate-900/60 p-4">
                                Anotado! Mercado R$ 12. No mês você já gastou R$ 327. Quer categorizar como Alimentação?
                            </div>
                        </div>
                        <div
                            class="mt-6 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-xs text-emerald-100">
                            Controle de gastos, receitas e contas a pagar — direto no WhatsApp.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="como-funciona" class="mx-auto w-full max-w-6xl px-6 py-16">
            <div class="grid gap-8 md:grid-cols-3">
                <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                    <p class="text-sm font-semibold text-emerald-200">1. Cadastro rápido</p>
                    <h3 class="mt-3 text-xl font-semibold text-white">Confirme seu WhatsApp</h3>
                    <p class="mt-2 text-sm text-slate-300">Você valida seu número e já pode começar o teste de 14
                        dias.</p>
                </div>
                <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                    <p class="text-sm font-semibold text-emerald-200">2. Registre por mensagem</p>
                    <h3 class="mt-3 text-xl font-semibold text-white">Beto entende seu dia a dia</h3>
                    <p class="mt-2 text-sm text-slate-300">“Gastei 34 no almoço”, “caiu salário”, “tem conta pendente?”
                        — ele organiza tudo.</p>
                </div>
                <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                    <p class="text-sm font-semibold text-emerald-200">3. Acompanhe e planeje</p>
                    <h3 class="mt-3 text-xl font-semibold text-white">Resumo e lembretes</h3>
                    <p class="mt-2 text-sm text-slate-300">Veja o total do mês, contas a pagar e agendamentos sem abrir
                        planilha.</p>
                </div>
            </div>
        </section>

        <section id="beneficios" class="bg-white/5">
            <div class="mx-auto w-full max-w-6xl px-6 py-16">
                <div class="grid gap-10 md:grid-cols-[1fr_1fr] md:items-center">
                    <div>
                        <h2 class="text-3xl font-semibold text-white">Benefícios que fazem diferença</h2>
                        <p class="mt-3 text-slate-300">Menos esquecimento, mais clareza do que entra e sai — com o Beto
                            do seu lado.</p>
                        <div class="mt-6 space-y-4 text-sm text-slate-300">
                            <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">Registro rápido de
                                gastos e receitas pelo WhatsApp
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">Contas a pagar com
                                lembretes e agendamentos
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">Resumo do mês e visão
                                por categoria (quando disponível)
                            </div>
                        </div>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-slate-900/60 p-6">
                        <div class="flex items-center justify-between text-sm text-slate-300">
                            <span>Organização do mês</span>
                            <span class="text-emerald-300">Em dia</span>
                        </div>
                        <div class="mt-4 h-2 rounded-full bg-white/10">
                            <div class="h-2 w-[92%] rounded-full bg-emerald-400"></div>
                        </div>
                        <div class="mt-6 grid gap-4 text-sm text-slate-300">
                            <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 p-4">
                                Menos contas esquecidas
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                Mais clareza do seu saldo
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <livewire:landing-plans />
        <section id="faq" class="bg-white/5">
            <div class="mx-auto w-full max-w-6xl px-6 py-16">
                <h2 class="text-3xl font-semibold text-white">Perguntas frequentes</h2>
                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    <div class="rounded-3xl border border-white/10 bg-slate-900/60 p-6">
                        <h3 class="text-lg font-semibold text-white">Preciso configurar algo complexo?</h3>
                        <p class="mt-2 text-sm text-slate-300">Não. Você confirma seu WhatsApp, escolhe um plano e
                            começa a registrar por mensagem.</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-slate-900/60 p-6">
                        <h3 class="text-lg font-semibold text-white">O teste de 14 dias funciona como?</h3>
                        <p class="mt-2 text-sm text-slate-300">Você testa o Fala Beto por 14 dias e decide se quer
                            continuar em um dos planos.</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-slate-900/60 p-6">
                        <h3 class="text-lg font-semibold text-white">Consigo ver o resumo do mês?</h3>
                        <p class="mt-2 text-sm text-slate-300">Sim. O Beto mostra totais, pendências e movimentações — e
                            você também acompanha pelo painel.</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-slate-900/60 p-6">
                        <h3 class="text-lg font-semibold text-white">Meus dados ficam seguros?</h3>
                        <p class="mt-2 text-sm text-slate-300">Aplicamos boas práticas de segurança e criptografia em
                            dados sensíveis. Você mantém o controle.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-white/5">
        <div
            class="mx-auto flex w-full max-w-6xl flex-col items-center justify-between gap-4 px-6 py-8 text-sm text-slate-400 md:flex-row">
            <span>© {{ date('Y') }} Fala Beto. Todos os direitos reservados.</span>
            <div class="flex gap-4">
                <a href="#planos" class="hover:text-white">Planos</a>
                <a href="#faq" class="hover:text-white">FAQ</a>
            </div>
        </div>
    </footer>
</div>
