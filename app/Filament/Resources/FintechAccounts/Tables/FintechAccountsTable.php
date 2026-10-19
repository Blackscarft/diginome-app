<?php

namespace App\Filament\Resources\FintechAccounts\Tables;

use App\Exports\FintechAccountTemplateExport;
use App\Filament\Exports\FintechAccountExporter;
use App\Imports\FintechAccountsImport;
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
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class FintechAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Daftar Akun Fintech')
            ->description('Kelola data akun financial technology perusahaan di sini.') 
            ->columns([
                TextColumn::make('account.account_name')
                    ->label('Kode Akun')
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->account->account_code . ' - ' . $state
                    )
                    ->toggleable(),

                TextColumn::make('feeAccount.account_name')
                    ->label('Akun Fee')
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->feeAccount->account_code . ' - ' . $state
                    )
                    ->toggleable(),

                TextColumn::make('fintech_account_code')
                    ->label('Kode Fintech')
                    ->badge()
                    ->icon('heroicon-o-clipboard-document')
                    ->copyable()
                    ->copyMessage('Kode fintech berhasil disalin')
                    ->searchable(),

                TextColumn::make('fintech_account_name')
                    ->label('Nama Fintech')
                    ->searchable(),

                TextColumn::make('merchant_id')
                    ->label('Merchant ID')
                    ->icon('heroicon-o-clipboard-document')
                    ->placeholder('-')
                    ->copyable()
                    ->copyMessage('merchant_id berhasil disalin')
                    ->searchable(),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Tidak Aktif'),

                TextColumn::make('initial_balance')
                    ->label('Saldo Initial')
                    ->money('idr')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->placeholder('-')
                    ->wrap(),
            ])
            ->headerActions([
                Action::make('ImportFintechAccounts')
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
                                new FintechAccountTemplateExport, 
                                'template_import_akun_fintech.xlsx'
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
                            ->directory('imports/fintech_accounts')
                            ->getUploadedFileNameForStorageUsing(
                                fn ($file) => 'Akun_Fintech_' . now()->format('Ymd_His') . '_' . $file->getClientOriginalName()
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
                            'importer' => FintechAccountsImport::class,
                            'file_name' => $fileName,
                            'file_path' => $storedFilePath,
                            'total_rows' => 0,
                            'successful_rows' => 0,
                            'processed_rows' => 0
                        ]);

                        // 4. Masukkan proses import ke Queue (Background)
                        Excel::queueImport(
                            new FintechAccountsImport($importRecord->id, Auth::id()), 
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

                ExportAction::make('ExportFintechAccounts')
                            ->label('Export')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('success')
                            ->exporter(FintechAccountExporter::class),
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
