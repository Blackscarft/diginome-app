<?php

namespace App\Filament\Resources\CashAccounts;

use App\Filament\Resources\CashAccounts\Pages\CreateCashAccounts;
use App\Filament\Resources\CashAccounts\Pages\EditCashAccounts;
use App\Filament\Resources\CashAccounts\Pages\ListCashAccounts;
use App\Filament\Resources\CashAccounts\Schemas\CashAccountsForm;
use App\Filament\Resources\CashAccounts\Tables\CashAccountsTable;
use App\Models\CashAccount;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CashAccountsResource extends Resource
{
    protected static ?string $model = CashAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'CashAccount';

    protected static string|UnitEnum|null $navigationGroup = 'Bank';

    public static function form(Schema $schema): Schema
    {
        return CashAccountsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashAccountsTable::configure($table);
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
            'index' => ListCashAccounts::route('/'),
            'create' => CreateCashAccounts::route('/create'),
            'edit' => EditCashAccounts::route('/{record}/edit'),
        ];
    }
}
