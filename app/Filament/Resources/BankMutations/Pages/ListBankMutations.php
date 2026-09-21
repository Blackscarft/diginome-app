<?php

namespace App\Filament\Resources\BankMutations\Pages;

use App\Exports\BankMutationsTemplateExport;
use App\Filament\Resources\BankMutations\BankMutationResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use App\Filament\Imports\BankMutationImporter; // Import Importer
use App\Imports\BankMutationsImport;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

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
                        ->storeFiles(false) // Tidak perlu disimpan permanen di storage
                        ->required()
                        ->maxSize(10240), // Maksimal ukuran file 10MB
                ])
                ->action(function (array $data): void {
                    try {
                        // Jalankan Laravel Excel Import
                        Excel::import(new BankMutationsImport, $data['file_excel']);

                        Notification::make()
                            ->title('Import Berhasil')
                            ->body('Data mutasi bank berhasil di-import dari file Excel.')
                            ->success()
                            ->send();
                    } catch (\Throwable $th) {
                        Notification::make()
                            ->title('Import Gagal')
                            ->body('Terjadi kesalahan: ' . $th->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            CreateAction::make(),
        ];
    }
}
