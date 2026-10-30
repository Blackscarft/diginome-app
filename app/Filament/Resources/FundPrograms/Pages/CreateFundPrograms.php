<?php

namespace App\Filament\Resources\FundPrograms\Pages;

use App\Filament\Resources\FundPrograms\FundProgramsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFundPrograms extends CreateRecord
{
    protected static string $resource = FundProgramsResource::class;

    // Redirect ke tabel List setelah berhasil simpan (Create)
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
