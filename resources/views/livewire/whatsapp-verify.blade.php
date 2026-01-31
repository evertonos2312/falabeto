<div class="flex min-h-screen items-center justify-center px-6 py-16">
    <div class="w-full max-w-lg rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl shadow-emerald-500/10">
        <h1 class="text-2xl font-semibold text-white">Confirme seu WhatsApp</h1>
        <p class="mt-2 text-sm text-slate-300">Vamos enviar um código para o seu WhatsApp. (Mock)</p>

        <div class="mt-6 space-y-4">
            <button wire:click="sendCode" class="rounded-full bg-emerald-500 px-5 py-2 text-sm font-semibold text-slate-950 shadow-lg shadow-emerald-500/20">
                Enviar código
            </button>

            @if ($generatedCode)
                <div class="rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                    Código gerado (mock): <span class="font-semibold">{{ $generatedCode }}</span>
                </div>
            @endif

            <form wire:submit="verifyCode" class="space-y-3">
                <div>
                    <label class="text-sm text-slate-300">Digite o código</label>
                    <input type="text" wire:model.defer="code" maxlength="6" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-white focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/20">
                    @error('code') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="w-full rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white hover:border-emerald-300">
                    Validar código
                </button>
            </form>
        </div>
    </div>
</div>
