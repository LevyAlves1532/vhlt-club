<?php

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeamsRelationManager extends RelationManager
{
    protected static string $relationship = 'teams';

    protected static ?string $title = 'Times';

    protected static ?string $modelLabel = 'time';
    
    protected static ?string $pluralModelLabel = 'times';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('tournament_id')
                    ->default(fn () => $this->ownerRecord->id),
                Forms\Components\SpatieMediaLibraryFileUpload::make('teamFlag')
                    ->collection('teamFlags')
                    ->label('Bandeira:')
                    ->columnSpan(2)
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nome:')
                    ->placeholder('Nome do time...')
                    ->minLength(3)
                    ->maxLength(255)
                    ->required(),
                Forms\Components\ColorPicker::make('color')
                    ->label('Cor do Timer:')
                    ->required(),
                Forms\Components\Toggle::make('is_open')
                    ->label('Time Aberto'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Time Aberto')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('teamFlag')
                    ->collection('teamFlags')
                    ->label('Bandeira:')
                    ->conversion('small'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome:')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ColorColumn::make('color')
                    ->label('Cor:'),
                Tables\Columns\ToggleColumn::make('is_open')
                    ->label('Time Aberto:'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Ativo:'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->before(function ($data, $action) {
                        $this->validateName($data, $action);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->color('info')
                    ->before(function ($data, $action, $record) {
                        $this->validateName($data, $action, $record);
                    }),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    private function validateName($data, $action, $record = null)
    {
        $tournament_id = $data['tournament_id'];
        $name = $data['name'];
        
        $query = Team::query();

        if ($record) {
            $query = $query->where('id', '!=', $record->id);
        }

        $count = $query->where('tournament_id', $tournament_id)->where('name', $name)->count();

        if ($count > 0) {
            Notification::make()
                ->title('Nome repetido')
                ->body('Existe um time neste torneio com esse nome')
                ->danger()
                ->send();

            $action->halt();
        }
    }
}
