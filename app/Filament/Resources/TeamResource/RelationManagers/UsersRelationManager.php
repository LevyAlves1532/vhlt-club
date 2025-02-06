<?php

namespace App\Filament\Resources\TeamResource\RelationManagers;

use App\Filament\Resources\TeamResource\Pages\ListTeams;
use App\Models\Team;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Membros';

    protected static ?string $modelLabel = 'membro';
    
    protected static ?string $pluralModelLabel = 'membros';

    public function form(Form $form): Form
    {
        $users = User::where('is_active', true)
            ->whereDoesntHave('teams', function ($query) {
                $query->where('teams.id', $this->ownerRecord->id);
            })
            ->pluck('name', 'id');

        return $form
            ->schema([
                Forms\Components\Hidden::make('team_id')
                    ->default($this->ownerRecord->id),
                Forms\Components\Select::make('user_id')
                    ->label('Usuário:')
                    ->options(fn ($operation) => $operation === 'create' ? $users : User::where('is_active', true)->pluck('name', 'id'))
                    ->disabled(fn ($operation) => $operation === 'edit')
                    ->columnSpan(2)
                    ->searchable()
                    ->required(),
                Forms\Components\Toggle::make('is_allowed')
                    ->label('Permitido'),
                Forms\Components\Toggle::make('is_accepted')
                    ->label('Aceitou'),
                Forms\Components\Toggle::make('is_leader')
                    ->label('Lider')
                    ->hidden(fn ($operation) => $this->ownerRecord->users()->wherePivot('is_leader', true)->count() > 0 && $operation === 'create'),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome:')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail:')
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
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Vincular usuário a um time')
                    ->using(function ($data) {
                        $team = $this->ownerRecord;
                        $is_leader = $team->users()->wherePivot('is_leader', true)->count() === 0 
                            ? $data['is_leader'] 
                            : false;

                        $team->users()->attach($data['user_id'], [
                            'is_allowed' => $data['is_allowed'],
                            'is_accepted' => $data['is_accepted'],
                            'is_leader' => $is_leader,
                            'is_active' => $data['is_active'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        return $team;
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
                        $team = $this->ownerRecord;

                        $team->users()->detach($record->id);

                        if ($team->users()->wherePivot('is_leader', true)->count() === 0) {
                            $user_id = null;

                            $user_all_permission = $team->users()->wherePivot('is_allowed', true)
                                ->wherePivot('is_accepted', true)
                                ->orderBy('team_user.created_at', 'desc')
                                ->first();

                            if ($user_all_permission) {
                                $user_id = $user_all_permission->id;
                            }

                            $user_accept = $team->users()->wherePivot('is_accepted', true)
                                ->orderBy('team_user.created_at', 'desc')
                                ->first();

                            if ($user_accept && !$user_id) {
                                $user_id = $user_accept->id;
                            }

                            if ($user_id) {
                                $team->users()->updateExistingPivot($user_id, [
                                    'is_leader' => true,
                                    'is_allowed' => true,
                                ]);
                            } else {
                                $team->users()->detach();
                                $team->delete();

                                return redirect(ListTeams::getUrl());
                            }
                        }
                        
                        return $this->ownerRecord;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
