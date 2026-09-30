<?php

namespace App\Filament\Exports;

use App\Models\BankAccount;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class BankAccountExporter extends Exporter
{
    protected static ?string $model = BankAccount::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('account.code')
                ->label('Kode Akun'),
            ExportColumn::make('account.name')
                ->label('Nama Akun'),
            ExportColumn::make('bank_code')
                ->label('Kode Bank'),
            ExportColumn::make('bank_name')
                ->label('Nama Bank'),
            ExportColumn::make('account_number')
                ->label('No. Rekening'),
            ExportColumn::make('account_holder')
                ->label('Atas Nama'),
            ExportColumn::make('notes')
                ->label('Catatan'),
            ExportColumn::make('is_active')
                ->label('Status'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your bank account export has completed and ' . Str::of('row')->counted($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Str::of('row')->counted($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
