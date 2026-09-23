<?php

namespace App\Filament\Widgets;

use App\Services\WorkerCheckerService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;

use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Contracts\HasForms;

class QueueWorkerStatusWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected ?string $pollingInterval = '10s';
    protected string $view = 'filament.widgets.queue-worker-status-widget';
    protected int | string | array $columnSpan = 1;
    /**
     * Mengirimkan data status aktual ke Blade View
     */
    protected function getViewData(): array
    {
        return [
            'status' => WorkerCheckerService::isRunning(),
        ];
    }

    /**
     * Tombol Action: Restart Worker
     */
    public function restartWorkerAction(): Action
    {
        return Action::make('restartWorkerAction')
            ->label('Restart')
            ->icon('heroicon-m-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Restart Queue Worker?')
            ->modalDescription('Apakah Anda yakin ingin merestart seluruh proses queue worker?')
            ->action(fn () => $this->handleWorkerAction('restart'));
    }

    /**
     * Tombol Action: Start Worker
     */
    public function startWorkerAction(): Action
    {
        return Action::make('startWorkerAction')
            ->label('Start')
            ->icon('heroicon-m-play')
            ->color('success')
            ->action(fn () => $this->handleWorkerAction('start'));
    }

    /**
     * Tombol Action: Stop Worker
     */
    public function stopWorkerAction(): Action
    {
        return Action::make('stopWorkerAction')
            ->label('Stop')
            ->icon('heroicon-m-power')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Matikan Queue Worker?')
            ->modalDescription('Perhatian: Job antrean tidak akan diproses sampai worker dihidupkan kembali!')
            ->action(fn () => $this->handleWorkerAction('stop'));
    }

    /**
     * Helper eksekusi aksi dan pengiriman notifikasi Toast
     */
    private function handleWorkerAction(string $action): void
    {
        $result = WorkerCheckerService::executeAction($action);

        if ($result['success']) {
            Notification::make()
                ->title('Berhasil!')
                ->body($result['message'])
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Gagal!')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }
}
