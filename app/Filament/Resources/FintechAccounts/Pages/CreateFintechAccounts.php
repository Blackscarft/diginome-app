<?php

namespace App\Filament\Resources\FintechAccounts\Pages;

use App\Filament\Resources\FintechAccounts\FintechAccountsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFintechAccounts extends CreateRecord
{
    protected static string $resource = FintechAccountsResource::class;

    // Redirect ke tabel List setelah berhasil simpan (Create)
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
