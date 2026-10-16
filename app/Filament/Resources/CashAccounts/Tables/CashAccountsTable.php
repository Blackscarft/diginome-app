<?php

namespace App\Filament\Resources\CashAccounts\Tables;

use App\Exports\CashAccountTemplateExport;
use App\Filament\Exports\CashAccountExporter;
use App\Imports\CashAccountsImport;
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

class CashAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Daftar Akun Kas')
            ->description('Kelola data akun kas perusahaan di sini.') 
            ->columns([
                TextColumn::make('account.account_code')
                    ->label('Kode Akun')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cash_account_code')
                    ->label('Kode Kas')
                    ->badge()
                    ->icon('heroicon-o-clipboard-document')
                    ->copyable()
                    ->copyMessage('Kode kas berhasil disalin')
                    ->searchable(),
                
                TextColumn::make('cash_account_name')
                    ->label('Nama kas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.branch_name')
                    ->label('Cabang'),
                
                TextColumn::make('custodian_name')
                    ->label('Penanggung Jawab'),

                TextColumn::make('maximum_balance')
                    ->label('Saldo Maksimum')
                    ->money('idr'),
                
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
                Action::make('ImportCashAccounts')
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
                                new CashAccountTemplateExport, 
                                'template_import_akun_kas.xlsx'
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
                            ->directory('imports/cash_accounts')
                            ->getUploadedFileNameForStorageUsing(
                                fn ($file) => 'Akun_Kas_' . now()->format('Ymd_His') . '_' . $file->getClientOriginalName()
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
                            'importer' => CashAccountsImport::class,
                            'file_name' => $fileName,
                            'file_path' => $storedFilePath,
                            'total_rows' => 0,
                            'successful_rows' => 0,
                            'processed_rows' => 0
                        ]);

                        // 4. Masukkan proses import ke Queue (Background)
                        Excel::queueImport(
                            new CashAccountsImport($importRecord->id, Auth::id()), 
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

                ExportAction::make('ExportCashAccounts')
                            ->label('Export')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('success')
                            ->exporter(CashAccountExporter::class),
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
