<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
                Forms\Components\Hidden::make('user_id')
                    ->default($this->ownerRecord->id),
                Forms\Components\Select::make('team_id')
                    ->label('Time:')
                    ->options(Team::all()->pluck('name', 'id'))
                    ->columnSpan(2)
                    ->searchable()
                    ->live(onBlur: true)
                    ->required(),
                Forms\Components\Toggle::make('is_allowed')
                    ->label('Permitido'),
                Forms\Components\Toggle::make('is_accepted')
                    ->label('Aceitou'),
                Forms\Components\Toggle::make('is_leader')
                    ->label('Lider')
                    ->hidden(function (Get $get) {
                        $is = false;

                        if ($get('team_id')) {
                            $is = Team::find($get('team_id'))->users()->wherePivot('is_leader', true)->count() > 0;
                        }
                        return $is;
                    })
                    ->reactive(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Ativo')
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
                Tables\Columns\IconColumn::make('pivot.is_allowed')
                    ->label('Permitido:')
                    ->boolean(),
                Tables\Columns\IconColumn::make('pivot.is_accepted')
                    ->label('Aceito:')
                    ->boolean(),
                Tables\Columns\IconColumn::make('pivot.is_leader')
                    ->label('Lider:')
                    ->boolean(),
                Tables\Columns\IconColumn::make('pivot.is_active')
                    ->label('Ativo:')
                    ->boolean(),
                Tables\Columns\TextColumn::make('tournament.title')
                    ->label('Torneio:')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ColorColumn::make('color')
                    ->label('Cor:')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_open')
                    ->label('Time Aberto:')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Time Ativo:')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Vincular a um time')
                    ->using(function ($data, $action) {
                        $user = $this->ownerRecord;

                        if (!empty($data['is_leader'])) {
                            $count_leader = Team::find($data['team_id'])->users()->wherePivot('is_leader', true)->count();

                            // dd($count_leader);

                            if ($count_leader > 0 || !$data['is_allowed'] || !$data['is_accepted']) {
                                Notification::make()
                                    ->title('Lider Recusado')
                                    ->body('Este usuário só pode ser aceito como lider se estiver permitido e aceito sua participação no time e se não houver outro lider')
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }
                        } 

                        $user->teams()->attach($data['team_id'], [
                            'is_allowed' => $data['is_allowed'],
                            'is_accepted' => $data['is_accepted'],
                            'is_leader' => !empty($data['is_leader']) ? true : false,
                            'is_active' => $data['is_active'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        return $user;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->before(function ($data, $action) {
                        $team = Team::find($data['team_id']);
                        $team_leader = $team->users()->wherePivot('is_leader', true)->first();

                        if ((!$data['is_allowed'] || !$data['is_accepted']) && $data['is_leader']) {
                            Notification::make()
                                ->title('Lider Recusado')
                                ->body('Este usuário só pode ser aceito como lider se estiver permitido e aceito sua participação no time')
                                ->danger()
                                ->send();

                            $action->halt();
                        }

                        if ($data['is_leader'] && $team_leader) {
                            $team->users()->updateExistingPivot($team_leader->id, [
                                'is_leader' => false
                            ]);
                        }
                    }),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->using(function ($record) {
                        $record->users()->detach($this->ownerRecord->id);

                        if ($record->users()->wherePivot('is_leader', true)->count() === 0) {
                            $user_id = null;

                            $user_all_permission = $record->users()
                                ->wherePivot('is_allowed', true)
                                ->wherePivot('is_accepted', true)
                                ->orderBy('team_user.created_at', 'desc')
                                ->first();

                            if ($user_all_permission) {
                                $user_id = $user_all_permission->id;
                            }

                            $user_accept = $record->users()->wherePivot('is_accepted', true)
                                ->orderBy('team_user.created_at', 'desc')
                                ->first();

                            if ($user_accept && !$user_id) {
                                $user_id = $user_accept->id;
                            }

                            if ($user_id) {
                                $record->users()->updateExistingPivot($user_id, [
                                    'is_leader' => true,
                                    'is_allowed' => true,
                                ]);
                            } else {
                                $record->users()->detach();
                                $record->delete();
                            }
                        }
                        
                        return $record;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
