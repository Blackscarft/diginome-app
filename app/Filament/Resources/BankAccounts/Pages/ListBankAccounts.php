<?php

namespace App\Filament\Resources\BankAccounts\Pages;

use App\Filament\Exports\BankAccountExporter;
use App\Filament\Resources\BankAccounts\BankAccountResource;
use App\Models\Account;
use App\Models\BankAccount;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBankAccounts extends ListRecords
{
    protected static string $resource = BankAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Semua Bank')
                ->badge(BankAccount::count()),
        ];

        $accounts = Account::whereHas('bankAccount')->get();
        
        foreach ($accounts as $account) {
            $tabs["account_{$account->id}"] = Tab::make("{$account->name}")
                ->modifyQueryUsing(fn (Builder $query) => $query->where('account_id', $account->id))
                ->badge(BankAccount::where('account_id', $account->id)->count())
                ->badgeColor('primary');
        }

        return $tabs;
    }
}
