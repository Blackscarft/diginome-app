<x-filament-widgets::widget>
    
    <x-filament::section
        heading="Queue Worker Status"
        description="Monitor worker"
        icon="heroicon-o-server"
    >

        <x-filament::callout
            :color="$status['is_running'] ? 'success' : 'danger'"
            :icon="$status['is_running'] ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle'"
        >
            <x-slot name="heading">
                {{ $status['is_running'] ? 'Active' : 'Stopped' }}
            </x-slot>

            <x-slot name="description">
                <p>Environment: {{ $status['environment'] }}</p>
                <p>{{ $status['message'] }}</p>
            </x-slot>
        </x-filament::callout>    

        <x-slot name="afterHeader">
            <x-filament::badge 
                :color="$status['is_running'] ? 'success' : 'danger'"
                :icon="$status['is_running'] ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle'"
            >
                {{ $status['is_running'] ? 'ACTIVE' : 'MATI' }}
            </x-filament::badge>
        </x-slot>

        @if(PHP_OS_FAMILY === 'Linux')
            <x-slot name="footer">
                <div class="flex gap-2">
                        {{ $this->restartWorkerAction() }}
                        {{ $this->startWorkerAction() }}
                        {{ $this->stopWorkerAction() }}
                </div>
            </x-slot>
        @endif

        <x-filament-actions::modals />

    </x-filament::section>
</x-filament-widgets::widget>