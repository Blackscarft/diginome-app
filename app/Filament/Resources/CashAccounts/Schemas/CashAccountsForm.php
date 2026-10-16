<?php

namespace App\Filament\Resources\CashAccounts\Schemas;

use App\Models\Account;
use App\Models\Branch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CashAccountsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun Kas')
                    ->description('Detail Identitas kas')
                    ->schema([
                        Select::make('account_id')
                            ->label('Akun CoA')
                            ->options(
                                Account::query()
                                    ->where('account_code', 'like', '1-11%')
                                    ->where('is_postable', true)
                                    ->get()
                                    ->mapWithKeys(fn ($record) => [
                                        $record->id => "{$record->account_code} - {$record-> account_name}",
                                    ])
                                    ->all()
                            )
                            ->searchable()
                            ->required(),

                        Select::make('branch_id')
                            ->label('Cabang')
                            ->options(
                                Branch::query()
                                    ->where('parent_id', -1)
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(fn ($record) => [
                                        $record->id => "{$record-> branch_name}",
                                    ])
                                    ->all()
                            )
                            ->searchable()
                            ->required(),

                        TextInput::make('cash_account_code')
                                ->label('Kode Kas')
                                ->required()
                                ->maxLength(20)
                                ->placeholder('Contoh: B-SLO'),

                        TextInput::make('cash_account_name')
                                ->label('Nama Kas')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Contoh: Kas Pusat'),

                        TextInput::make('custodian_name')
                                ->label('Nama Penanggung Jawab')
                                ->nullable()
                                ->maxLength(50)
                                ->placeholder('Contoh: Jhon Doe'),

                        TextInput::make('initial_balance')
                                ->label('Saldo Awal')
                                ->numeric()
                                ->required()
                                ->default(0),

                        TextInput::make('maximum_balance')
                                ->label('Saldo Maksimum')
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
