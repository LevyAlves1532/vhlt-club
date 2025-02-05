<?php

namespace App\Filament\Resources;

use App\Enum\TournamentStatusEnum;
use App\Filament\Resources\TeamResource\Pages;
use App\Filament\Resources\TeamResource\RelationManagers;
use App\Filament\Resources\TeamResource\RelationManagers\UsersRelationManager;
use App\Models\Community;
use App\Models\Team;
use App\Models\Tournament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $modelLabel = 'time';

    protected static ?string $pluralModelLabel = 'times';

    protected static ?string $navigationGroup = 'Comunidades';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Time')
                    ->columns(2)
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('teamFlag')
                            ->collection('teamFlags')
                            ->label('Bandeira:')
                            ->columnSpan(2)
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                            ->required(),
                        Forms\Components\Select::make('tournament_id')
                            ->label('Torneio:')
                            ->options(Tournament::where('status', TournamentStatusEnum::OPEN)->pluck('title', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nome:')
                            ->placeholder('Nome do time...')
                            ->minLength(3)
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\ColorPicker::make('color')
                            ->label('Cor do Timer:')
                            ->columnSpan(2)
                            ->required(),
                        Forms\Components\Toggle::make('is_open')
                            ->label('Time Aberto'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Time Aberto')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('teamFlag')
                    ->collection('teamFlags')
                    ->label('Bandeira:')
                    ->conversion('small'),
                Tables\Columns\TextColumn::make('tournament.title')
                    ->label('Torneio:'),
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
                Tables\Filters\SelectFilter::make('tournament_id')
                    ->label('Condição:')
                    ->options(Community::where('is_tournament', true)->pluck('name', 'id'))
                    ->query(function (Builder $query, $state) {
                        if (!empty($state['value'])) {
                            return $query->whereHas('tournament', function ($queryTournament) use ($state) {
                                return $queryTournament->whereHas('community', function ($queryCommunity)  use ($state) {
                                    return $queryCommunity->where('id', $state['value']);
                                });
                            });
                        }

                        return $query;
                    }),
                Tables\Filters\SelectFilter::make('tournament_id')
                    ->label('Torneio:')
                    ->options(Tournament::all()->pluck('title', 'id')),
                Tables\Filters\SelectFilter::make('is_private')
                    ->label('Condição:')
                    ->options([
                        'open' => 'Mostrar somente os abertos',
                        'closed' => 'Mostrar somente os fechados',
                        'active' => 'Mostrar somente os ativos',
                        'inactive' => 'Mostrar somente os inativos',
                    ])
                    ->query(function (Builder $query, $state) {
                        switch ($state['value']) {
                            case 'open':
                                return $query->where('is_open', true);
                            case 'closed':
                                return $query->where('is_open', false);
                            case 'active':
                                return $query->where('is_active', true);
                            case 'inactive':
                                return $query->where('is_active', false);
                            default:
                                return $query;
                        }
                    })
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
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
