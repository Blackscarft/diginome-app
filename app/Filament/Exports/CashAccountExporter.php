<?php

namespace App\Filament\Exports;

use App\Models\CashAccount;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class CashAccountExporter extends Exporter
{
    protected static ?string $model = CashAccount::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('account.account_code')
                ->label('Kode Akun'),
            ExportColumn::make('account.account_name')
                ->label('Nama Akun'),
            ExportColumn::make('branch.branch_code')
                ->label('Kode Cabang'),
            ExportColumn::make('branch.branch_name')
                ->label('Nama Cabang'),
            ExportColumn::make('cash_account_code')
                ->label('Kode Kas'),
            ExportColumn::make('cash_account_name')
                ->label('Nama Kas'),
            ExportColumn::make('initial_balance')
                ->label('saldo initial'),
            ExportColumn::make('maximum_balance')
                ->label('saldo maksimum'),
            ExportColumn::make('custodian_name')
                ->label('Penanggung Jawab'),
            ExportColumn::make('notes')
                ->label('Catatan'),
            ExportColumn::make('is_active')
                ->label('Status')
                ->formatStateUsing(fn ($state) => $state ? true : false),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your cash accout export has completed and ' . Str::of('row')->counted($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Str::of('row')->counted($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
