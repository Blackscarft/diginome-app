<?php

namespace App\Filament\Pages;

use App\Filament\Exports\FailedImportRowExporter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\ExportAction;
use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Imports\Models\Import;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\View\View;

class ManageImports extends Page implements HasTable
{
    use InteractsWithTable;
    use InteractsWithActions;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $navigationLabel = 'Riwayat Import';

    protected static ?string $title = 'Riwayat File Import';

    protected string $view = 'filament.pages.manage-imports';

    public function table(Table $table): Table
    {
        return $table
            ->query(Import::query()->latest())
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu Upload')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Pengunggah')
                    ->searchable()
                    ->default('-'),
                
                TextColumn::make('importer')
                    ->label('Tipe Import')
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => class_basename($state)),

                TextColumn::make('file_name')
                    ->label('Nama File')
                    ->searchable()
                    ->limit(35)
                    ->tooltip(fn (Import $record) => $record->file_name)
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (Import $record) {
                        // Cek keberadaan file di storage 'local'
                        if (Storage::disk('local')->exists($record->file_path)) {
                            return response()->download(
                                Storage::disk('local')->path($record->file_path),
                                $record->file_name,
                                [
                                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                ]
                            );
                        }

                        Notification::make()
                            ->title('File Tidak Ditemukan')
                            ->body('File fisik sudah tidak ada di server.')
                            ->danger()
                            ->send();
                    }),

                TextColumn::make('total_rows')
                    ->label('Total Baris')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('successful_rows')
                    ->label('Sukses')
                    ->numeric()
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                TextColumn::make('failed_rows')
                    ->label('Gagal')
                    ->state(fn (Import $record) => max(0, $record->total_rows - $record->successful_rows))
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                    ->alignCenter(),

                TextColumn::make('completed_at')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Selesai' : 'Memproses...')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
            ])
            ->recordActions([
                // ACTION: Lihat Detail Baris Gagal
                Action::make('viewFailures')
                    ->label('Lihat Error')
                    ->icon('heroicon-o-eye')
                    ->color('danger')
                    // Tombol hanya muncul jika ada baris yang gagal
                    ->visible(fn (Import $record) => ($record->total_rows - $record->successful_rows) > 0)
                    ->modalHeading(fn (Import $record) => "Detail Baris Gagal - {$record->file_name}")
                    ->modalSubmitAction(false) // Sembunyikan tombol Submit
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function (Import $record): View {
                        return view('filament.pages.partials.failed-rows-modal', [
                            'failedRows' => $record->failedRows,
                        ]);
                    }),

                    ExportAction::make('export_errors')
                        ->label('Export Error')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('danger')
                        ->exporter(FailedImportRowExporter::class)
                        ->modifyQueryUsing(fn (Import $record) => FailedImportRow::query()->where('import_id', $record->id))
                        ->visible(fn (Import $record): bool => $record->failedRows()->count() > 0)
                ])

            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete_selected')
                        ->label('Hapus Terpilih')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Riwayat Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus semua data yang dipilih beserta file fisiknya di storage?')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                // 1. Cek & Hapus file fisik dari storage menggunakan relative file_path
                                if ($record->file_path && Storage::disk('local')->exists($record->file_path)) {
                                    Storage::disk('local')->delete($record->file_path);
                                }

                                // 2. Hapus record dari database
                                $record->delete();
                            }
    
                            Notification::make()
                                ->title('Riwayat dan file terpilih berhasil dihapus')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
