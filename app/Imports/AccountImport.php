<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\User;
use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Row;
use Throwable;

class AccountImport implements 
    OnEachRow,
    WithHeadingRow,
    SkipsOnFailure,
    SkipsOnError,
    WithChunkReading,
    ShouldQueue,
    WithEvents
{
    use RegistersEventListeners;
    use SkipsFailures;
    
    public function __construct(
        public int $importRecordId,
        public int $userId,
    ) {}

    /**
     * Simpan data gagal beserta pesan error.
     */
    private function saveFailedRow(array $row, string $message): void
    {
        FailedImportRow::create([
            'import_id'        => $this->importRecordId,
            'data'             => $row,
            'validation_error' => $message,
        ]);

        DB::table('imports')
            ->where('id', $this->importRecordId)
            ->increment('processed_rows');
    }

    public function onRow(Row $excelRow): void
    {
        $row = $excelRow->toArray();

        $validator = Validator::make($row, [
            'kode_akun'         => ['required', 'string', 'unique:accounts,account_code'],
            'nama_akun'         => ['required', 'string'],
            'normal_balance'    => ['required', 'in:debit,credit'],
            'parent_id'         => ['required', 'numeric'],
            'dapat_diposting'   => ['required', 'boolean'],
            'urutan_tampilan'   => ['required', 'numeric'],
            'status_aktif'      => ['required', 'boolean'],
            'deskripsi'         => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            $this->saveFailedRow(
                $row,
                implode('; ', $validator->errors()->all())
            );

            return;
        }

        try {
            DB::transaction(function () use ($row) {
                Account::create([
                    'account_code'      => (string) $row['kode_akun'],
                    'account_name'      => (string) $row['nama_akun'],
                    'normal_balance'    => (string) $row['normal_balance'],
                    'is_postable'       => (bool) $row['dapat_diposting'] ?? 0,
                    'parent_id'         => (int) $row['parent_id'] ?? -1,
                    'order'             => (int) $row['urutan_tampilan'] ?? 1,
                    'is_active'         => (bool) $row['status_aktif'] ?? true,
                    'description'       => (string) $row['deskripsi'] ?? null,
                ]);

                DB::table('imports')
                    ->where('id', $this->importRecordId)
                    ->increment('successful_rows');

                DB::table('imports')
                    ->where('id', $this->importRecordId)
                    ->increment('processed_rows');
            });
        } catch (Throwable $e) {
            report($e);
            
            $this->saveFailedRow(
                $row,
                'Database Error: ' . $e->getMessage()
            );
        }
    }

    public function onError(Throwable $e): void
    {
        report($e);
        
        $this->saveFailedRow(
            [],
            'Excel Error: ' . $e->getMessage()
        );
    }

    public function beforeImport(BeforeImport $event): void
    {
        $import = Import::find($this->importRecordId);

        if (! $import) {
            return;
        }

        $rows = $event->getReader()->getTotalRows();
        $totalRows = max(0, (array_values($rows)[0] ?? 0) - 1);

        $import->update([
            'total_rows' => $totalRows,
        ]);
    }

    public function afterImport(AfterImport $event): void
    {
        $importRecord = Import::find($this->importRecordId);

        if (! $importRecord) {
            return;
        }

        $failedCount = FailedImportRow::where('import_id', $importRecord->id)->count();

        $importRecord->refresh();

        $importRecord->update([
            'processed_rows' => $importRecord->successful_rows + $failedCount,
            'completed_at'   => now(),
        ]);

        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        Notification::make()
            ->title(
                $failedCount > 0
                    ? 'Import Selesai dengan Catatan'
                    : 'Import Akun Berhasil'
            )
            ->body(
                "File {$importRecord->file_name} telah selesai diproses ({$importRecord->successful_rows} berhasil, {$failedCount} gagal)."
            )
            ->color($failedCount > 0 ? 'warning' : 'success')
            ->icon($failedCount > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
            ->sendToDatabase($user);
    }

    public function importFailed(ImportFailed $event): void
    {
        $importRecord = Import::find($this->importRecordId);

        if (! $importRecord) {
            return;
        }

        $importRecord->update([
            'completed_at' => now(),
        ]);
        
        report($event->getException());

        if ($user = User::find($this->userId)) {
            Notification::make()
                ->title('Import Akun Gagal Total')
                ->body(
                    "Terjadi kesalahan sistem saat memproses file {$importRecord->file_name}: {$event->getException()->getMessage()}"
                )
                ->danger()
                ->sendToDatabase($user);
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
