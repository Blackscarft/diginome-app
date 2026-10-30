<?php

namespace App\Filament\Resources\FundPrograms\Schemas;

use App\Models\Fund;
use App\Models\FundProgram;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class FundProgramsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Program')
                ->description('Detail informasi program.')
                ->schema([
                    Select::make('parent_id')
                        ->label('Parent Akun')
                        ->options(
                            fn () => ['-1' => '- Root -'] +
                                FundProgram::query()
                                    ->rootAccounts()
                                    ->get()
                                    ->mapWithKeys(fn ($record) => [
                                        $record->id => "{$record->fund_program_code} - {$record-> fund_program_name}",
                                    ])
                                    ->all()
                        )
                        ->default('-1')
                        ->searchable()
                        ->required(),
                    Select::make('fund_id')
                        ->label('Jenis Sumber')
                        ->options(Fund::where('parent_id', '!=', '-1')->pluck('fund_name', 'id'))
                        ->required(),
                    TextInput::make('fund_program_code')->label('Kode Program')->required()->unique(),
                    TextInput::make('fund_program_name')->label('Nama Program')->required(),
                    Select::make('category')
                        ->label('Kategori')
                        ->options(function(){
                            $column = DB::select("SHOW COLUMNS FROM fund_programs WHERE Field = 'category'")[0];
                            
                            preg_match("/^enum\('(.*)'\)$/", $column->Type, $matches);

                            return collect(explode("','", $matches[1]))
                                ->mapWithKeys(fn ($value) => [$value => ucfirst($value)])
                                ->toArray();
                        })->required(),
                    Textarea::make('description')->label('Deskripsi'),
                    TextInput::make('initial_balance')->label('Saldo Initial')->required(),
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
