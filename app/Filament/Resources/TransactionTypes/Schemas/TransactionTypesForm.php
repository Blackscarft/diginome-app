<?php

namespace App\Filament\Resources\TransactionTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class TransactionTypesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tipe Transaksi Mutasi')
                    ->description('Detail informasi tipe transaksi mutasi.')
                    ->schema([
                        TextInput::make('type_code')
                                    ->label('Kode Tipe')
                                    ->required()
                                    ->maxLength(20)
                                    ->placeholder('Contoh: EXP'),
            
                        TextInput::make('type_name')
                                    ->label('Nama Tipe')
                                    ->required()
                                    ->maxLength(20)
                                    ->placeholder('Contoh: Pengeluaran'),

                        Textarea::make('description')
                                ->label('Deskripsi')
                                ->nullable(),

                        Select::make('category')
                            ->label('Kategori')
                            ->options(function () {
                                $column = DB::select("SHOW COLUMNS FROM transaction_types WHERE Field = 'category'")[0];

                                preg_match("/^enum\('(.*)'\)$/", $column->Type, $matches);

                                return collect(explode("','", $matches[1]))
                                    ->mapWithKeys(fn ($value) => [$value => ucfirst($value)])
                                    ->toArray();
                            })
                            ->required(),
                        
                        ToggleButtons::make('is_active')
                                ->label('Aktif?')
                                ->boolean('Ya', 'Tidak')
                                ->inline()
                                ->required()
                                ->default(true),
                    ])
            ]);
    }
}
