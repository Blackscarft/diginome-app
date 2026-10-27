<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}
    </form>

    {{ $this->summary }}

    <x-filament::section>
            <x-slot name="heading">
                Preview Data Transaksi
            </x-slot>

            <x-slot name="description">
                Menampilkan maksimal 100 dari {{ $totalRows }} baris.
                Data belum disimpan ke database.
            </x-slot>
            {{ $this->table }}
    </x-filament::section>

</x-filament-panels::page>
