<?php

namespace App\Filament\Resources\BankMutations\Pages;

use App\Exports\BankMutationsTemplateExport;
use App\Filament\Resources\BankMutations\BankMutationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Imports\Models\Import; // Model Import 
use App\Imports\BankMutationsImport;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

// Import Action default Filament (hanya bisa csv)
use Filament\Actions\ImportAction;
use App\Filament\Imports\BankMutationImporter; // Import Importer

class ListBankMutations extends ListRecords
{
    protected static string $resource = BankMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ImportAction::make()
            //     ->importer(BankMutationImporter::class)
            //     ->label('Import Mutasi Bank')
            //     ->icon('heroicon-o-document-arrow-up')
            //     ->color('success'),
            
            Action::make('importExcel')
                ->label('Import Mutasi Bank (.xlsx)')
                ->icon('heroicon-o-document-arrow-up')
                ->modalDescription(
                    'Upload file Excel (.xlsx) yang berisi data mutasi bank. Pastikan format kolom sesuai dengan template yang disediakan.'
                )
                // Menambahkan tombol kustom di footer modal untuk download template
                ->extraModalFooterActions([
                    Action::make('downloadTemplate')
                        ->label('Download Template')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('warning')
                        ->action(fn () => Excel::download(
                            new BankMutationsTemplateExport, 
                            'template_import_mutasi_bank.xlsx'
                        )),
                ])
                ->color('success')
                ->schema([
                    FileUpload::make('file_excel')
                        ->label('Pilih file Excel (.xlsx)')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->storeFiles(true)
                        ->directory('imports/bank_mutations')
                        ->required()
                        ->maxSize(10240), // Maksimal ukuran file 10MB
                ])
                ->action(function (array $data): void {
                    // 1. Ambil relative path tempat file disimpan di storage
                    $storedFilePath = $data['file_excel'];

                    // 2. Ambil nama file asli (misal: "data_mutasi_januari.xlsx")
                    // Karena FileUpload menyimpan file di disk, kita pakai pathinfo() untuk mengambil nama aslinya
                    $originalFileName = basename($storedFilePath);

                    // 3. Format Custom Name (Gunakan tanggal/timestamp agar tidak ada ekstensi ganda)
                    $customFileName = 'Mutasi_Bank_' . now()->format('Ymd_His') . '_' . $originalFileName;
                    
                    // 4. Simpan data ke Tabel Imports
                    $importRecord = Import::create([
                        'user_id' => Auth::id(),
                        'importer' => BankMutationsImport::class,
                        'file_name' => $customFileName,
                        'file_path' => $storedFilePath,
                        'total_rows' => 0,
                        'successful_rows' => 0,
                        'processed_rows' => 0
                    ]);

                    // 5. Masukkan proses import ke Queue (Background)
                    Excel::queueImport(
                        new BankMutationsImport($importRecord->id, Auth::id()), 
                        $storedFilePath, 
                        'local' // Disk penyimpanan Laravel (default: storage/app)
                    );

                    Notification::make()
                        ->title('Import Dimulai di Background')
                        ->body('File Anda sedang diproses di antrean server. Anda dapat melanjutkan pekerjaan lain.')
                        ->info()
                        ->send();
                }),

            CreateAction::make(),
        ];
    }
}
