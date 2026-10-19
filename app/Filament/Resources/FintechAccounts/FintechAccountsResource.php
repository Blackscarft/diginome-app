<?php

namespace App\Filament\Resources\FintechAccounts;

use App\Filament\Resources\FintechAccounts\Pages\CreateFintechAccounts;
use App\Filament\Resources\FintechAccounts\Pages\EditFintechAccounts;
use App\Filament\Resources\FintechAccounts\Pages\ListFintechAccounts;
use App\Filament\Resources\FintechAccounts\Schemas\FintechAccountsForm;
use App\Filament\Resources\FintechAccounts\Tables\FintechAccountsTable;
use App\Models\FintechAccount;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FintechAccountsResource extends Resource
{
    protected static ?string $model = FintechAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'FintechAccount';

    protected static string|UnitEnum|null $navigationGroup = 'Bank';

    public static function form(Schema $schema): Schema
    {
        return FintechAccountsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FintechAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFintechAccounts::route('/'),
            'create' => CreateFintechAccounts::route('/create'),
            'edit' => EditFintechAccounts::route('/{record}/edit'),
        ];
    }
}
