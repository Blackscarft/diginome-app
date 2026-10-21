<?php

namespace App\Filament\Resources\AccountTransactions\Schemas;

use App\Models\BankAccount;
use App\Models\CashAccount;
use App\Models\FintechAccount;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->description('Informasi akun bank yang terkait dengan mutasi.')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('is_matched')
                            ->label('Status Rekonsilisasi')
                            ->formatStateUsing(fn ($state) => $state ? 'Matched' : 'Unmatched')
                            ->badge()
                            ->colors([
                                'success' => true,
                                'danger' => false,
                            ]),

                        TextEntry::make('transactionType.type_name')
                            ->label('Tipe Transaksi')
                            ->badge(),

                        TextEntry::make('accountable_type')
                            ->label('Jenis Akun')
                            ->formatStateUsing(
                                fn (?string $state) => match ($state) {
                                    BankAccount::class => 'Bank',
                                    CashAccount::class => 'Kas',
                                    FintechAccount::class => 'Fintech',
                                    default => class_basename($state ?? '-'),
                                }
                            )
                            ->placeholder('-'),

                        TextEntry::make('accountable_name')
                            ->label('Nama Akun')
                            ->state(function ($record) {
                                $accountable = $record->accountable;

                                if (! $accountable) {
                                    return '-';
                                }

                                return match (class_basename($accountable)) {
                                    'BankAccount' => $accountable->bank_name . ' - ' . $accountable->account_number,
                                    'CashAccount' => $accountable->cash_account_name,
                                    'FintechAccount' => $accountable->fintech_account_name . ' - ' . $accountable->merchant_id,
                                    default => '-',
                                };
                            })
                            ->placeholder('-'),
                    ]),

                Section::make('Informasi Transaksi')
                    ->description('Detail transaksi dan status pencocokan.')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->date('d M Y')
                            ->color('primary'),

                        TextEntry::make('transaction_time')
                            ->label('Waktu Transaksi')
                            ->time('H:i:s')
                            ->placeholder('--:--:--'),
        
                        TextEntry::make('description')
                            ->label('Deskripsi'),
        
                        TextEntry::make('debit')
                            ->label('Debit')
                            ->numeric(decimalPlaces: 2),
        
                        TextEntry::make('credit')
                            ->label('Kredit')
                            ->numeric(decimalPlaces: 2),
        
                        TextEntry::make('fee_amount')
                            ->label('Biaya')
                            ->numeric(decimalPlaces: 2),
        
                        TextEntry::make('reference')
                            ->label('Referensi')
                            ->badge()
                            ->copyable()
                            ->icon('heroicon-o-clipboard-document')
                            ->color('info')
                            ->placeholder('-'),
                    ])
            ]);
    }

}
