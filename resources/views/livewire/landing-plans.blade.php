<section id="planos" class="mx-auto w-full max-w-6xl px-6 py-16">
    @php
        $trialEnabled = (bool) settings('commercial.trial_enabled_default', true);
        $trialDays = (int) settings('commercial.trial_days_default', 14);

        $featureCatalog = [
            'messages_per_day' => [
                'label' => 'Mensagens por dia',
                'type' => 'int',
                'render' => fn($v) => $v ? "{$v} mensagens/dia" : null,
            ],
            'scheduled_transactions_limit' => [
                'label' => 'Agendamentos',
                'type' => 'int',
                'render' => function($v) {
                    if ($v === null) return null;
                    return ($v >= 999999) ? 'Agendamentos ilimitados' : "{$v} agendamentos/mês";
                },
            ],
            'reports_level' => [
                'label' => 'Relatórios',
                'type' => 'int',
                'render' => function($v) {
                    return match ((int) $v) {
                        0 => 'Resumo do mês',
                        1 => 'Relatórios detalhados',
                        2 => 'Relatórios avançados',
                        default => null,
                    };
                },
            ],
            'export_csv' => [
                'label' => 'Exportação em CSV',
                'type' => 'bool',
                'render' => fn($v) => $v ? 'Exportação em CSV' : null,
            ],
        ];

        $getItemValue = function($plan, string $code) {
            $item = $plan->items->firstWhere('item_code', $code);
            if (!$item) return null;

            return match ($item->item_type) {
                'int' => $item->value_int,
                'bool' => $item->value_bool,
                'string' => $item->value_string,
                default => null,
            };
        };
    @endphp

    <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
        <div>
            <h2 class="text-3xl font-semibold text-white">Planos simples e transparentes</h2>
            <p class="mt-2 text-slate-300">
                @if ($trialEnabled)
                    Teste por {{ $trialDays }} dias em qualquer plano.
                @else
                    Planos mensais sem período de teste.
                @endif
            </p>
        </div>
        <a href="{{ route('register') }}"
           class="rounded-full border border-emerald-400/40 px-5 py-2 text-sm font-semibold text-emerald-200 hover:border-emerald-300">
            Começar agora
        </a>
    </div>

    <div class="mt-10 grid gap-6 md:grid-cols-3">
        @foreach ($plans as $plan)
            @php
                $isPremium = in_array(strtolower($plan->code ?? ''), ['premium', 'pro'], true);
            @endphp

            <div
                class="rounded-3xl border {{ $isPremium ? 'border-emerald-400/30 bg-emerald-500/10 ring-1 ring-emerald-400/20' : 'border-white/10 bg-white/5' }} p-6">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-emerald-200">{{ $plan->name }}</p>
                    @if ($isPremium)
                        <span
                            class="rounded-full border border-emerald-400/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-200">
                            Mais completo
                        </span>
                    @endif
                </div>

                <div class="mt-4 flex items-end gap-1">
                    <span class="text-3xl font-semibold text-white">
                        R$ {{ number_format($plan->price_cents / 100, 0, ',', '.') }}
                    </span>
                    <span class="text-sm text-slate-400">/mês</span>
                </div>

                <p class="mt-3 text-sm text-slate-300">
                    {{ $isPremium ? 'Para quem quer visão completa, exportação e mais limites.' : 'O essencial para organizar suas finanças no WhatsApp.' }}
                </p>

                <ul class="mt-6 space-y-3 text-sm text-slate-300">
                    @foreach ($featureCatalog as $code => $meta)
                        @php
                            $raw = $getItemValue($plan, $code);

                            $has = match ($meta['type']) {
                                'bool' => (bool) $raw,
                                'int' => $raw !== null && (int) $raw > 0,
                                'string' => $raw !== null && trim((string) $raw) !== '',
                                default => false,
                            };

                            $text = $meta['render']($raw);
                            $text = $text ?: $meta['label'];
                        @endphp

                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-md border
                                {{ $has ? 'border-emerald-400/30 bg-emerald-500/10 text-emerald-200' : 'border-white/10 bg-white/5 text-slate-500' }}">
                                @if ($has)
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                              d="M16.704 5.29a1 1 0 0 1 0 1.414l-7.5 7.5a1 1 0 0 1-1.414 0l-3.5-3.5A1 1 0 1 1 5.704 9.29l2.793 2.793 6.793-6.793a1 1 0 0 1 1.414 0Z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                              d="M5.293 5.293a1 1 0 0 1 1.414 0L10 8.586l3.293-3.293a1 1 0 1 1 1.414 1.414L11.414 10l3.293 3.293a1 1 0 0 1-1.414 1.414L10 11.414l-3.293 3.293a1 1 0 0 1-1.414-1.414L8.586 10 5.293 6.707a1 1 0 0 1 0-1.414Z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </span>

                            <div class="leading-5">
                                <span class="{{ $has ? 'text-slate-200' : 'text-slate-400' }}">
                                    {{ $text }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('register') }}"
                   class="mt-6 inline-flex w-full items-center justify-center rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-emerald-500/20">
                    Começar teste
                </a>
            </div>
        @endforeach
    </div>
</section>
