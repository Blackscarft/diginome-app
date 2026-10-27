<?php

namespace App\Filament\Resources\AccountTransactions\Pages;

use App\Exports\AccountTransactionTemplateExport;
use App\Filament\Resources\AccountTransactions\AccountTransactionResource;
use App\Imports\PreviewAccountTransactionImport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;

use Illuminate\Support\Collection;

use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\View;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Validation\ValidationException;
use Throwable;

class ImportAccountTransactions extends Page Implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = AccountTransactionResource::class;

    protected string $view = 'filament.resources.account-transactions.pages.import-account-transactions';

    // Variabel untuk menampung state form
    public ?array $data = [];
    
    public array $previewRows = [];

    public int $totalRows = 0;

    public array $allPreviewRows = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function submit()
    {
        $formData = $this->form->getState();
    }

    private function formatExcelDate(?String $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            // Jika tanggal berupa angka serial Excel
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject((float) $value)
                    ->format('Y-m-d');
            }

            // Jika tanggal sudah berupa string
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function formatExcelTime(?String $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $seconds = (int) round(((float) $value) * 86400) % 86400;

            return gmdate('H:i:s', $seconds);
        }

        try {
            return \Carbon\Carbon::parse((string) $value)->format('H:i:s');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    public function loadPreview(
        ?TemporaryUploadedFile $file
    ): void {
        $this->previewRows = [];
        $this->totalRows = 0;

        if (! $file) {
            return;
        }

        try {
            $import = new PreviewAccountTransactionImport();
            
            $import->onlySheets('Transactions');

            Excel::import($import, $file->getRealPath());

            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheetNames = $spreadsheet->getSheetNames();

            if (!in_array('Transactions', $sheetNames, true)) {
                Notification::make()
                    ->title('Gagal membaca file Excel')
                    ->body(
                        'Sheet "Transactions" tidak ditemukan. Sheet yang tersedia: '
                        . implode(', ', $sheetNames)
                    )
                    ->danger()
                    ->persistent()
                    ->send();

                return;
            }

            $rows = $import->transactionSheet->rows;

            $this->totalRows = $rows->count();

            // Menyimpan semua baris preview
            $this->allPreviewRows = $rows
                        ->map(function ($row) {
                            $account = trim($row['account_name'] ?? '');
                            $partAccount = explode(' - ', $account, 2);

                            $type = trim($row['transaction_type'] ?? '');
                            $partType = explode(' - ', $type, 2);

                            return [
                                'transaction_code' => $partType[0] ?? null,
                                'transaction_type' => $partType[1] ?? null,
                                'account_type' => $row['account_type'] ?? null,
                                'account_name' => $partAccount[0] ?? null,
                                'account_number' => $partAccount[1] ?? null,
                                'transaction_date' => $this->formatExcelDate(
                                    $row['transaction_date'] ?? null
                                ),
                                'transaction_time' => $this->formatExcelTime(
                                    $row['transaction_time'] ?? null
                                ),
                                'reference' => $row['reference'] ?? null,
                                'description' => $row['description'] ?? null,
                                'debit' => $row['debit'] ?? 0,
                                'credit' => $row['credit'] ?? 0,
                                'fee_amount' => $row['fee_amount'] ?? 0,
                            ];
                        })
                        ->values()
                        ->toArray();

            // Mengambil 100 baris pertama
            $this->previewRows = array_slice($this->allPreviewRows, 0, 100);

            Notification::make()
                ->title('Preview berhasil dibuat')
                ->body("Ditemukan {$this->totalRows} baris transaksi.")
                ->success()
                ->send();

        } catch (Throwable $e) {
            $this->previewRows = [];
            $this->totalRows = 0;

            report($e);

            Notification::make()
                ->title('Gagal membaca file Excel')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    public function getImportSummary(): array
    {
        $rows = collect(
            $this->allPreviewRows ?? $this->previewRows
        );

        return [
            'total_transactions' => $this->totalRows,

            'total_accounts' => $rows
                ->pluck('account_number')
                ->filter()
                ->unique()
                ->count(),

            'total_debit' => $rows->sum(
                fn ($row) => (float) ($row['debit'] ?? 0)
            ),

            'total_credit' => $rows->sum(
                fn ($row) => (float) ($row['credit'] ?? 0)
            ),

            'total_fee' => $rows->sum(
                fn ($row) => (float) ($row['fee_amount'] ?? 0)
            ),

            'date_from' => $rows
                ->pluck('transaction_date')
                ->filter()
                ->min(),

            'date_until' => $rows
                ->pluck('transaction_date')
                ->filter()
                ->max(),
        ];
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
                                    ->storeFiles(false)
                                    // ->directory('imports/account_transactions')
                                    // ->getUploadedFileNameForStorageUsing(
                                    //     fn ($file) => 'Mutasi_' . now()->format('Ymd_His') . '_' . $file->getClientOriginalName()
                                    // )
                                    // ->live()
                                    // ->afterStateUpdated(function (
                                    //     ?TemporaryUploadedFile $state
                                    // ): void {
                                    //     $this->loadPreview($state);
                                    // })
                                    ->maxSize(10240)
                                    ->afterStateUpdated(function ($state): void {
                                        if (! $state) {
                                            return;
                                        }

                                        Notification::make()
                                            ->title('Upload selesai')
                                            ->body('File Excel berhasil diunggah. Klik Preview Import untuk melanjutkan.')
                                            ->success()
                                            ->send();
                                    })
                                    ->helperText('Upload file Excel. Maksimal 10 MB.'),
                    ])
                    ->footerActions([
                        Action::make('previewImport')
                            ->label('Preview Import')
                            ->color('success')
                            ->disabled(fn () => empty($this->data['file_excel']))
                            
                            ->action(function (): void {
                                $state = $this->form->getState();
                                $file = $state['file_excel'] ?? null;

                                if (!$file instanceof TemporaryUploadedFile) {
                                    Notification::make()
                                        ->title('File belum diupload')
                                        ->body('Silakan upload file Excel terlebih dahulu.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $this->loadPreview($file);
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    public function getFilteredPreviewRows(): Collection
    {
        $rows = collect($this->previewRows);

        $filters = $this->tableFilters['transaction_date'] ?? [];

        $from = $filters['from'] ?? null;
        $until = $filters['until'] ?? null;

        $accountNumber = $filters['account_number'] ?? null;

        return $rows
            ->filter(function (array $row) use ($from, $until, $accountNumber) {
                $date = $row['transaction_date'] ?? null;

                if (blank($date)) {
                    return false;
                }

                try {
                    $date = Carbon::parse($date)->toDateString();
                } catch (\Throwable $e) {
                    return false;
                }

                if ($from && $date < $from) {
                    return false;
                }

                if ($until && $date > $until) {
                    return false;
                }

                if (
                    $accountNumber !== null &&
                    (string) ($row['account_number'] ?? '') !== (string) $accountNumber
                ) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    public function cancelImport(): void
    {
        // Bersihkan seluruh data preview
        $this->previewRows = [];
        $this->allPreviewRows = [];
        $this->totalRows = 0;

        // Reset filter tabel
        $this->tableFilters = [];

        // Bersihkan file yang diunggah dari state form
        $this->form->fill([
            'file_excel' => null,
        ]);

        // Beri notifikasi
        Notification::make()
            ->title('Import dibatalkan')
            ->body('File, preview transaksi, filter, dan ringkasan berhasil dibersihkan.')
            ->success()
            ->send();
    }

    public function table(Table $table): Table
    {
        return $table
                ->records(fn () => collect($this->getFilteredPreviewRows()))
                ->headerActions([
                    Action::make('cancel')
                            ->label('Cancel')
                            ->color('danger')
                            ->visible(fn () => ! empty($this->previewRows))
                            ->action('cancelImport'),
                    
                    Action::make('Import')
                            ->label('Import')
                            ->color('info')
                            ->visible(fn () => ! empty($this->previewRows))
                            ->action('submit'), // Memanggil method submit() di file PHP Page Anda

                ])
                ->columns([
                    TextColumn::make('transaction_code')->label('Code Tipe Transaksi'),
                    TextColumn::make('transaction_type')
                            ->badge()
                            ->label('Tipe Transaksi'),
                    TextColumn::make('account_type')
                            ->badge()
                            ->label('Jenis Akun'),
                    TextColumn::make('account_name')->label('Nama Akun'),
                    TextColumn::make('account_number')->label('Nomor Akun'),
                    TextColumn::make('transaction_date')
                            ->date('Y-m-d')
                            ->label('Tanggal'),
                    TextColumn::make('transaction_time')->label('Waktu')->placeholder('null'),
                    TextColumn::make('reference')->label('Referensi')->placeholder('null'),
                    TextColumn::make('description')
                        ->wrap(true)
                        ->searchable()
                        ->label('Deskripsi'),
                    TextColumn::make('debit')->label('Debit'),
                    TextColumn::make('credit')->label('Kredit'),
                    TextColumn::make('fee_amount')->label('Biaya')->placeholder('0'),
                ])
                ->filters([
                    Filter::make('transaction_date')
                        ->label('Tanggal Transaksi')
                        ->schema([
                            DatePicker::make('from')
                                ->label('Dari tanggal'),

                            DatePicker::make('until')
                                ->label('Sampai tanggal'),

                            Select::make('account_number')
                                ->label('Nama Akun - Nomor Akun')
                                ->options(function (): array {
                                    return collect($this->previewRows)
                                        ->filter(fn (array $row) => filled($row['account_number'] ?? null))
                                        ->unique('account_number')
                                        ->mapWithKeys(fn (array $row) => [
                                            $row['account_number'] => (
                                                ($row['account_name'] ?? '-') . ' - ' .
                                                $row['account_number']
                                            ),
                                        ])
                                        ->all();
                                })
                                ->searchable()
                                ->placeholder('Semua akun')
                                ->native(false),

                        ])
                        ->indicateUsing(function (array $data): array {
                            $indicators = [];

                            if ($data['from'] ?? null) {
                                $indicators[] = 'Dari: ' . $data['from'];
                            }

                            if ($data['until'] ?? null) {
                                $indicators[] = 'Sampai: ' . $data['until'];
                            }

                            if ($data['account_number'] ?? null) {
                                $row = collect($this->previewRows)->first(
                                    fn (array $row) =>
                                        (string) ($row['account_number'] ?? '') ===
                                        (string) $data['account_number']
                                );

                                if ($row) {
                                    $indicators[] = \Filament\Tables\Filters\Indicator::make(
                                        ($row['account_name'] ?? '-') . ' - ' .
                                        $row['account_number']
                                    )->removeField('account_number');
                                }
                            }

                            return $indicators;
                        }),
                ]);
    }

    
    
    public function summary(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'sm' => 2,
                    'lg' => 3,
                ])
                    ->schema([
                        Section::make('Total Baris')
                            ->icon('heroicon-o-bars-arrow-down')
                            ->schema([
                                TextEntry::make('total_transaksi')
                                    ->label('Jumlah baris')
                                    ->state(fn () => number_format(
                                        $this->getImportSummary()['total_transactions'],
                                        0,
                                        ',',
                                        '.'
                                    ))
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold),
                            ]),

                        Section::make('Total Debit')
                            ->icon('heroicon-o-arrow-up-circle')
                            ->schema([
                                TextEntry::make('total_debit')
                                    ->label('Total debit')
                                    ->state(fn () => 'Rp ' . number_format(
                                        $this->getImportSummary()['total_debit'],
                                        2,
                                        ',',
                                        '.'
                                    ))
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold),
                            ]),

                        Section::make('Total Kredit')
                            ->icon('heroicon-o-arrow-down-circle')
                            ->schema([
                                TextEntry::make('total_kredit')
                                    ->label('Total kredit')
                                    ->state(fn () => 'Rp ' . number_format(
                                        $this->getImportSummary()['total_credit'],
                                        2,
                                        ',',
                                        '.'
                                    ))
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold),
                            ]),

                        Section::make('Total Biaya')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                TextEntry::make('total_biaya')
                                    ->label('Total biaya transaksi')
                                    ->state(fn () => 'Rp ' . number_format(
                                        $this->getImportSummary()['total_fee'],
                                        2,
                                        ',',
                                        '.'
                                    ))
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold),
                            ]),

                        Section::make('Total Akun')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                TextEntry::make('total_akun')
                                    ->label('Jumlah akun unik')
                                    ->state(fn () => number_format(
                                        $this->getImportSummary()['total_accounts'],
                                        0,
                                        ',',
                                        '.'
                                    ))
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold),
                            ]),

                        Section::make('Periode Transaksi')
                            ->icon('heroicon-o-calendar-days')
                            ->schema([
                                TextEntry::make('periode_transaksi')
                                    ->label('Tanggal awal - akhir')
                                    ->state(function () {
                                        $summary = $this->getImportSummary();

                                        $from = $summary['date_from']
                                            ? Carbon::parse($summary['date_from'])->format('d/m/Y')
                                            : '-';

                                        $until = $summary['date_until']
                                            ? Carbon::parse($summary['date_until'])->format('d/m/Y')
                                            : '-';

                                        return "{$from} - {$until}";
                                    })
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold),
                            ]),
                    ]),
            ]);
    }



}
