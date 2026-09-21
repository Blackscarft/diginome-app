<x-filament-panels::page>
    {{-- Render Komponen Tree di Atas --}}
    @livewire(\App\Filament\Pages\ManageAccounts::class)

    <div class="mt-8">
        <h2 class="text-lg font-bold tracking-tight mb-4">Daftar Akun (Tabel View)</h2>
        
        {{-- Render Tabel Filament di Bawah --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>