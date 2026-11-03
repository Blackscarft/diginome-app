<?php

namespace App\Imports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class AccountTransactionsSheetImport implements
    Import,
    WithMultipleSheets,
    ShouldQueue,
    WithChunkReading
{
    public function __construct(
        public int $importRecordId,
        public int $userId
    ) {}

    public function sheets(): array
    {
        return [
            'Transactions' => new AccountTransactionsImport(
                $this->importRecordId,
                $this->userId
            ),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}