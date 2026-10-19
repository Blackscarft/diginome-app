<?php

namespace App\Filament\Resources\TransactionTypes\Pages;

use App\Filament\Resources\TransactionTypes\TransactionTypesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransactionTypes extends CreateRecord
{
    protected static string $resource = TransactionTypesResource::class;

    // Redirect ke tabel List setelah berhasil simpan (Create)
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
