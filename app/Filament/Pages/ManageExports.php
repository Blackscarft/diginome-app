<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Exports\Models\Export; // Memanggil model log export Filament
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;


class ManageExports extends Page implements HasTable
{
    use InteractsWithTable;
    use InteractsWithActions;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';
    
    // Label teks yang akan muncul di menu sidebar admin
    protected static ?string $navigationLabel = 'Riwayat Ekspor';
    
    // Judul halaman
    protected static ?string $title = 'Riwayat Ekspor';

    // Tampilan halaman yang digunakan
    protected string $view = 'filament.pages.manage-exports';

    public function table(Table $table): Table
    {
        return $table
            ->query(Export::query()->latest())
            ->columns([
                TextColumn::make('user.name')
                    ->label('Diekspor Oleh')
                    ->searchable()
                    ->sortable()
                    ->default('System / Guest'),

                TextColumn::make('exporter')
                    ->label('Tipe Ekspor')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->searchable(),

                TextColumn::make('total_rows')
                    ->label('Total Baris')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('successful_rows')
                    ->label('Berhasil')
                    ->numeric()
                    ->color('success'),

                TextColumn::make('file_name')
                    ->label('Nama File')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Export $record): string => 
                        $record->completed_at ? 'completed' : 'processing'
                    )
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'processing' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Selesai',
                        'processing' => 'Diproses',
                        'failed' => 'Gagal',
                        default => $state,
                    }),

                TextColumn::make('completed_at')
                    ->label('Tanggal Selesai')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                // Tombol Download Format Excel (.xlsx)
                Action::make('download_excel')
                    ->label('Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->visible(fn (Export $record): bool => !is_null($record->completed_at))
                    ->action(function (Export $record) {
                        $disk = Storage::disk($record->file_disk); // Biasanya disk 'local'
                        
                        // Susun path berdasarkan struktur folder: filament_exports/{id}/{filename}
                        // Jika $record->file_name hanya berisi nama file saja:
                        $baseName = pathinfo($record->file_name, PATHINFO_FILENAME);
                        $filePath = "filament_exports/{$record->id}/{$baseName}.xlsx";

                        // Fallback jika $record->file_name sudah mencakup path lengkapnya
                        if (!$disk->exists($filePath)) {
                            $filePath = str_ends_with($record->file_name, '.xlsx') 
                                ? $record->file_name 
                                : "filament_exports/{$record->id}/" . basename($record->file_name);
                        }

                        if (!$disk->exists($filePath)) {
                            Notification::make()->title('File Excel tidak ditemukan di storage')->danger()->send();
                            return;
                        }

                        return response()->download(
                            $disk->path($filePath),
                            basename($filePath),
                            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                        );
                    }),
                
                // Tombol Download Format CSV (.csv)
                Action::make('download_csv')
                    ->label('CSV')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->visible(fn (Export $record): bool => !is_null($record->completed_at))
                    ->action(function (Export $record) {
                        $disk = Storage::disk($record->file_disk);
                        
                        $baseName = pathinfo($record->file_name, PATHINFO_FILENAME);
                        $filePath = "filament_exports/{$record->id}/{$baseName}.csv";

                        if (!$disk->exists($filePath)) {
                            $filePath = str_ends_with($record->file_name, '.csv') 
                                ? $record->file_name 
                                : "filament_exports/{$record->id}/" . basename($record->file_name);
                        }

                        if (!$disk->exists($filePath)) {
                            Notification::make()->title('File CSV tidak ditemukan di storage')->danger()->send();
                            return;
                        }

                        return response()->download(
                            $disk->path($filePath),
                            basename($filePath),
                            ['Content-Type' => 'text/csv'],
                        );
                    }),
            ])
            ->toolbarActions([
                // Bulk Action untuk menghapus banyak data sekaligus beserta file storagenya
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
                                $disk = Storage::disk($record->file_disk);
                                $directory = "filament_exports/{$record->id}";
    
                                // Hapus folder storage jika ada
                                if ($disk->exists($directory)) {
                                    $disk->deleteDirectory($directory);
                                }
                                if ($record->file_name && $disk->exists($record->file_name)) {
                                    $disk->delete($record->file_name);
                                }
    
                                // Hapus record database
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
