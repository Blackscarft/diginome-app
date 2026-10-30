<?php
namespace App\Imports;

use App\Models\Fund;
use App\Models\FundProgram;
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

class FundProgramsImport implements
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

    /**
     * Ambil kategori ENUM dari database.
     */
    private function getKategoriEnum(): array
    {
        $column = DB::select(
            "SHOW COLUMNS FROM fund_programs WHERE Field = 'category'"
        );

        if (empty($column)) {
            throw new \RuntimeException(
                'Kolom category tidak ditemukan pada tabel fund_programs.'
            );
        }

        preg_match("/^enum\((.*)\)$/", $column[0]->Type, $matches);

        if (empty($matches[1])) {
            throw new \RuntimeException(
                'Format ENUM category tidak valid.'
            );
        }

        preg_match_all("/'((?:[^']|'')*)'/", $matches[1], $values);

        return array_map(
            fn ($value) => str_replace("''", "'", $value),
            $values[1]
        );
    }

    public function onRow(Row $excelRow): void
    {
        $row = $excelRow->toArray();

        try {
            // Ambil kategori ENUM.
            $kategoriEnum = $this->getKategoriEnum();

            // Cari Fund berdasarkan kode sumber dana.
            $partJenisSumber = explode(
                ' - ',
                $row['jenis_sumber'] ?? '',
                2
            );

            $jenisSumberCode = trim($partJenisSumber[0]);

            $fund = Fund::where(
                'fund_code',
                $jenisSumberCode
            )->first();

            // Siapkan data validasi.
            $data = [
                ...$row,
                'jenis_sumber' => $fund?->id,
            ];

            // Validasi.
            $validator = Validator::make($data, [
                'parent_id' => [
                    'required',
                ],

                'jenis_sumber' => [
                    'required',
                    'exists:funds,id',
                ],

                'kode_program' => [
                    'required',
                    'string',
                    'unique:fund_programs,fund_program_code',
                ],

                'nama_program' => [
                    'required',
                    'string',
                ],

                'deskripsi' => [
                    'nullable',
                    'string',
                ],

                'kategori' => [
                    'required',
                    'string',
                    'in:' . implode(',', $kategoriEnum),
                ],

                'saldo_initial' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'status_aktif' => [
                    'required',
                    'boolean',
                ],
            ], [
                'parent_id.required' =>
                    'Parent ID wajib diisi.',

                'jenis_sumber.required' =>
                    'Jenis sumber dana tidak ditemukan atau belum diisi.',

                'jenis_sumber.exists' =>
                    'Sumber dana tidak ditemukan di database.',

                'kode_program.required' =>
                    'Kode program wajib diisi.',

                'kode_program.unique' =>
                    'Kode program sudah digunakan.',

                'nama_program.required' =>
                    'Nama program wajib diisi.',

                'kategori.required' =>
                    'Kategori wajib diisi.',

                'kategori.in' =>
                    'Kategori harus salah satu dari: ' .
                    implode(', ', $kategoriEnum),

                'saldo_initial.required' =>
                    'Saldo awal wajib diisi.',

                'saldo_initial.numeric' =>
                    'Saldo awal harus berupa angka.',

                'saldo_initial.min' =>
                    'Saldo awal tidak boleh kurang dari 0.',

                'status_aktif.required' =>
                    'Status aktif wajib diisi.',

                'status_aktif.boolean' =>
                    'Status aktif hanya boleh bernilai 1 atau 0.',
            ]);

            if ($validator->fails()) {
                $this->saveFailedRow(
                    $row,
                    implode('; ', $validator->errors()->all())
                );

                return;
            }

            // Simpan data.
            DB::transaction(function () use ($row, $fund) {

                FundProgram::create([
                    'parent_id'         => (int) $row['parent_id'],
                    'fund_id'           => $fund->id,
                    'fund_program_code' => $row['kode_program'],
                    'fund_program_name' => $row['nama_program'],
                    'description'       => $row['deskripsi'] ?? null,
                    'category'          => $row['kategori'],
                    'initial_balance'   => $row['saldo_initial'],
                    'is_active'         => (bool) $row['status_aktif'],
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
                'Import Error: ' . $e->getMessage()
            );
        }
    }

    /**
     * Error dari Laravel Excel.
     */
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

        $totalRows = max(
            0,
            (array_values($rows)[0] ?? 0) - 1
        );

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

        $failedCount = FailedImportRow::where(
            'import_id',
            $this->importRecordId
        )->count();

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
                    : 'Import Program Dana Berhasil'
            )
            ->body(
                "File {$importRecord->file_name} selesai diproses " .
                "({$importRecord->successful_rows} berhasil, " .
                "{$failedCount} gagal)."
            )
            ->color($failedCount > 0 ? 'warning' : 'success')
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

        $user = User::find($this->userId);

        if ($user) {
            Notification::make()
                ->title('Import Program Dana Gagal')
                ->body(
                    'Error: ' . $event->getException()->getMessage()
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
