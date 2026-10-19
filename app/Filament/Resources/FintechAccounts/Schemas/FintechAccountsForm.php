<?php

namespace App\Filament\Resources\FintechAccounts\Schemas;

use App\Models\Account;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FintechAccountsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun Fintech')
                    ->description('Detail Identitas Financial Technology')
                    ->schema([
                        Select::make('account_id')
                            ->label('Akun CoA')
                            ->options(
                                Account::query()
                                    ->where('account_code', 'like', '1-16%')
                                    ->where('is_postable', true)
                                    ->get()
                                    ->mapWithKeys(fn ($record) => [
                                        $record->id => "{$record->account_code} - {$record-> account_name}",
                                    ])
                                    ->all()
                            )
                            ->searchable()
                            ->required(),

                        Select::make('fee_account_id')
                            ->label('Akun Fee CoA')
                            ->options(
                                Account::query()
                                    ->where('account_code', 'like', '5-2%')
                                    ->where('is_postable', true)
                                    ->get()
                                    ->mapWithKeys(fn ($record) => [
                                        $record->id => "{$record->account_code} - {$record-> account_name}",
                                    ])
                                    ->all()
                            )
                            ->searchable()
                            ->required(),

                        TextInput::make('fintech_account_code')
                                ->label('Kode Fintech')
                                ->required()
                                ->maxLength(20)
                                ->placeholder('Contoh: MDT'),
                        
                        TextInput::make('fintech_account_name')
                                ->label('Nama Fintech')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Contoh: Midtrans'),

                        TextInput::make('merchant_id')
                                ->label('Merchant ID')
                                ->maxLength(255)
                                ->placeholder('Contoh: 483920184920194'),

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

                        
                    ]),
            ]);
    }
}
