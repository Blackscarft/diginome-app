<?php

namespace App\Filament\Exports;

use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class FailedImportRowExporter extends Exporter
{
    protected static ?string $model = FailedImportRow::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            
            ExportColumn::make('import_id')
                ->label('Import ID'),

            ExportColumn::make('data')
                ->label('Detail Data Baris')
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : $state),

            ExportColumn::make('validation_error')
                ->label('Detail Error Validasi'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export detail error selesai dan ' . number_format($export->successful_rows) . ' baris berhasil diunduh.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diexport.';
        }

        return $body;
    }
}
