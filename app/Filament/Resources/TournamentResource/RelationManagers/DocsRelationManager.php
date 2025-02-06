<?php

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class DocsRelationManager extends RelationManager
{
    protected static string $relationship = 'docs';

    protected static ?string $title = 'Documentos';

    protected static ?string $modelLabel = 'documento';
    
    protected static ?string $pluralModelLabel = 'documentos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('tournamentDoc')
                    ->collection('tournamentDocs')
                    ->label('Documentos:')
                    ->columnSpan(2)
                    ->acceptedFileTypes(['application/pdf'])
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('Título:')
                    ->placeholder('Título do que o documento se trata...')
                    ->columnSpan(2)
                    ->maxLength(255)
                    ->minLength(8)
                    ->required(),
                Forms\Components\RichEditor::make('description')
                    ->label('Descrição')
                    ->placeholder('Resumo do documento...')
                    ->columnSpan(2)
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título:')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Ativo:'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
