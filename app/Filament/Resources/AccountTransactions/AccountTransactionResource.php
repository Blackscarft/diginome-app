<?php

namespace App\Filament\Resources\AccountTransactions;

use App\Filament\Resources\AccountTransactions\Pages\CreateAccountTransaction;
use App\Filament\Resources\AccountTransactions\Pages\EditAccountTransaction;
use App\Filament\Resources\AccountTransactions\Pages\ListAccountTransactions;
use App\Filament\Resources\AccountTransactions\Pages\ViewAccountTransaction;
use App\Filament\Resources\AccountTransactions\Schemas\AccountTransactionForm;
use App\Filament\Resources\AccountTransactions\Schemas\AccountTransactionInfolist;
use App\Filament\Resources\AccountTransactions\Tables\AccountTransactionsTable;
use App\Models\AccountTransaction;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountTransactionResource extends Resource
{
    protected static ?string $model = AccountTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = ' Kas & Bank';

    protected static ?string $navigationLabel = 'Mutasi';

    public static function form(Schema $schema): Schema
    {
        return AccountTransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccountTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountTransactionsTable::configure($table);
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
            'index' => ListAccountTransactions::route('/'),
            'create' => CreateAccountTransaction::route('/create'),
            'view' => ViewAccountTransaction::route('/{record}'),
            'edit' => EditAccountTransaction::route('/{record}/edit'),
        ];
    }
}
