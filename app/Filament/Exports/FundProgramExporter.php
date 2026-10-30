<?php

namespace App\Filament\Exports;

use App\Models\FundProgram;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class FundProgramExporter extends Exporter
{
    protected static ?string $model = FundProgram::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('parent_id'),
            ExportColumn::make('fund.fund_name')
                ->label('Jenis Sumber'),
            ExportColumn::make('fund_program_code')
                ->label('Kode Program'),
            ExportColumn::make('fund_program_name')
                ->label('Nama Program'),
            ExportColumn::make('description')
                ->label('Deskripsi'),
            ExportColumn::make('category')
                ->label('Kategori'),
            ExportColumn::make('initial_balance')
                ->label('Saldo Initial'),
            ExportColumn::make('is_active'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at')
                
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export fund program telah selesai dan ' . Str::of('row')->counted($export->successful_rows) . ' berhasil diexport.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Str::of('row')->counted($failedRowsCount) . ' gagal diexport.';
        }

        return $body;
    }
}
