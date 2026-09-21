<?php

namespace App\Filament\Resources\BankMutations\Pages;

use App\Filament\Resources\BankMutations\BankMutationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBankMutation extends ViewRecord
{
    protected static string $resource = BankMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
