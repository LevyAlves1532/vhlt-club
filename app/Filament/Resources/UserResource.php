<?php

namespace App\Filament\Resources;

use App\Enum\PermissionsEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'usuário';
    
    protected static ?string $pluralModelLabel = 'usuários';

    protected static ?string $navigationGroup = 'Usuários';

    public static function form(Form $form): Form
    {
        $user = Auth::user();

        return $form
            ->schema([
                Forms\Components\Section::make('Usuário')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome:')
                            ->placeholder('Nome do usuário...')
                            ->minLength(3)
                            ->maxLength(100)
                            ->required()
                            ->disabled(fn ($record) => ($record && $user->id === $record->id)),
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail:')
                            ->placeholder('E-mail do usuário...')
                            ->email()
                            ->disabled(fn ($record) => ($record && $user->id === $record->id))
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->visibleOn('create')
                            ->label('Senha:')
                            ->placeholder('Senha do usuário...')
                            ->password()
                            ->revealable()
                            ->minValue(8)
                            ->maxValue(255)
                            ->required(),
                        Forms\Components\Select::make('permission')
                            ->label('Tipo de Usuário:')
                            ->disabled(fn ($record) => ($user->permission !== PermissionsEnum::SUPER_ADMIN || ($record && $user->id === $record->id)))
                            ->columnSpan(fn ($operation) => $operation !== 'create' ? 2 : 1)
                            ->options(PermissionsEnum::labels()),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativo:')
                            ->default(true)
                            ->disabled(fn ($record) => ($record && $user->id === $record->id)),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome:')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail:')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('permission')
                    ->label('Tipo de Usuário:')
                    ->badge()
                    ->formatStateUsing(fn ($state) => PermissionsEnum::labels()[$state->value]),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Ativo:')
                    ->disabled(fn ($record) => $record->permission === PermissionsEnum::SUPER_ADMIN),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('permission')
                    ->options(PermissionsEnum::labels()),
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Condição:')
                    ->options([
                        'active' => 'Mostrar somente os ativos',
                        'inactive' => 'Mostrar somente os desativos',
                    ])
                    ->query(function (Builder $query, $state) {
                        switch ($state['value']) {
                            case 'active':
                                return $query->where('is_active', true);
                            case 'inactive':
                                return $query->where('is_active', false);
                            default:
                                return $query;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->color(Color::Purple)
                    ->hidden(fn ($record) => $user->id === $record->id),
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->color('info')
                    ->hidden(fn ($record) => ($user->id === $record->id || $record->permission === PermissionsEnum::SUPER_ADMIN)),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->hidden(fn ($record) => $user->id === $record->id),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
