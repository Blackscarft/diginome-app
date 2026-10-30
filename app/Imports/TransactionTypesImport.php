<?php

namespace App\Imports;

use App\Models\TransactionType;
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

class TransactionTypesImport implements  
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

        // Ambil definisi enum dari database
        $enumCategory = DB::select(
            "SHOW COLUMNS FROM transaction_types WHERE Field = 'category'"
        )[0];

        // Ekstrak nilai enum
        preg_match("/^enum\((.*)\)$/", $enumCategory->Type, $matches);
        preg_match_all("/'((?:[^']|'')*)'/", $matches[1], $values);

        $kategoriEnum = array_map(
            fn ($value) => str_replace("''", "'", $value),
            $values[1]
        );

        $validator = Validator::make($row, [
            'kode_tipe'     => ['required', 'string', 'unique:transaction_types,type_code'],
            'nama_tipe'     => ['required', 'string'],
            'deskripsi'     => ['nullable', 'string'],
            'kategori'     => ['required', 'string', 'in:' . implode(',', $kategoriEnum)],
            'status_aktif'  => ['required', 'boolean', 'in:1,0'],
        ],[
            'kategori.in' => 'Kategori harus salah satu dari: ' . implode(', ', $kategoriEnum),
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
                TransactionType::create([
                    'type_code'       => (string) $row['kode_tipe'],
                    'type_name'       => (string) $row['nama_tipe'],
                    'description'     => (string) $row['deskripsi'] ?? null,
                    'category'        => (string) $row['kategori'],
                    'is_active'       => (bool) $row['status_aktif'],
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
                    : 'Import Tipe Transaksi Berhasil'
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
                ->title('Import Tipe Transaksi Gagal Total')
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
