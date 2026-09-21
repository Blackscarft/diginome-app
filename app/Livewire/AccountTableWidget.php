<?php

namespace App\Livewire;

use App\Filament\Exports\AccountExporter;
use App\Filament\Pages\ManageAccounts;
use App\Models\Account;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;

class AccountTableWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Account::query())
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Akun')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Akun')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parent.name')
                    ->label('Induk (Parent)')
                    ->default('- Root -'),

                TextColumn::make('normal_balance')
                    ->label('Saldo Normal')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'debit' => 'warning',
                        'credit' => 'success',
                        default => 'gray',
                    }),
                
                IconColumn::make('is_postable')
                    ->label('Dapat Posting?')
                    ->boolean()
            ])
            ->filters([
                //
            ])
            ->headerActions([
                ExportAction::make()
                        ->exporter(AccountExporter::class)
                        ->label('Export Akun')
                        ->color('success')
            ])
            ->recordActions([
                EditAction::make()
                    ->schema(ManageAccounts::getAccountFormSchema())
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
