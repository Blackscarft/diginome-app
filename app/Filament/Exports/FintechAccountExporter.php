<?php

namespace App\Filament\Exports;

use App\Models\FintechAccount;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class FintechAccountExporter extends Exporter
{
    protected static ?string $model = FintechAccount::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('account.account_code')
                ->label('Kode Akun'),
            ExportColumn::make('account.account_name')
                ->label('Nama Akun'),
            ExportColumn::make('feeAccount.account_code')
                ->label('Kode Akun Fee'),
            ExportColumn::make('feeAccount.account_name')
                ->label('Nama Akun Fee'),
            ExportColumn::make('fintech_account_code')
                ->label('Kode Fintech'),
            ExportColumn::make('fintech_account_name')
                ->label('Nama Fintech'),
            ExportColumn::make('merchant_id')
                    ->label('merchant id'),
            ExportColumn::make('initial_balance')
                ->label('saldo initial'),
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
        $body = 'Your fintech account export has completed and ' . Str::of('row')->counted($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Str::of('row')->counted($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
