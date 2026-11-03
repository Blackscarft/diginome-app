<?php

namespace App\Jobs;

use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;

class ImportFinalizer implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $importRecordId,
        public int $userId,
        public string $successTitle = 'Import Berhasil',
        public string $failedTitle = 'Import Selesai dengan Catatan',
        public string $description = 'Import telah selesai diproses.',
    ) {}

    public function handle(): void
    {
        $importRecord = Import::find($this->importRecordId);

        if (! $importRecord) {
            return;
        }

        $failedCount = FailedImportRow::where(
            'import_id',
            $this->importRecordId
        )->count();

        $importRecord->refresh();

        $importRecord->update([
            'processed_rows' =>
                $importRecord->successful_rows + $failedCount,
            'completed_at' => now(),
        ]);

        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        $hasFailures = $failedCount > 0;

        Notification::make()
            ->title(
                $hasFailures
                    ? $this->failedTitle
                    : $this->successTitle
            )
            ->body(
                "{$this->description} "
                . "({$importRecord->successful_rows} berhasil, "
                . "{$failedCount} gagal)."
            )
            ->color($hasFailures ? 'warning' : 'success')
            ->icon(
                $hasFailures
                    ? 'heroicon-o-exclamation-triangle'
                    : 'heroicon-o-check-circle'
            )
            ->sendToDatabase($user);
    }
}
