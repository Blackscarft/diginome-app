<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use App\Models\Account;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BankAccountForm
{   
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun Bank')
                    ->description('Detail Identitas dan nomor rekening')
                    ->schema([
                        Select::make('account_id')
                            ->label('Akun CoA')
                            ->relationship(
                                name: 'account', // Sesuaikan nama relasi di Model (misal: chartOfAccount atau account)
                                titleAttribute: 'name',
                                // Hanya tampilkan CoA bertipe Kas/Bank (Aset) dan yang bukan berupa Header/Parent
                                modifyQueryUsing: fn ($query) => $query->where('is_postable', true)
                                                                        ->where('code', 'like', '1-1%')
                            )
                            ->getOptionLabelFromRecordUsing(fn (Account $record) => "{$record->code} - {$record->name}")
                            ->searchable(['code', 'name']) // Bisa dicari berdasarkan kode atau nama akun
                            ->preload()                   // Memuat data di awal agar respon UI cepat
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('bank_code')
                                ->label('Kode Bank')
                                ->required()
                                ->maxLength(20)
                                ->placeholder('Contoh: BS-SM-042'),

                        TextInput::make('bank_name')
                                ->label('Nama Bank')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Contoh: Bank BSI'),

                        TextInput::make('account_number')
                                ->label('Nomor Rekening')
                                ->nullable()
                                ->maxLength(50)
                                ->placeholder('Contoh: 1234567891011234'),

                        TextInput::make('account_holder')
                                ->label('Nama Pemilik Rekening')
                                ->required()
                                ->placeholder('Contoh: PT. Digimone')
                                ->maxLength(255),

                        TextInput::make('initial_balance')
                                ->label('Saldo Awal')
                                ->numeric()
                                ->required()
                                ->default(0),
                        
                        ToggleButtons::make('is_active')
                            ->label('Aktif?')
                            ->boolean('Ya', 'Tidak')
                            ->inline()
                            ->required()
                            ->default(true),
                        
                        Textarea::make('notes')
                                ->label('Catatan')
                                ->nullable()
                    ])
            ]);
    }
}
