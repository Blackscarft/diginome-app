<?php

namespace App\Filament\Resources\CashAccounts\Pages;

use App\Filament\Resources\CashAccounts\CashAccountsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCashAccounts extends CreateRecord
{
    protected static string $resource = CashAccountsResource::class;

    // Redirect ke tabel List setelah berhasil simpan (Create)
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
