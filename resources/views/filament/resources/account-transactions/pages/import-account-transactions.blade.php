<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        {{-- <div class="mt-6 flex justify-end gap-x-4">
            <x-filament::button type="submit" color="primary">
                Proses Import
            </x-filament::button>
        </div> --}}
    </form>
</x-filament-panels::page>
