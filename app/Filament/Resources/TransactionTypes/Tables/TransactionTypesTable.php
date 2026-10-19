<?php

namespace App\Filament\Resources\TransactionTypes\Tables;

use App\Exports\TransactionTypesTemplateExport;
use App\Filament\Exports\TransactionTypeExporter;
use App\Imports\TransactionTypesImport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel; 
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class TransactionTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Daftar Tipe Transaksi')
            ->description('Kelola data tipe transaksi mutasi di sini.') 
            ->columns([
                TextColumn::make('type_code')
                    ->label('Kode Tipe')
                    ->badge()
                    ->icon('heroicon-o-clipboard-document')
                    ->copyable()
                    ->copyMessage('Kode bank berhasil disalin')
                    ->searchable(),

                TextColumn::make('type_name')
                    ->label('Nama Tipe')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Deskripsi'),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'income' => 'success',
                            'expense' => 'danger',
                            'transfer' => 'info',
                            'adjustment' => 'gray',
                            'other' => 'warning',
                            default => 'secondary',
                        };
                    }),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Tidak Aktif'),
            ])
            ->headerActions([
                Action::make('ImportBankAccounts')
                    ->label('Import')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->modalDescription(
                        'Upload file Excel (.xlsx) yang berisi data akun bank. Pastikan format kolom sesuai dengan template yang disediakan.'
                    )
                    ->extraModalFooterActions([
                        Action::make('downloadTemplate')
                            ->label('Download Template')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('warning')
                            ->action(fn () => Excel::download(
                                new TransactionTypesTemplateExport, 
                                'template_import_tipe_transaksi_mutasi.xlsx'
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
                                fn ($file) => 'Transaction_Types_' . now()->format('Ymd_His') . '_' . $file->getClientOriginalName()
                            )
                            ->required()
                            ->maxSize(1024), // 1 MB
                    ])
                    ->action(function (array $data): void {
                        // 1. Ambil relative path tempat file disimpan di storage
                        $storedFilePath = $data['file_excel'];

                        // 2. Ambil nama file asli (misal: "data_mutasi_januari.xlsx")
                        // Karena FileUpload menyimpan file di disk, kita pakai pathinfo() untuk mengambil nama aslinya
                        $fileName = basename($storedFilePath);

                        // 3. Simpan data ke Tabel Imports
                        $importRecord = Import::create([
                            'user_id' => Auth::id(),
                            'importer' => TransactionTypesImport::class,
                            'file_name' => $fileName,
                            'file_path' => $storedFilePath,
                            'total_rows' => 0,
                            'successful_rows' => 0,
                            'processed_rows' => 0
                        ]);

                        // 4. Masukkan proses import ke Queue (Background)
                        Excel::queueImport(
                            new TransactionTypesImport($importRecord->id, Auth::id()), 
                            $storedFilePath, 
                            'local' // Disk penyimpanan Laravel (default: storage/app)
                        );

                        // 5. Kirim notifikasi
                        Notification::make()
                            ->title('Import Dimulai di Background')
                            ->body('File Anda sedang diproses di antrean server. Anda dapat melanjutkan pekerjaan lain.')
                            ->info()
                            ->send();

                    }),

                ExportAction::make('ExportBankAccounts')
                            ->label('Export')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('success')
                            ->exporter(TransactionTypeExporter::class),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Akun')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
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
