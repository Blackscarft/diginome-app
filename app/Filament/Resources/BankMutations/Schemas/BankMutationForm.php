<?php

namespace App\Filament\Resources\BankMutations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BankMutationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun Bank')
                    ->description('Pilih akun bank dan informasi kode bank pengirim/penerima.')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        // Select::make('bank_account_id')
                        //     ->label('Akun Bank')
                        //     // ->relationship('journal', 'id')
                        //     ->searchable()
                        //     ->preload()
                        //     ->nullable()
                        //     ->placeholder('Pilih Akun Bank (jika ada)'),

                        TextInput::make('bank_code')
                            ->label('Kode Bank')
                            ->required()
                            ->maxLength(25),

                        TextInput::make('bank_name')
                            ->label('Nama Bank')
                            ->placeholder('Contoh: Bank BSI')
                            ->required()
                            ->maxLength(255),
                    ]),
                
                Section::make('Informasi Transaksi')
                    ->description('Nominal mutasi, tanggal, serta status pencocokan (rekon).')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->default(now())
                            ->native(false)
                            ->required(),
                        
                        TimePicker::make('transaction_time')
                            ->label('Waktu Transaksi')
                            ->seconds(false)
                            ->time(),
                        
                        TextInput::make('reference')
                            ->label('No. Referensi')
                            ->maxLength(255)
                            ->placeholder('Contoh: TRX12345678'),
        
                        TextInput::make('debit')
                            ->label('Debit (Uang Out / DB)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->required()
                            ->live(onBlur: true),
        
                        TextInput::make('credit')
                            ->label('Kredit (Uang In / CR)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->required()
                            ->live(onBlur: true),
                        
                        Toggle::make('is_matched')
                            ->label('Status Matched')
                            ->helperText('Tandai jika mutasi ini sudah dicocokkan dengan jurnal')
                            ->default(false)
                            ->inline(false),
                    ]),

                    Section::make('Relasi Jurnal & Catatan')
                    ->schema([
                        // Select::make('journal_id')
                        //     ->label('Jurnal Terkait')
                        //     ->relationship('journal', 'id')
                        //     ->searchable()
                        //     ->preload()
                        //     ->nullable()
                        //     ->placeholder('Pilih Jurnal (jika ada)'),

                        TextInput::make('source_id')
                            ->label('Source ID')
                            ->numeric()
                            ->nullable()
                            ->placeholder('ID Sumber Eksternal'),

                        Textarea::make('description')
                            ->label('Deskripsi / Keterangan Mutasi')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Masukkan berita transaksi / keterangan mutasi...'),
                    ])->columns(2),

            ]);
    }
}
