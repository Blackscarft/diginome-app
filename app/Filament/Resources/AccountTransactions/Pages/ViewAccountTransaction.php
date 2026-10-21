<?php

namespace App\Filament\Resources\AccountTransactions\Pages;

use App\Filament\Resources\AccountTransactions\AccountTransactionResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountTransaction extends ViewRecord
{
    protected static string $resource = AccountTransactionResource::class;

    public function getTitle(): string
    {
        return 'Detail Transaksi - ' . ($this->record->id ?? '-');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('back')
                ->label('List Transaction')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
        ];
    }
}
