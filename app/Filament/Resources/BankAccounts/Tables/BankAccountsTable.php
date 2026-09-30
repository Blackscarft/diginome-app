<?php

namespace App\Filament\Resources\BankAccounts\Tables;

use App\Exports\BankAccountsTemplateExport;
use App\Filament\Exports\BankAccountExporter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Imports\Models\Import;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Maatwebsite\Excel\Facades\Excel; 
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Imports\BankAccountsImport;

class BankAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Daftar Akun Bank')
            ->description('Kelola data akun bank perusahaan di sini.') 
            ->columns([
                TextColumn::make('account.code')
                    ->label('Kode Akun')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bank_code')
                    ->label('Kode Bank')
                    ->badge()
                    ->searchable(),
                
                TextColumn::make('bank_name')
                    ->label('Nama Bank')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('account_number')
                    ->label('No. Rekening')
                    ->icon('heroicon-o-clipboard-document')
                    ->copyable()
                    ->copyMessage('Nomor rekening berhasil disalin')
                    ->searchable(),
                
                TextColumn::make('account_holder')
                    ->label('Atas Nama'),
                
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Tidak Aktif'),

                TextColumn::make('initial_balance')
                    ->label('Saldo Awal')
                    ->money('idr')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->placeholder('-')
                    ->wrap(),

                
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
                                new BankAccountsTemplateExport, 
                                'template_import_akun_bank.xlsx'
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
                            ->directory('imports/bank_accounts')
                            ->getUploadedFileNameForStorageUsing(
                                fn ($file) => 'Akun_Bank_' . now()->format('Ymd_His') . '_' . $file->getClientOriginalName()
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
                        'importer' => BankAccountsImport::class,
                        'file_name' => $fileName,
                        'file_path' => $storedFilePath,
                        'total_rows' => 0,
                        'successful_rows' => 0,
                        'processed_rows' => 0
                    ]);

                    // 4. Masukkan proses import ke Queue (Background)
                    Excel::queueImport(
                        new BankAccountsImport($importRecord->id, Auth::id()), 
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

                // CreateAction::make(),

                ExportAction::make('ExportBankAccounts')
                            ->label('Export')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('success')
                            ->exporter(BankAccountExporter::class),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Akun')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->defaultGroup(
                Group::make('account.name') // Sesuai nama relasi & atribut nama di model CoA
                    ->label('Akun CoA')
                    ->getTitleFromRecordUsing(fn ($record) => "{$record->account?->code} - {$record->account?->name}")
                    ->collapsible() // Mengizinkan grup untuk di-collapse (buka-tutup)
            )
            ->recordActions([
                EditAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
