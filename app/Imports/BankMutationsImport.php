<?php

namespace App\Imports;

use App\Models\BankMutation;
use App\Models\User;
use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BankMutationsImport implements
    OnEachRow,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
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
     * Validasi setiap baris.
     */
    public function rules(): array
    {
        return [
            'kode_bank' => ['required'],
            'nama_bank' => ['required', 'string'],
            'tanggal' => ['required'],
            'deskripsi' => ['required', 'string'],
        ];
    }

    /**
     * Dipanggil sebelum import dimulai.
     */
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

    /**
     * Dipanggil setiap baris.
     */
    public function onRow(Row $excelRow): void
    {
        $row = $excelRow->toArray();

        $transactionDate = is_numeric($row['tanggal'])
            ? Date::excelToDateTimeObject($row['tanggal'])->format('Y-m-d')
            : $row['tanggal'];

        DB::transaction(function () use ($row, $transactionDate) {

            BankMutation::create([
                'bank_code'        => (string) $row['kode_bank'],
                'bank_name'        => $row['nama_bank'],
                'transaction_date' => $transactionDate,
                'transaction_time' => $row['waktu'] ?? null,
                'debit'            => $row['debit'] ?? 0,
                'credit'           => $row['kredit'] ?? 0,
                'reference'        => $row['referensi'] ?? null,
                'description'      => $row['deskripsi'],
                'is_matched'       => false,
            ]);

            // Update statistik Filament
            DB::table('imports')
                ->where('id', $this->importRecordId)
                ->update([
                    'successful_rows' => DB::raw('successful_rows + 1'),
                    'processed_rows'  => DB::raw('processed_rows + 1'),
                ]);
        });
    }

    /**
     * Dipanggil setelah seluruh import selesai.
     */
    public function afterImport(AfterImport $event): void
    {
        $importRecord = Import::find($this->importRecordId);

        if (! $importRecord) {
            return;
        }

        // Simpan failed rows
        foreach ($this->failures() as $failure) {
            FailedImportRow::create([
                'import_id'        => $importRecord->id,
                'data'             => $failure->values(),
                'validation_error' => implode(', ', $failure->errors()),
            ]);
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
            ->title($failedCount > 0 ? 'Import Selesai dengan Catatan' : 'Import Mutasi Bank Berhasil')
            ->body(
                "File {$importRecord->file_name} telah selesai diproses ({$importRecord->successful_rows} berhasil, {$failedCount} gagal)."
            )
            ->color($failedCount > 0 ? 'warning' : 'success')
            ->sendToDatabase($user);
    }

    /**
     * Jika import gagal total.
     */
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
                ->title('Import Mutasi Bank Gagal')
                ->body(
                    "Terjadi kesalahan saat memproses {$importRecord->file_name}: {$event->getException()->getMessage()}"
                )
                ->danger()
                ->sendToDatabase($user);
        }
    }

    /**
     * Ukuran chunk.
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}