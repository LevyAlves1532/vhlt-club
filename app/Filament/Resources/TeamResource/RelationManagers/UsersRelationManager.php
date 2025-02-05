<?php

namespace App\Filament\Resources\TeamResource\RelationManagers;

use App\Models\Team;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
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
                    ->options($users)
                    ->columnSpan(2)
                    ->searchable()
                    ->required(),
                Forms\Components\Toggle::make('is_allowed')
                    ->label('Permitido'),
                Forms\Components\Toggle::make('is_accepted')
                    ->label('Aceitou'),
                Forms\Components\Toggle::make('is_leader')
                    ->label('Lider'),
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
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function ($data) {
                        $team = Team::find($data['team_id']);
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
                    ->beforeFormFilled(function ($record) {
                        dd($record->pivot);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
