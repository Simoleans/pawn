<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Enums\Relationship;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PersonalReferencesRelationManager extends RelationManager
{
    protected static string $relationship = 'personalReferences';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('mobile')
                    ->label('Celular')
                    ->required()
                    ->maxLength(20),
                Forms\Components\Select::make('relationship')
                    ->label('Parentesco/Relación')
                    ->options(Relationship::toArray()),
                Forms\Components\TextInput::make('address')
                    ->label('Dirección')
                    ->maxLength(1024),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('mobile')
                    ->label('Celular'),
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
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
