<?php

namespace App\Filament\Imports;

use App\Models\BankMutation;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class BankMutationImporter extends Importer
{
    protected static ?string $model = BankMutation::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('journal_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('bank_account_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('source_id')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('bank_code')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('bank_name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('debit')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('credit')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('transaction_date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('transaction_time'),
            ImportColumn::make('description')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('reference')
                ->rules(['max:255']),
            ImportColumn::make('is_matched')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): BankMutation
    {
        return new BankMutation();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your bank mutation import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
