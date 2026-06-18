<x-filament-panels::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}
        
        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit" size="lg" color="success">
                Terbitkan Modul
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>