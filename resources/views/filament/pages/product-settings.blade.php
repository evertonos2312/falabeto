<x-filament-panels::page>
    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        @if (count($this->getFormActions()))
            <x-filament::actions :actions="$this->getFormActions()" />
        @endif
    </form>
</x-filament-panels::page>
