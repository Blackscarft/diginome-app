<?php

namespace App\Filament\Resources\AccountTransactions\Schemas;

use App\Models\BankAccount;
use App\Models\CashAccount;
use App\Models\FintechAccount;
use App\Models\TransactionType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Icon;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class AccountTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tambah Mutasi Kas atau Bank')
                    ->description('Detail Mutasi Kas atau Bank')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('transaction_types')
                        ->label('Jenis Transaksi')
                        ->options(
                            fn () => TransactionType::query()
                                ->orderBy('type_name')
                                ->pluck('type_name', 'id')
                                ->all()
                        )
                        ->required()
                        ->searchable(),

                        ToggleButtons::make('accountable_type')
                                ->label('Jenis Akun')
                                ->options([
                                    BankAccount::class => 'Bank',
                                    CashAccount::class => 'Kas',
                                    FintechAccount::class => 'Fintech',
                                ])
                                ->icons([
                                    BankAccount::class => Heroicon::OutlinedBuildingLibrary,
                                    CashAccount::class => Heroicon::OutlinedBanknotes,
                                    FintechAccount::class => Heroicon::OutlinedQrCode,
                                ])
                                ->inline()
                                ->live()
                                ->afterStateUpdated(fn (Set $set) => $set('accountable_id', null))
                                ->required(),
                            
                        Select::make('accountable_id')
                            ->label('Nama Akun')
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
                            ->searchable()
                            ->live()
                            ->disabled(fn (Get $get) => blank($get('accountable_type')))
                            ->required(),

                        TextInput::make('debit')
                                    ->label('Debit')
                                    ->numeric()
                                    ->required()
                                    ->default(0),

                        TextInput::make('credit')
                                    ->label('Credit')
                                    ->numeric()
                                    ->required()
                                    ->default(0),
                                    
                        TextInput::make('fee_amount')
                                    ->label('Biaya')
                                    ->numeric()
                                    ->default(0)
                                    ->belowContent([
                                        Icon::make(Heroicon::InformationCircle),
                                        'jika ada biaya layanan dalam transaksi.',
                                        // Action ,
                                    ]),

                        DatePicker::make('transaction_date')
                                    ->label('Tanggal Transaksi')
                                    ->required(),
                        
                        TimePicker::make('transaction_time')
                                    ->label('Waktu Transaksi')
                                    ->native(false)
                                    ->format('H:i:s')
                                    ->displayFormat('H:i:s')
                                    ->seconds(true)
                                    ->required(),

                        TextInput::make('reference')
                                    ->label('Nomor Referensi')
                                    ->maxLength(255),

                        TextInput::make('description')
                                    ->label('Deskripsi')
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->required()
                                    ->default('-'),

                        ToggleButtons::make('is_matched')
                            ->label('Status Rekonsiliasi')
                            ->boolean('Match', 'Unmatch')
                            ->inline()
                            ->required()
                            ->default(false)
                            ->disabled(),
                    ])
            ]);
    }
}
