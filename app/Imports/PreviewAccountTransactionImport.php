<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;

class PreviewAccountTransactionImport implements Import, WithMultipleSheets 
{
    use WithConditionalSheets;

    public TransactionSheetImport $transactionSheet;

    public function __construct()
    {
        $this->transactionSheet = new TransactionSheetImport();
    }

    public function conditionalSheets(): array
    {
        return [
            'Transactions' => $this->transactionSheet
        ];
    }
}

class TransactionSheetImport implements ToCollection, WithHeadingRow
{
    public Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    public function collection(Collection $rows): void
    {
        $this->rows = $rows;
    }
}
