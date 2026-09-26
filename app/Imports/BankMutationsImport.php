<?php

namespace App\Imports;

use App\Models\BankMutation;
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
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;

class BankMutationsImport implements
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
     * Proses setiap baris secara manual dengan validasi eksplisit.
     */
    public function onRow(Row $excelRow): void
    {
        $row = $excelRow->toArray();

        // 1. Normalisasi tanggal & waktu dari format Excel Serial Number
        if (isset($row['tanggal']) && is_numeric($row['tanggal'])) {
            $row['tanggal'] = Date::excelToDateTimeObject($row['tanggal'])->format('Y-m-d');
        }

        if (isset($row['waktu']) && is_numeric($row['waktu'])) {
            $row['waktu'] = Date::excelToDateTimeObject($row['waktu'])->format('H:i:s');
        }

        // 2. JALANKAN VALIDASI SECARA MANUAL
        $validator = Validator::make($row, [
            'kode_bank' => ['required'],
            'nama_bank' => ['required', 'string'],
            'tanggal'   => ['required', 'date'],
            'deskripsi' => ['required', 'string'],
            'debit'     => ['nullable', 'numeric'],
            'kredit'    => ['nullable', 'numeric'],
            'waktu'     => ['nullable', 'date_format:H:i:s'],
            'referensi' => ['nullable'],
        ]);

        // Jika data tidak valid (misal: debit/kredit berisi string 'asdasdasd')
        if ($validator->fails()) {
            FailedImportRow::create([
                'import_id'        => $this->importRecordId,
                'data'             => $row,
                'validation_error' => implode(', ', $validator->errors()->all()),
            ]);

            DB::table('imports')
                ->where('id', $this->importRecordId)
                ->increment('processed_rows');

            return; // HENTIKAN proses untuk baris ini, lanjut ke baris berikutnya
        }

        // 3. JIKA LOLOS VALIDASI, SIMPAN KE DATABASE DENGAN TRY-CATCH
        try {
            DB::transaction(function () use ($row) {
                BankMutation::create([
                    'bank_code'        => (string) $row['kode_bank'],
                    'bank_name'        => $row['nama_bank'],
                    'transaction_date' => $row['tanggal'],
                    'transaction_time' => $row['waktu'] ?? null,
                    'debit'            => $row['debit'] ?? 0,
                    'credit'           => $row['kredit'] ?? 0,
                    'reference'        => $row['referensi'] ?? null,
                    'description'      => $row['deskripsi'],
                    'is_matched'       => false,
                ]);

                DB::table('imports')
                    ->where('id', $this->importRecordId)
                    ->increment('successful_rows');

                DB::table('imports')
                    ->where('id', $this->importRecordId)
                    ->increment('processed_rows');
            });
        } catch (Throwable $e) {
            // Tangkap jika terjadi database error tak terduga agar job queue tidak mati
            FailedImportRow::create([
                'import_id'        => $this->importRecordId,
                'data'             => $row,
                'validation_error' => 'Database Error: ' . $e->getMessage(),
            ]);

            DB::table('imports')
                ->where('id', $this->importRecordId)
                ->increment('processed_rows');
        }
    }

    public function onError(Throwable $e): void
    {
        // Tetap dipertahankan untuk mengantisipasi error di luar onRow
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
                    : 'Import Mutasi Bank Berhasil'
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

        if ($user = User::find($this->userId)) {
            Notification::make()
                ->title('Import Mutasi Bank Gagal Total')
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