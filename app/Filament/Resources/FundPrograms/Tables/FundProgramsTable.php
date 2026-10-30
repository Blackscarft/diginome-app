<?php

namespace App\Filament\Resources\FundPrograms\Tables;

use App\Exports\FundProgramsTemplateExport;
use App\Filament\Exports\FundProgramExporter;
use App\Imports\FundProgramsImport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel; 
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class FundProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Daftar Program Dana')
            ->description('Kelola data program dana di sini.') 
            ->columns([
                TextColumn::make('parent.account_name')
                    ->label('Induk (Parent)')
                    ->default('- Root -'),

                TextColumn::make('fund.fund_name')
                    ->label('Jenis Sumber')
                    ->badge(),
                TextColumn::make('fund_program_code')
                    ->label('Kode Program')
                    ->badge()
                    ->searchable(),
                TextColumn::make('fund_program_name')
                    ->label('Nama Program')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori'),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Tidak Aktif')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ]),
                TextColumn::make('initial_balance')
                    ->label('Saldo Initial')
                    ->money('idr')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('ImportFundPrograms')
                    ->label('Import')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->modalDescription(
                        'Upload file Excel (.xlsx) data program dana.'
                    )
                    ->extraModalFooterActions([
                        Action::make('downloadTemplate')
                            ->label('Download Template')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->action(fn () => Excel::download(
                                new FundProgramsTemplateExport, 
                                'template_import_program_dana.xlsx'
                            )),
                    ])
                    ->schema([
                        FileUpload::make('file_excel')
                                ->label('Pilih file Excel (.xlsx)')
                                ->required()
                                ->acceptedFileTypes([
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'application/vnd.ms-excel',
                                ])
                                ->storeFiles(true)
                                ->directory('imports/transaction-types')
                                ->getUploadedFileNameForStorageUsing(
                                    fn ($file) => 'Fund_Programs_' . now()->format('Ymd_His') . '_' . $file->getClientOriginalName()
                                )
                                ->required()
                                ->maxSize(1024), // 1 MB
                    ])
                    ->action(function (array $data): void {
                        $storedFilePath = $data['file_excel'];
                        $importRecord = Import::create([
                            'user_id' => Auth::id(),
                            'importer' => FundProgramsImport::class,
                            'file_name' => basename($storedFilePath),
                            'file_path' => $storedFilePath,
                            'total_rows' => 0,
                            'successful_rows' => 0,
                            'processed_rows' => 0
                        ]);
                        Excel::queueImport(
                            new FundProgramsImport($importRecord->id, Auth::id()), 
                            $storedFilePath, 
                            'local'
                        );
                        Notification::make()
                            ->title('Import Dimulai di Background')
                            ->body('File Anda sedang diproses di antrean server. Anda dapat melanjutkan pekerjaan lain.')
                            ->info()
                            ->send();
                    }),
                    
                ExportAction::make('ExportFundPrograms')
                            ->label('Export')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('success')
                            ->exporter(FundProgramExporter::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
