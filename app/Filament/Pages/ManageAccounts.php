<?php

namespace App\Filament\Pages;

use App\Livewire\AccountTableWidget;
use App\Models\Account;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use SolutionForest\FilamentTree\Pages\TreePage;
use Illuminate\Database\Eloquent\Builder; 
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ManageAccounts extends TreePage
{
    protected static string $model = Account::class;

    protected static string|UnitEnum|null $navigationGroup = 'Master';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Chart of Accounts';

    protected static ?string $title = 'Bagan Akun (COA)';

    protected static int $maxDepth = 4;

    public static function getAccountFormSchema(): array
    {
        return [
            Select::make('parent_id')
                ->label('Parent Akun')
                ->options(
                    fn () => ['-1' => '- Root -'] +
                        Account::query()
                            ->rootAccounts()
                            ->get()
                            ->mapWithKeys(fn ($record) => [
                                $record->id => "{$record->account_code} - {$record-> account_name} - urutan: {$record->order}",
                            ])
                            ->all()
                )
                ->default('-1')
                ->searchable()
                ->required(),

            TextInput::make('account_code')
                ->label('Kode Akun')
                ->required()
                ->maxLength(50),

            TextInput::make('account_name')
                ->label('Nama Akun')
                ->required()
                ->maxLength(255),

            ToggleButtons::make('normal_balance')
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Kredit',
                    ])
                    ->label('Saldo Normal')
                    ->inline()
                    ->required()
                    ->default('debit'),
            
            TextInput::make('description')
                ->label('Deskripsi')
                ->nullable(),
            
            TextInput::make('order')
                ->label('Urutan Tampilan')
                ->numeric()
                ->default(0)
                ->required(),
            
            ToggleButtons::make('is_postable')
                ->label('Dapat Diposting?')
                ->boolean('Ya', 'Tidak')
                ->inline()
                ->required()
                ->default(false),

            ToggleButtons::make('is_active')
                ->label('Status ?')
                ->boolean('Aktif', 'Non-Aktif')
                ->inline()
                ->required()
                ->default(false),
        ];
    }

    public function getNodeCollapsedState(?\Illuminate\Database\Eloquent\Model $record = null): bool
    {
        return true; // Start with all nodes collapsed
    }

    // Kustomisasi teks yang tampil pada setiap node di tree
    public function getTreeRecordTitle(?\Illuminate\Database\Eloquent\Model $record = null): string
    {
        if (!$record) {
            return '';
        }

        $title = "{$record->account_code} - {$record->account_name}";

        // Jika tidak aktif, tambahkan keterangan status di judulnya
        if (!$record->is_active) {
            $title = "🚫 " . $title;
        }
        return (string) $title;
    }

    protected function getTreeToolbarActions(): array
    {
        return [];
    }

    protected function getActions(): array
    {
        return [
            $this->getCreateAction()
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }

    protected function getFormSchema(): array
    {
        return self::getAccountFormSchema();
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
        return false;
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [
            AccountTableWidget::class,
        ];
    }

    // CUSTOMIZE ICON OF EACH RECORD, CAN DELETE
    // public function getTreeRecordIcon(?\Illuminate\Database\Eloquent\Model $record = null): ?string
    // {
    //     return null;
    // }
}
