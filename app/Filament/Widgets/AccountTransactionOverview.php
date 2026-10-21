<?php

namespace App\Filament\Widgets;

use App\Models\AccountTransaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AccountTransactionOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = true;

    protected ?string $placeholderHeight = '110px';

    protected function getStats(): array
    {
        $query = AccountTransaction::query()
            ->whereBetween('transaction_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ]);

        $debit = (clone $query)->sum('debit');
        $credit = (clone $query)->sum('credit');

        $unmatched = AccountTransaction::query()
            ->where('is_matched', false)
            ->count();

        return [
            Stat::make('Debit', 'Rp ' . number_format($debit, 2, ',', '.'))
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->description('Total debit bulan ini')
                ->color('success'),

            Stat::make('Kredit','Rp ' . number_format($credit, 2, ',', '.'))
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->description('Total kredit bulan ini')
                ->color('danger'),

            Stat::make(
                    'Belum Direkonsiliasi',
                    number_format($unmatched, 0, ',', '.')
                )
                ->description('Semua tanggal')
                ->color($unmatched > 0 ? 'warning' : 'success'),
        ];
    }
}
