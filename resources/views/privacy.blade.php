@component('layouts.app', ['title' => 'Política de Privacidade'])
    <div class="mx-auto min-h-screen w-full max-w-4xl px-6 py-16 text-slate-200">
        <h1 class="text-3xl font-semibold text-white">Política de Privacidade</h1>
        <p class="mt-4 text-sm text-slate-300">
            Sua privacidade é importante. Usamos dados apenas para operar o produto, garantir segurança
            e melhorar a experiência.
        </p>

        <div class="mt-8 space-y-6 text-sm text-slate-300">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Logs de auditoria</h2>
                <p class="mt-2">Registramos metadados das mensagens para rastreabilidade e prevenção de fraudes.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Dados financeiros</h2>
                <p class="mt-2">Campos sensíveis são criptografados e não são compartilhados com terceiros.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Contato</h2>
                <p class="mt-2">Você pode solicitar suporte ou exclusão de dados pelo WhatsApp oficial.</p>
            </div>
        </div>
    </div>
@endcomponent
