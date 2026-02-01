@props([
    'name',
    'title',
])

<div id="{{ $name }}-modal" data-modal="{{ $name }}" class="fixed inset-0 z-50 hidden items-center justify-center px-6 py-8" style="display:none; position:fixed; inset:0; z-index:50;">
    <div class="absolute inset-0 bg-slate-950/70 opacity-0 backdrop-blur-sm transition-opacity duration-200" style="position:absolute; inset:0;" data-modal-overlay></div>
    <div class="relative w-full max-w-xl max-h-[80vh] overflow-y-auto rounded-3xl border border-slate-200/70 bg-white p-6 text-black shadow-2xl shadow-slate-950/30 opacity-0 transition-all duration-200 translate-y-2" data-modal-panel>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-black">{{ $title }}</h2>
                @if (isset($subtitle))
                    <p class="mt-2 text-sm text-slate-700">{{ $subtitle }}</p>
                @endif
            </div>
            <button type="button" class="cursor-pointer rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-700 hover:text-black" data-close-modal>
                Fechar
            </button>
        </div>

        <div class="mt-6 space-y-4 text-sm text-slate-700">
            {{ $slot }}
        </div>
    </div>
</div>
