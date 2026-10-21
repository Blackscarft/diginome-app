<?php

namespace App\Filament\Resources\AccountTransactions\Pages;

use App\Filament\Resources\AccountTransactions\AccountTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountTransaction extends CreateRecord
{
    protected static string $resource = AccountTransactionResource::class;

    // Redirect ke tabel List setelah berhasil simpan (Create)
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
