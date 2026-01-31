@component('layouts.app', ['title' => 'Termos de Uso'])
    <div class="mx-auto min-h-screen w-full max-w-4xl px-6 py-16 text-slate-200">
        <h1 class="text-3xl font-semibold text-white">Termos de Uso</h1>
        <p class="mt-4 text-sm text-slate-300">
            Estes termos descrevem o uso do Falabeto. Ao criar sua conta, você concorda com o uso responsável
            do serviço e com as políticas de segurança e privacidade.
        </p>

        <div class="mt-8 space-y-6 text-sm text-slate-300">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Dados armazenados</h2>
                <p class="mt-2">Guardamos dados essenciais para operação financeira e atendimento no WhatsApp.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Criptografia</h2>
                <p class="mt-2">Campos textuais sensíveis são criptografados para proteção adicional.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Uso de IA</h2>
                <p class="mt-2">Mensagens podem ser interpretadas por IA para automatizar lançamentos.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Cancelamento</h2>
                <p class="mt-2">Você pode cancelar o plano a qualquer momento. O acesso pode ser reduzido após o término.</p>
            </div>
        </div>
    </div>
@endcomponent
