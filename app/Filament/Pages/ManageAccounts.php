<?php

namespace App\Filament\Pages;

use App\Livewire\AccountTableWidget;
use App\Models\Account;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Columns\TextColumn;
use SolutionForest\FilamentTree\Pages\TreePage;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder; 
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Table;
use UnitEnum;
use Filament\Tables\Concerns\InteractsWithTable;

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
            TextInput::make('code')
                ->label('Kode Akun')
                ->required()
                ->maxLength(50),

            TextInput::make('name')
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
            
            TextInput::make('order')
                ->label('Urutan Tampilan')
                ->numeric()
                ->default(0)
                ->required(),
            
            Select::make('parent_id')
                    ->label('Parent Akun')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->rootAccounts()
                    )
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->code} - {$record->name} - urutan: {$record->order}")
                    ->searchable(['code', 'name'])
                    ->nullable(),
            
            ToggleButtons::make('is_postable')
                ->label('Dapat Diposting?')
                ->boolean('Ya', 'Tidak')
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
        if (!$record) return '';
        return "{$record->code} - {$record->name}";
    }

    protected function getTreeToolbarActions(): array
    {
        return [
            ExportAction::make('Export Akun')
                ->label('Export Akun')
        ];
    }

    protected function getActions(): array
    {
        return [
            $this->getCreateAction(),
            // SAMPLE CODE, CAN DELETE
            //\Filament\Pages\Actions\Action::make('sampleAction'),
        ];
    }

    protected function getFormSchema(): array
    {
        return self::getAccountFormSchema();
    }

    protected function hasDeleteAction(): bool
    {
        return true;
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
