<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunityResource\Pages;
use App\Filament\Resources\CommunityResource\RelationManagers;
use App\Models\Community;
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

class CommunityResource extends Resource
{
    protected static ?string $model = Community::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $modelLabel = 'comunidade';
    
    protected static ?string $pluralModelLabel = 'comunidades';

    protected static ?string $navigationGroup = 'Comunidades';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('communityAvatar')
                    ->collection('communityAvatars')
                    ->label('Imagem:')
                    ->columnSpan(2)
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nome:')
                    ->placeholder('Nome da comunidade...')
                    ->minLength(3)
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $state, Set $set) => $set('slug', Str::slug($state)))
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug:')
                    ->placeholder('Slug da comunidade...')
                    ->unique('communities', 'slug', fn ($record) => isset($record) ? $record : null)
                    ->disabled(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true),
                Forms\Components\Toggle::make('is_tournament')
                    ->label('Tôrneios')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('communityAvatar')
                    ->collection('communityAvatars')
                    ->label('Imagem:'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome:')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Ativo:'),
                Tables\Columns\ToggleColumn::make('is_tournament')
                    ->label('Tôrneios:'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListCommunities::route('/'),
            'create' => Pages\CreateCommunity::route('/create'),
            'edit' => Pages\EditCommunity::route('/{record}/edit'),
        ];
    }
}
