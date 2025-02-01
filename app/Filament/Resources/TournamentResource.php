<?php

namespace App\Filament\Resources;

use App\Enum\TournamentStatusEnum;
use App\Filament\Resources\TournamentResource\Pages;
use App\Filament\Resources\TournamentResource\RelationManagers;
use App\Models\Community;
use App\Models\Tournament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TournamentResource extends Resource
{
    protected static ?string $model = Tournament::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $modelLabel = 'torneio';
    
    protected static ?string $pluralModelLabel = 'torneios';

    protected static ?string $navigationGroup = 'Comunidades';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Torneio')
                    ->columns(2)
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('tournamentCover')
                            ->collection('tournamentCovers')
                            ->label('Imagem:')
                            ->columnSpan(2)
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                            ->required(),
                        Forms\Components\Select::make('community_id')
                            ->label('Comunidade:')
                            ->options(Community::where('is_tournament', true)->pluck('name', 'id'))
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label('Título:')
                            ->placeholder('Título do torneio...')
                            ->minLength(3)
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $state, Set $set) => $set('slug', Str::slug($state)))
                            ->required(),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug:')
                            ->placeholder('Slug do torneio...')
                            ->unique('tournaments', 'slug', fn ($record) => isset($record) ? $record : null)
                            ->columnSpan(2)
                            ->disabled(),
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
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('tournamentCover')
                    ->collection('tournamentCovers')
                    ->label('Imagem:')
                    ->conversion('small'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título:')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('community.name')
                    ->label('Comunidade:'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status:')
                    ->badge()
                    ->formatStateUsing(fn ($state) => TournamentStatusEnum::labels()[$state->value])
                    ->color(fn ($state) => TournamentStatusEnum::color()[$state->value]),
                Tables\Columns\ToggleColumn::make('is_private')
                    ->label('Privado:')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('community_id')
                    ->label('Comunidade:')
                    ->options(Community::where('is_tournament', true)->pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status:')
                    ->options(TournamentStatusEnum::labels()),
                Tables\Filters\SelectFilter::make('is_private')
                    ->label('Condição:')
                    ->options([
                        'private' => 'Mostrar somente os Privados',
                        'public' => 'Mostrar somente os Públicos',
                    ])
                    ->query(function (Builder $query, $state) {
                        switch ($state['value']) {
                            case 'private':
                                return $query->where('is_private', true);
                            case 'public':
                                return $query->where('is_private', false);
                            default:
                                return $query;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->color(Color::Purple),
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->color('info'),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTournaments::route('/'),
            'create' => Pages\CreateTournament::route('/create'),
            'edit' => Pages\EditTournament::route('/{record}/edit'),
        ];
    }
}
