<?php

namespace App\Imports;

use App\Models\AccountTransaction;
use App\Models\BankAccount;
use App\Models\CashAccount;
use App\Models\FintechAccount;
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

use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class AccountTransactionsImport implements     
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

        $accountType = match ($row['account_type'] ?? null) {
            'Bank' => BankAccount::class,
            'Cash' => CashAccount::class,
            'Fintech' => FintechAccount::class,
            default => null, // Atau class default jika tidak ada yang cocok
        };

        $partAccount = explode(' - ',  $row['account_name'], 2);
        $account_name   = $partAccount[0];
        $account_number = $partAccount[1] ?? null;

        $partTransactionType = explode(' - ',  $row['transaction_type'], 2);
        $transaction_type_code   = $partTransactionType[0];
        $transaction_type_name   = $partTransactionType[1] ?? null;

        $transaction_date = $row['transaction_date'];
        if ($transaction_date !== null && $transaction_date !== '') {
            if (is_numeric($transaction_date)) {
                $transaction_date = ExcelDate::excelToDateTimeObject((float) $transaction_date)
                    ->format('Y-m-d');
            } else {
                $transaction_date = Carbon::parse($transaction_date)->format('Y-m-d');
            }
        }

        $transaction_time = $row['transaction_time'];
        if ($transaction_time !== null && $transaction_time !== '') {
            if (is_numeric($transaction_time)) {
                $transaction_time = ExcelDate::excelToDateTimeObject((float) $transaction_time)
                    ->format('H:i:s');
            }else{
                $transaction_time = Carbon::parse($transaction_time)->format('H:i:s');
            }
        }

        $validator = Validator::make([
            ...$row,
            'account_number' => $account_number,
            'transaction_type_code' => $transaction_type_code,
            'transaction_date' => $transaction_date,
            'transaction_time' => $transaction_time,
        ], [
            'account_type' => ['required', 'string', 'in:Bank,Cash,Fintech'],
            'debit' => ['required', 'numeric', 'min:0'],
            'credit' => ['required', 'numeric', 'min:0'],
            'account_number' => ['required', 'exists:bank_accounts,account_number'],
            'transaction_type_code' => ['required', 'exists:transaction_types,type_code'],
            'transaction_date' => ['required', 'date_format:Y-m-d'],
            'transaction_time' => ['nullable', 'date_format:H:i:s'],
            'description' => ['nullable', 'max:255'],
            'reference' => ['nullable', 'max:255'],
        ]);

        if ($validator->fails()) {
            $this->saveFailedRow(
                $row,
                implode('; ', $validator->errors()->all())
            );

            return;
        }

        $accountable_id = match ($row['account_type'] ?? null) {
            'Bank' => BankAccount::where('account_number', $account_number)
                ->value('id'),

            'Cash' => CashAccount::where('account_number', $account_number)
                ->value('id'),

            'Fintech' => FintechAccount::where('merchant_id', $account_number)
                ->value('id'),
            default => null,
        };

        $transactionTypeId = TransactionType::where('type_code', $transaction_type_code)->value('id');

        try {
            DB::transaction(function () use (
                $row,
                $accountType,
                $transaction_date,
                $transaction_time,
                $accountable_id,
                $transactionTypeId
            ) {
                AccountTransaction::create([
                    'accountable_type'  => $accountType,
                    'accountable_id'    => $accountable_id,
                    'transaction_types' => $transactionTypeId,
                    'debit'             => $row['debit'],
                    'credit'            => $row['credit'],
                    'transaction_date'  => $transaction_date,
                    'transaction_time'  => $transaction_time ?? null,
                    'description'       => $row['description'] ?? null,
                    'reference'         => $row['reference'] ?? null,
                    'fee_amount'        => $row['fee_amount'] ?? 0,
                    'is_matched'        => false
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
                    : 'Import Transaksi Berhasil'
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
                ->title('Import Transaksi Gagal Total')
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
