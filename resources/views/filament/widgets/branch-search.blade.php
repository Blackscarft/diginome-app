
<div class="fi-header">
    <h1 class="mb-2 fi-header-heading">
        {{ $heading }}
    </h1>

    @if ($subheading)
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ $subheading }}
        </p>
    @endif

    <div class="fi-header-actions-ctn">
        <div class="fi-ac fi-align-start">
            @foreach ($this->getCachedHeaderActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </div>
</div>

<div class="mb-4 space-y-4">
    <x-filament::input.wrapper>
        <x-filament::input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari kode atau nama cabang..."
        />
    </x-filament::input.wrapper>
</div>