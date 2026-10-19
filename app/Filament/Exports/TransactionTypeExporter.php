<?php

namespace App\Filament\Exports;

use App\Models\TransactionType;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class TransactionTypeExporter extends Exporter
{
    protected static ?string $model = TransactionType::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('type_code')
                ->label('Kode Tipe'),
            ExportColumn::make('type_name')
                ->label('Nama Tipe'),
            ExportColumn::make('description')
                ->label('Deskripsi'),
            ExportColumn::make('category')
                ->label('Kategori'),
            ExportColumn::make('is_active')
                ->label('Status')
                ->formatStateUsing(fn ($state) => $state ? true : false),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your transaction type export has completed and ' . Str::of('row')->counted($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Str::of('row')->counted($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
