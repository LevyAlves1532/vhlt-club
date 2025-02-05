<?php

namespace App\Filament\Resources\CommunityResource\RelationManagers;

use App\Enum\TournamentStatusEnum;
use App\Models\Community;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class TournamentsRelationManager extends RelationManager
{
    protected static string $relationship = 'tournaments';

    protected static ?string $title = 'Torneios';

    protected static ?string $modelLabel = 'torneio';
    
    protected static ?string $pluralModelLabel = 'torneios';
 
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->is_tournament;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('tournamentCover')
                    ->collection('tournamentCovers')
                    ->label('Imagem:')
                    ->columnSpan(2)
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                    ->required(),
                Forms\Components\Hidden::make('community_id')
                    ->default(fn () => $this->ownerRecord->id),
                Forms\Components\TextInput::make('title')
                    ->label('Título:')
                    ->placeholder('Título do torneio...')
                    ->minLength(3)
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Set $set) => $set('slug', Str::slug($state)))
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug:')
                    ->placeholder('Slug do torneio...')
                    ->unique('tournaments', 'slug', fn ($record) => isset($record) ? $record : null)
                    ->disabled(),
                Forms\Components\TextInput::make('number_players_team')
                    ->label('Jogadores por time:')
                    ->placeholder('Quantidade de jogadores por time...')
                    ->integer()
                    ->columnSpan(2)
                    ->required(),
                Forms\Components\Textarea::make('short_description')
                    ->label('Pequena Descrição:')
                    ->placeholder('Pequena descrição para o torneio...')    
                    ->columnSpan(2)
                    ->rows(2)
                    ->minLength(16)
                    ->required(),
                Forms\Components\RichEditor::make('description')
                    ->label('Descrição:')
                    ->placeholder('Sobre do torneio...')
                    ->columnSpan(2)
                    ->minLength(32)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->columnSpan(2)
                    ->options(TournamentStatusEnum::labels())
                    ->visibleOn('edit'),
                Forms\Components\Toggle::make('is_private')
                    ->label('Privado'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('tournamentCover')
                    ->collection('tournamentCovers')
                    ->label('Imagem:')
                    ->conversion('small'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título:')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status:')
                    ->badge()
                    ->formatStateUsing(fn ($state) => TournamentStatusEnum::labels()[$state->value])
                    ->color(fn ($state) => TournamentStatusEnum::color()[$state->value]),
                Tables\Columns\ToggleColumn::make('is_private')
                    ->label('Privado:')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(fn ($data) => $this->mutateFormData($data)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->color('info')
                    ->mutateFormDataUsing(fn ($data) => $this->mutateFormData($data)),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    private function mutateFormData($data)
    {
        $data['slug'] = Str::slug($data['title']);
                
        return $data;
    }
}
