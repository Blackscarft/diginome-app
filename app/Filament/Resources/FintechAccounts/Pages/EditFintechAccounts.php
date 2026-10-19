<?php

namespace App\Filament\Resources\FintechAccounts\Pages;

use App\Filament\Resources\FintechAccounts\FintechAccountsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFintechAccounts extends EditRecord
{
    protected static string $resource = FintechAccountsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    // Redirect ke tabel List setelah berhasil simpan (Create)
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
