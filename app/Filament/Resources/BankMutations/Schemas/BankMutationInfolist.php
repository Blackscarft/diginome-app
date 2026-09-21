<?php

namespace App\Filament\Resources\BankMutations\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BankMutationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun Bank')
                    ->description('Informasi akun bank yang terkait dengan mutasi.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('bank_code')
                            ->label('Kode Bank')
                            ->placeholder('-'),

                        TextEntry::make('bank_name')
                            ->label('Nama Bank')
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
                            ->placeholder('-'),

                        TextEntry::make('transaction_time')
                            ->label('Waktu Transaksi')
                            ->time('H:i')
                            ->placeholder('-'),

                        TextEntry::make('reference')
                            ->label('No. Referensi')
                            ->placeholder('-'),

                        TextEntry::make('debit')
                            ->label('Debit')
                            ->money('IDR')
                            ->placeholder('Rp 0'),

                        TextEntry::make('credit')
                            ->label('Kredit')
                            ->money('IDR')
                            ->placeholder('Rp 0'),

                        IconEntry::make('is_matched')
                            ->label('Status Matched')
                            ->boolean(),
                    ]),

                Section::make('Relasi Jurnal & Catatan')
                    ->description('Informasi sumber dan keterangan mutasi.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('source_id')
                            ->label('Source ID')
                            ->placeholder('-'),

                        TextEntry::make('description')
                            ->label('Deskripsi / Keterangan Mutasi')
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->prose(),
                    ]),
            ]);
    }
}
