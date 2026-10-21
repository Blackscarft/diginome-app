<?php

namespace App\Filament\Resources\AccountTransactions\Pages;

use App\Filament\Resources\AccountTransactions\AccountTransactionResource;
use App\Filament\Widgets\AccountTransactionOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountTransactions extends ListRecords
{
    protected static string $resource = AccountTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Transaction')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AccountTransactionOverview::class,
        ];
    }
}
