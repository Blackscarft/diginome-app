<?php

namespace App\Filament\Resources\AccountTransactions\Tables;

use App\Filament\Exports\AccountTransactionExporter;
use App\Filament\Resources\AccountTransactions\AccountTransactionResource;
use App\Models\BankAccount;
use App\Models\CashAccount;
use App\Models\FintechAccount;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Schemas\Components\Utilities\Get;

class AccountTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->defaultSort('transaction_date', 'desc')
            ->heading('Daftar Transaksi Akun')
            ->description('Kelola daftar transaksi akun') 
            ->columns([
                TextColumn::make('transactionType.type_name')
                    ->label('Tipe')
                    ->badge(),

                TextColumn::make('accountable_type')
                    ->label('Jenis Akun')
                    ->formatStateUsing(
                        fn (?string $state) => match ($state) {
                            BankAccount::class => 'Bank',
                            CashAccount::class => 'Kas',
                            FintechAccount::class => 'Fintech',
                            default => class_basename($state ?? '-'),
                        }
                    )
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                            BankAccount::class => 'success',
                            CashAccount::class => 'gray',
                            FintechAccount::class => 'info',
                            default => 'gray',
                    }),

                TextColumn::make('accountable_name')
                    ->label('Nama Akun')
                    ->state(function ($record) {
                        $accountable = $record->accountable;

                        if (! $accountable) {
                            return '-';
                        }

                        return match (class_basename($accountable)) {
                            'BankAccount' => $accountable->bank_name . ' - ' . $accountable->account_number,
                            'CashAccount' => $accountable->cash_account_name,
                            'FintechAccount' => $accountable->fintech_account_name . ' - ' . $accountable->merchant_id,
                            default => '-',
                        };
                    })
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                TextColumn::make('transaction_time')
                    ->label('Waktu')
                    ->time('H:i:s')
                    ->placeholder('-'),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->wrap()
                    ->limit(45),

                TextColumn::make('reference')
                    ->label('Referensi')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('debit')
                    ->label('Debit')
                    ->money('IDR')
                    ->alignEnd(),

                TextColumn::make('credit')
                    ->label('Kredit')
                    ->money('IDR')
                    ->alignEnd(),

                TextColumn::make('fee_amount')
                    ->label('Biaya')
                    ->money('IDR')
                    ->alignEnd(),

                TextColumn::make('is_matched')
                    ->label('Rekonsiliasi')
                    ->badge()
                    ->icons([
                        'heroicon-o-check-circle' => true,
                        'heroicon-o-clock' => false,
                    ])
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'matched' : 'unmatched'),
            ])
            ->headerActions([
                Action::make('ImportAccountTransaction')
                    ->label('Import')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->url(AccountTransactionResource::getUrl('import')),

                ExportAction::make('ExportAccountTransaction')
                        ->label('Export')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')                        
                        ->exporter(AccountTransactionExporter::class)
                        ->modifyQueryUsing(function (Builder $query, array $data) {
                            return $query
                                ->when(
                                    $data['start_date'] ?? null,
                                    fn (Builder $query, $date) =>
                                        $query->whereDate('transaction_date', '>=', $date)
                                )
                                ->when(
                                    $data['end_date'] ?? null,
                                    fn (Builder $query, $date) =>
                                        $query->whereDate('transaction_date', '<=', $date)
                                )
                                ->when(
                                    $data['accountable_type'] ?? null,
                                    fn (Builder $query, $type) =>
                                        $query->where('accountable_type', $type)
                                )
                                ->when(
                                    $data['accountable_id'] ?? null,
                                    fn (Builder $query, $id) =>
                                        $query->where('accountable_id', $id)
                                );
                        }),

            ])
            ->filters([
                Filter::make('account')
                    ->label('Akun')
                    ->schema([
                        Select::make('type')
                            ->label('Jenis akun')
                            ->options([
                                BankAccount::class => 'Bank',
                                CashAccount::class => 'Kas',
                                FintechAccount::class => 'Fintech',
                            ])
                            ->live(),

                        Select::make('id')
                            ->label('Nama akun')
                            ->options(function (Get $get): array {
                                return match ($get('type')) {
                                    BankAccount::class => BankAccount::query()
                                        ->orderBy('bank_name')
                                        ->get()
                                        ->mapWithKeys(fn ($account) => [
                                            $account->id => $account->bank_name
                                                . ' - '
                                                . ($account->account_number ?? '-'),
                                        ])
                                        ->all(),

                                    CashAccount::class => CashAccount::query()
                                        ->orderBy('cash_account_name')
                                        ->pluck('cash_account_name', 'id')
                                        ->all(),

                                    FintechAccount::class => FintechAccount::query()
                                        ->orderBy('fintech_account_name')
                                        ->get()
                                        ->mapWithKeys(fn ($account) => [
                                            $account->id => $account->fintech_account_name
                                                . ' - '
                                                . ($account->merchant_id ?? '-'),
                                        ])
                                        ->all(),

                                    default => [],
                                };
                            })
                            ->searchable()
                            ->disabled(fn (Get $get) => blank($get('type'))),
                    ])
                    ->query(function (
                        Builder $query,
                        array $data
                    ): Builder {
                        return $query
                            ->when(
                                $data['type'] ?? null,
                                fn (Builder $q, $type) =>
                                    $q->where('accountable_type', $type)
                            )
                            ->when(
                                $data['id'] ?? null,
                                fn (Builder $q, $id) =>
                                    $q->where('accountable_id', $id)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['type'] ?? null)) {
                            $typeLabel = match ($data['type']) {
                                BankAccount::class => 'Bank',
                                CashAccount::class => 'Kas',
                                FintechAccount::class => 'Fintech',
                                default => $data['type'],
                            };

                            $indicators[] = 'Jenis akun: ' . $typeLabel;
                        }

                        if (filled($data['id'] ?? null)) {
                            $accountName = match ($data['type'] ?? null) {
                                BankAccount::class => BankAccount::find($data['id'])?->bank_name,
                                CashAccount::class => CashAccount::find($data['id'])?->cash_account_name,
                                FintechAccount::class => FintechAccount::find($data['id'])?->fintech_account_name,
                                default => null,
                            };

                            $indicators[] = 'Nama akun: ' . ($accountName ?? $data['id']);
                        }

                        return $indicators;
                    }),

                Filter::make('transaction_date')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Dari tanggal'),

                        DatePicker::make('until')
                            ->label('Sampai tanggal'),
                    ])
                    ->query(function (
                        Builder $query,
                        array $data
                    ): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $q, $date) =>
                                    $q->whereDate(
                                        'transaction_date',
                                        '>=',
                                        $date
                                    )
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $q, $date) =>
                                    $q->whereDate(
                                        'transaction_date',
                                        '<=',
                                        $date
                                    )
                            );
                    }),

                SelectFilter::make('is_matched')
                    ->label('Status Rekonsiliasi')
                    ->options([
                        '1' => 'Matched',
                        '0' => 'Unmatched',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, $records) {
                            if ($records->contains(fn ($record) => $record->is_matched)) {
                                Notification::make()
                                    ->title('Tidak dapat menghapus data')
                                    ->body('Data yang sudah matched tidak dapat dihapus.')
                                    ->danger()
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }
}


