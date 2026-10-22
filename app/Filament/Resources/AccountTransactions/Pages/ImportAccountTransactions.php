<?php

namespace App\Filament\Resources\AccountTransactions\Pages;

use App\Exports\AccountTransactionTemplateExport;
use App\Filament\Resources\AccountTransactions\AccountTransactionResource;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Maatwebsite\Excel\Facades\Excel;

class ImportAccountTransactions extends Page
{
    protected static string $resource = AccountTransactionResource::class;

    protected string $view = 'filament.resources.account-transactions.pages.import-account-transactions';

    // Variabel untuk menampung state form
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function submit()
    {
        $formData = $this->form->getState();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('importForm')
                    ->heading('Import Account Transactions')
                    ->description('Pilih file Excel untuk diimport.')
                    ->afterHeader([
                        Action::make('downloadTemplate')
                            ->label('Download Template')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('warning')
                            ->action(fn () => Excel::download(
                                new AccountTransactionTemplateExport, 
                                'template_import_akun_transaksi.xlsx'
                            )),
                    ])
                    ->schema([
                        FileUpload::make('file_excel')
                                    ->label('File Transaksi')
                                    ->required()
                                    ->acceptedFileTypes([
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    ])
                                    ->storeFiles(true)
                                    ->directory('imports/account_transactions')
                                    ->getUploadedFileNameForStorageUsing(
                                        fn ($file) => 'Mutasi_' . now()->format('Ymd_His') . '_' . $file->getClientOriginalName()
                                    )
                                    ->required()
                                    ->maxSize(10240)
                                    ->helperText('Upload file Excel. Maksimal 10 MB.'),
                    ])
                    ->footerActions([
                        Action::make('submit')
                            ->label('Proses Import')
                            ->color('info')
                            // ->icon('heroicon-o-check')
                            ->action('submit'), // Memanggil method submit() di file PHP Page Anda
                    ]),
            ])
            ->statePath('data');
    }

}
