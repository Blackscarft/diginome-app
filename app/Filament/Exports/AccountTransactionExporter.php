<?php

namespace App\Filament\Exports;

use App\Models\AccountTransaction;
use App\Models\BankAccount;
use App\Models\CashAccount;
use App\Models\FintechAccount;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class AccountTransactionExporter extends Exporter
{
    protected static ?string $model = AccountTransaction::class;

    public function configureXlsxWriterAfterOpen(Writer $writer): Writer
    {
        $options = $this->getOptions();

        $startDate = $options['start_date'] ?? '-';
        $endDate = $options['end_date'] ?? '-';

        $accountType = match ($options['accountable_type'] ?? null) {
            BankAccount::class => 'Bank',
            CashAccount::class => 'Kas',
            FintechAccount::class => 'Fintech',
            default => 'Semua Jenis Akun',
        };

        $accountName = 'Semua Akun';

        if (! empty($options['accountable_id']) && ! empty($options['accountable_type'])) {
            $account = match ($options['accountable_type']) {
                BankAccount::class => BankAccount::find($options['accountable_id']),
                CashAccount::class => CashAccount::find($options['accountable_id']),
                FintechAccount::class => FintechAccount::find($options['accountable_id']),
                default => null,
            };

            if ($account) {
                $accountName = match (true) {
                    $account instanceof BankAccount =>
                        $account->bank_name . ' - ' . ($account->account_number ?? '-'),

                    $account instanceof CashAccount =>
                        $account->cash_account_name,

                    $account instanceof FintechAccount =>
                        $account->fintech_account_name . ' - ' . ($account->merchant_id ?? '-'),

                    default => 'Semua Akun',
                };
            }
        }

        $writer->addRow(Row::fromValues(['INFORMASI FILTER']));
        $writer->addRow(Row::fromValues(['Dari Transaksi Tanggal', $startDate]));
        $writer->addRow(Row::fromValues(['Sampai Transaksi Tanggal', $endDate]));
        $writer->addRow(Row::fromValues(['Jenis Akun', $accountType]));
        $writer->addRow(Row::fromValues(['Nama Akun', $accountName]));
        $writer->addRow(Row::fromValues(['Tanggal Ekspor', date('Y-m-d H:i:s')]));
        $writer->addRow(Row::fromValues([]));

        return $writer;
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Fieldset::make('Filter')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Dari Transaksi Tanggal')
                            ->native(false)
                            ->columnSpanFull()
                            ->required(),
            
                        DatePicker::make('end_date')
                            ->label('Sampai Transaksi Tanggal')
                            ->columnSpanFull()
                            ->native(false)
                            ->required()
                            ->afterOrEqual('start_date'),

                        Select::make('accountable_type')
                            ->label('Jenis Akun')
                            ->columnSpanFull()
                            ->options([
                                BankAccount::class => 'Bank',
                                CashAccount::class => 'Kas',
                                FintechAccount::class => 'Fintech',
                            ])
                            ->placeholder('Semua Jenis Akun')
                            ->default(null)
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('accountable_id', null)),

                        Select::make('accountable_id')
                            ->label('Nama Akun')
                            ->columnSpanFull()
                            ->options(function (Get $get): array {
                                return match ($get('accountable_type')) {
                                    BankAccount::class => BankAccount::query()
                                        ->orderBy('bank_name')
                                        ->get()
                                        ->mapWithKeys(fn ($account) => [
                                            $account->id => $account->bank_name
                                                . ' - '
                                                . ($account->account_number ?? '-'),
                                        ])
                                        ->all(),

                                    CashAccount::class => CashAccount::query()
                                        ->orderBy('cash_account_name')
                                        ->pluck('cash_account_name', 'id')
                                        ->all(),

                                    FintechAccount::class => FintechAccount::query()
                                        ->orderBy('fintech_account_name')
                                        ->get()
                                        ->mapWithKeys(fn ($account) => [
                                            $account->id => $account->fintech_account_name
                                                . ' - '
                                                . ($account->merchant_id ?? '-'),
                                        ])
                                        ->all(),

                                    default => [],
                                };
                            })
                            ->placeholder(fn (Get $get) => blank($get('accountable_type'))
                                ? 'Pilih jenis akun terlebih dahulu'
                                : 'Semua akun')
                            ->searchable()
                            ->live()
                            ->disabled(fn (Get $get) => blank($get('accountable_type')))
                            ->dehydrated()
                    ])
        ];
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('transactionType.type_name')
                ->label('Tipe Transaksi'),
            ExportColumn::make('accountable_type')
                ->label('Jenis Akun')
                ->formatStateUsing(fn ($state) => match ($state) {
                    \App\Models\BankAccount::class => 'Bank',
                    \App\Models\CashAccount::class => 'Kas',
                    \App\Models\FintechAccount::class => 'Fintech',
                    default => $state,
                }),
            ExportColumn::make('accountable_id')
                ->label('Nama Akun')
                ->state(function ($record) {
                    $account = $record->accountable;

                    if (! $account) {
                        return '-';
                    }

                    return match (true) {
                        $account instanceof \App\Models\BankAccount =>
                            $account->bank_name,

                        $account instanceof \App\Models\CashAccount =>
                            $account->cash_account_name,

                        $account instanceof \App\Models\FintechAccount =>
                            $account->fintech_account_name,

                        default => '-',
                    };
                }),
            ExportColumn::make('accountable_id')
                ->label('Rekening')
                ->state(function ($record) {
                    $account = $record->accountable;

                    if (! $account) {
                        return '-';
                    }

                    return match (true) {
                        $account instanceof \App\Models\BankAccount =>
                            $account->account_number,

                        $account instanceof \App\Models\CashAccount =>
                            $account->cash_account_code,

                        $account instanceof \App\Models\FintechAccount =>
                            $account->merchant_id,

                        default => '-',
                    };
                }),
                ExportColumn::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->formatStateUsing(fn ($state) => $state
                        ? \Carbon\Carbon::parse($state)->format('Y-m-d')
                        : null),
                ExportColumn::make('transaction_time')
                    ->label('Waktu Transaksi')
                    ->formatStateUsing(fn ($state) => $state ?: null),
                ExportColumn::make('description')
                    ->label('Deskripsi'),
                ExportColumn::make('reference')
                    ->label('Nomor Referensi')
                    ->formatStateUsing(fn ($state) => (string) $state),
                ExportColumn::make('debit')
                    ->label('Debit')
                    ->formatStateUsing(fn ($state) => (float) $state),
                ExportColumn::make('credit')
                    ->label('Kredit')
                    ->formatStateUsing(fn ($state) => (float) $state),
                ExportColumn::make('fee_amount')
                    ->label('Biaya Layanan')
                    ->formatStateUsing(fn ($state) => (float) $state),
                ExportColumn::make('is_matched')
                    ->label('Status Rekonsiliasi')
                    ->formatStateUsing(fn ($state) => $state ? 'matched' : 'unmatched'),
                ExportColumn::make('created_at'),
                ExportColumn::make('updated_at')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your account transaction export has completed and ' . Str::of('row')->counted($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Str::of('row')->counted($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
