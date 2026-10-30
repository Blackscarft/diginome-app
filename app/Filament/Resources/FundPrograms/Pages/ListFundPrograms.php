<?php

namespace App\Filament\Resources\FundPrograms\Pages;

use App\Filament\Resources\FundPrograms\FundProgramsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFundPrograms extends ListRecords
{
    protected static string $resource = FundProgramsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New Program')->icon('heroicon-o-plus'),
        ];
    }
}
