<?php

namespace App\Filament\Resources\BankMutations;

use App\Filament\Resources\BankMutations\Pages\CreateBankMutation;
use App\Filament\Resources\BankMutations\Pages\EditBankMutation;
use App\Filament\Resources\BankMutations\Pages\ListBankMutations;
use App\Filament\Resources\BankMutations\Pages\ViewBankMutation;
use App\Filament\Resources\BankMutations\Schemas\BankMutationForm;
use App\Filament\Resources\BankMutations\Schemas\BankMutationInfolist;
use App\Filament\Resources\BankMutations\Tables\BankMutationsTable;
use App\Models\BankMutation;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankMutationResource extends Resource
{
    protected static ?string $model = BankMutation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Bank';

    public static function form(Schema $schema): Schema
    {
        return BankMutationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BankMutationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankMutationsTable::configure($table);
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
            'index' => ListBankMutations::route('/'),
            'create' => CreateBankMutation::route('/create'),
            'view' => ViewBankMutation::route('/{record}'),
            'edit' => EditBankMutation::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
