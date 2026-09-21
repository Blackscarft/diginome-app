<?php

namespace App\Filament\Resources\BankMutations\Tables;

use App\Filament\Imports\BankMutationImporter;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BankMutationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_date', 'desc')
            ->recordClasses(fn ($record) => match (true) {
                $record->deleted_at !== null => 'deleted-record-table-row',
                default => null,
            })
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->date()
                    ->width(200),
                    
                TextColumn::make('transaction_time')
                    ->label('Waktu Transaksi')
                    ->dateTime('H:i')
                    ->time()
                    ->placeholder('-')
                    ->width(150),                  

                TextColumn::make('bank_code')
                    ->label('Kode Bank')
                    ->badge()
                    ->width(150),

                TextColumn::make('bank_name')
                    ->label('Nama Bank')
                    ->width(150)
                    ->searchable(),
                
                TextColumn::make('debit')
                    ->label('Debit')
                    ->money('IDR')
                    ->width(150)
                    ->color('danger'),

                TextColumn::make('credit')
                    ->label('Kredit')
                    ->money('IDR')
                    ->width(150)
                    ->color('success'),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable(),
                
                TextColumn::make('is_matched')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->width(150)
                    ->formatStateUsing(fn ($state) => $state ? 'Matched' : 'Unmatched'),
                    
                    
                TextColumn::make('reference')
                    ->label('Referensi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('source_id')
                    ->label('ID Sumber')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
            ])
            ->filters([
                TernaryFilter::make('is_matched')
                    ->label('Status Matched')
                    ->placeholder('Semua Status')
                    ->trueLabel('Sudah Matched')
                    ->falseLabel('Belum Matched'),
                SelectFilter::make('bank_code')
                    ->label('Kode Bank'),
                Filter::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->schema([
                        DatePicker::make('transaction_date_from')
                            ->label('Dari Tanggal')
                            ->placeholder('Pilih tanggal awal'),
                        DatePicker::make('transaction_date_to')
                            ->label('Sampai Tanggal')
                            ->placeholder('Pilih tanggal akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['transaction_date_from'])) {
                            $query->whereDate('transaction_date', '>=', $data['transaction_date_from']);
                        }
                        if (!empty($data['transaction_date_to'])) {
                            $query->whereDate('transaction_date', '<=', $data['transaction_date_to']);
                        }
                        return $query;
                    }),
                TrashedFilter::make()
                    ->label('Status Hapus')
                    ->placeholder('Aktif Saja')
                    ->trueLabel('Termasuk Dihapus')
                    ->falseLabel('Hanya Dihapus'),
            ])
            // ->headerActions([
            //     ImportAction::make()
            //         ->label('Import XLSX')
            //         ->color('success')
            //         ->importer(BankMutationImporter::class)
            //             ->modalHeading('Import Mutasi Bank')
            //             ->modalDescription(
            //                 'Upload file Excel (.xlsx), kemudian lakukan mapping kolom sebelum proses import.'
            //             ),
            // ])
            ->recordActions([   
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
                // ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
