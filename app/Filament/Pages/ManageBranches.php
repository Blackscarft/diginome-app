<?php

namespace App\Filament\Pages;

use App\Exports\BranchTemplateExport;
use App\Filament\Exports\BranchExporter;
use App\Imports\BranchImport;
use App\Models\Branch;
use Filament\Actions\ExportAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Builder; 
use SolutionForest\FilamentTree\Pages\TreePage;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ManageBranches extends TreePage
{
    protected static string $model = Branch::class;

    protected static string|UnitEnum|null $navigationGroup = 'Master';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Branch';

    protected static ?string $title = 'Master Cabang';

    // protected ?string $subheading = 'Kelola hierarki dan data cabang.';

    protected static int $maxDepth = 3;

    public ?string $search = '';

    protected function getTreeToolbarActions(): array
    {
        return [];
    }

    protected function getActions(): array
    {
        return [
            Action::make('ImportBranches')
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->modalHeading('Import Branches')
                ->modalDescription(
                    'Upload file Excel (.xlsx) yang berisi data cabang. Pastikan format kolom sesuai dengan template yang disediakan.'
                )
                ->extraModalFooterActions([
                    Action::make('downloadTemplate')
                        ->label('Download Template')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('warning')
                        ->action(fn () => Excel::download(
                            new BranchTemplateExport, 
                            'template_import_branches.xlsx'
                        )),
                ])
                ->schema([
                    FileUpload::make('file_excel')
                        ->label('Pilih file Excel (.xlsx)')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->storeFiles(true)
                        ->directory('imports/branches')
                        ->getUploadedFileNameForStorageUsing(
                            fn ($file) => 'Branch_' . now()->format('Ymd_His') . '_' . $file->getClientOriginalName()
                        )
                        ->required()
                        ->maxSize(1024), // 1 MB
                ])
                ->action(function (array $data) {

                    // 1. Ambil relative path tempat file disimpan di storage
                    $storedFilePath = $data['file_excel'];

                    // 2. Ambil nama file asli (misal: "data_mutasi_januari.xlsx")
                    // Karena FileUpload menyimpan file di disk, kita pakai pathinfo() untuk mengambil nama aslinya
                    $fileName = basename($storedFilePath);

                    // 3. Simpan data ke Tabel Imports
                    $importRecord = Import::create([
                        'user_id' => Auth::id(),
                        'importer' => BranchImport::class,
                        'file_name' => $fileName,
                        'file_path' => $storedFilePath,
                        'total_rows' => 0,
                        'successful_rows' => 0,
                        'processed_rows' => 0
                    ]);

                    // 4. Masukkan proses import ke Queue (Background)
                    Excel::queueImport(
                        new BranchImport($importRecord->id, Auth::id()), 
                        $storedFilePath, 
                        'local' // Disk penyimpanan Laravel (default: storage/app)
                    );

                    // 5. Kirim notifikasi
                    Notification::make()
                        ->title('Import Dimulai di Background')
                        ->body('File Anda sedang diproses di antrean server. Anda dapat melanjutkan pekerjaan lain.')
                        ->info()
                        ->send();

                }),

            ExportAction::make()
                ->exporter(BranchExporter::class)
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Export'),

            $this->getCreateAction()
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading('Tambah'),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('parent_id')
                ->label('Parent Akun')
                ->options(
                    fn () => ['-1' => '- Root -'] +
                        Branch::query()
                            ->rootBranches()
                            ->get()
                            ->mapWithKeys(fn ($record) => [
                                $record->id => "{$record->branch_code} - {$record->branch_name} - urutan: {$record->order}",
                            ])
                            ->all()
                )
                ->default('-1')
                ->searchable()
                ->required(),

            TextInput::make('branch_code')
                ->label('Kode Cabang')
                ->required()
                ->maxLength(50),

            TextInput::make('branch_name')
                ->label('Nama Cabang')
                ->required()
                ->maxLength(255),

            TextInput::make('description')
                ->label('Deskripsi')
                ->maxLength(255),

            TextInput::make('order')
                ->label('Urutan Tampilan')
                ->numeric()
                ->default(1)
                ->required(),

            ToggleButtons::make('is_active')
                    ->label('Aktif?')
                    ->boolean('Ya', 'Tidak')
                    ->inline()
                    ->required()
                    ->default(true),
        ];
    }

    public function getHeader(): View
    {
        return view('filament.widgets.tree-header-with-search', [
            'heading' => $this->getTitle(),
            'subheading' => $this->getSubheading(),
            'breadcrumbs' => $this->getBreadcrumbs(),
        ]);
    }


    public function getTreeRecordTitle(?\Illuminate\Database\Eloquent\Model $record = null): string
    {
        if (!$record) {
            return '';
        }

        $title = "{$record->branch_code} - {$record->branch_name}";

        // Jika tidak aktif, tambahkan keterangan status di judulnya
        if (!$record->is_active) {
            $title = "🚫 " . $title;
        }
        return (string) $title;
    }

    protected function getTreeQuery(): Builder
    {
        return parent::getTreeQuery()
            ->when(filled($this->search), function (Builder $query) {
                $search = trim($this->search);

                $query->where(function (Builder $query) use ($search) {
                    $query->where('branch_code', 'like', "%{$search}%")
                        ->orWhere('branch_name', 'like', "%{$search}%");
                });
            });
    }

    public function updatedSearch(): void
    {
        $this->records = null;
    }

    protected function hasDeleteAction(): bool
    {
        return false;
    }

    protected function hasEditAction(): bool
    {
        return true;
    }

    protected function hasViewAction(): bool
    {
        return true;
    }

    protected function getViewFormSchema(): array
    {
        return [
            TextEntry::make('branch_code')
                ->label('Kode Cabang'),

            TextEntry::make('branch_name')
                ->label('Nama Cabang'),

            TextEntry::make('parent.branch_name')
                ->label('Parent Cabang')
                ->placeholder('-'),

            TextEntry::make('description')
                ->label('Deskripsi')
                ->placeholder('-'),

            TextEntry::make('order')
                ->label('Urutan Tampilan'),

            TextEntry::make('is_active')
                ->label('Status')
                ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Tidak Aktif')
                ->badge()
                ->color(fn ($state) => $state ? 'success' : 'danger'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // BranchSearch::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    // CUSTOMIZE ICON OF EACH RECORD, CAN DELETE
    // public function getTreeRecordIcon(?\Illuminate\Database\Eloquent\Model $record = null): ?string
    // {
    //     return null;
    // }
}
