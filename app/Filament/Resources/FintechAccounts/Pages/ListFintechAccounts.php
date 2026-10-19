<?php

namespace App\Filament\Resources\FintechAccounts\Pages;

use App\Filament\Resources\FintechAccounts\FintechAccountsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFintechAccounts extends ListRecords
{
    protected static string $resource = FintechAccountsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New fintech account')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
