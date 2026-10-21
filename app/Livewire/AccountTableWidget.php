<?php

namespace App\Livewire;

use App\Exports\AccountTemplateExport;
use App\Filament\Exports\AccountExporter;
use App\Filament\Pages\ManageAccounts;
use App\Imports\AccountImport;
use App\Models\Account;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;

class AccountTableWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->query(fn (): Builder => Account::query())
            ->columns([
                TextColumn::make('account_code')
                    ->label('Kode Akun')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('account_name')
                    ->label('Nama Akun')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->placeholder('-'),

                TextColumn::make('parent.account_name')
                    ->label('Induk (Parent)')
                    ->default('- Root -'),

                TextColumn::make('normal_balance')
                    ->label('Saldo Normal')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'debit' => 'warning',
                        'credit' => 'success',
                        default => 'gray',
                    }),
                
                IconColumn::make('is_postable')
                    ->label('Dapat Posting?')
                    ->boolean(),
                
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Tidak Aktif')
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        default => 'danger',
                    })
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('importAccount')
                    ->label('Import')
                    ->color('info')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->modalHeading('Import Akun')
                    ->modalDescription(
                        'Upload file Excel (.xlsx) yang berisi data akun. Pastikan format kolom sesuai dengan template yang disediakan.'
                    )
                    ->extraModalFooterActions([
                        Action::make('downloadTemplate')
                            ->label('Download Template')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('warning')
                            ->action(fn () => Excel::download(
                                new AccountTemplateExport, 
                                'template_import_account.xlsx'
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
                            ->directory('imports/accounts')
                            ->getUploadedFileNameForStorageUsing(
                                fn ($file) => 'Account_' . now()->format('Ymd_His') . '_' . $file->getClientOriginalName()
                            )
                            ->required()
                            ->maxSize(1024), // 1 MB
                    ])
                    ->action(function (array $data) {

                    // 1. Ambil relative path tempat file disimpan di storage
                    $storedFilePath = $data['file_excel'];

                    // 2. Ambil nama file asli (misal: "data_mutasi_januari.xlsx")
                    // Karena FileUpload menyimpan file di disk, kita pakai pathinfo() untuk mengambil nama aslinya
                    $fileName = basename($storedFilePath);

                    // 3. Simpan data ke Tabel Imports
                    $importRecord = Import::create([
                        'user_id' => Auth::id(),
                        'importer' => AccountImport::class,
                        'file_name' => $fileName,
                        'file_path' => $storedFilePath,
                        'total_rows' => 0,
                        'successful_rows' => 0,
                        'processed_rows' => 0
                    ]);

                    // 4. Masukkan proses import ke Queue (Background)
                    Excel::queueImport(
                        new AccountImport($importRecord->id, Auth::id()), 
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
                    

                ExportAction::make()
                        ->exporter(AccountExporter::class)
                        ->label('Export')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
            ])
            ->recordActions([
                EditAction::make()
                    ->schema(ManageAccounts::getAccountFormSchema())
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
