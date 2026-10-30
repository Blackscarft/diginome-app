<?php

namespace App\Filament\Resources\FundPrograms;

use App\Filament\Resources\FundPrograms\Pages\CreateFundPrograms;
use App\Filament\Resources\FundPrograms\Pages\EditFundPrograms;
use App\Filament\Resources\FundPrograms\Pages\ListFundPrograms;
use App\Filament\Resources\FundPrograms\Schemas\FundProgramsForm;
use App\Filament\Resources\FundPrograms\Tables\FundProgramsTable;
use BackedEnum;
use UnitEnum;
use App\Models\FundProgram;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FundProgramsResource extends Resource
{
    protected static ?string $model = FundProgram::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'FundProgram';

    protected static string|UnitEnum|null $navigationGroup = 'Master';

    public static function form(Schema $schema): Schema
    {
        return FundProgramsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FundProgramsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFundPrograms::route('/'),
            'create' => CreateFundPrograms::route('/create'),
            'edit' => EditFundPrograms::route('/{record}/edit'),
        ];
    }
}
