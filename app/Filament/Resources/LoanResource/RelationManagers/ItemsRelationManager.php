<?php

namespace App\Filament\Resources\LoanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';


    public  function getTableHeading(): string
    {
        return 'Agregar Artículo';
    }

    public function getTableModelLabel(): ?string
    {
        return 'Artículo';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('item_id')
                    ->label('Artículo')
                    ->placeholder('Seleccione un artículo')
                    ->options(
                        \App\Models\Item::all()->pluck('name', 'id')
                    )
                    ->unique()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('item.name'),
                Tables\Columns\TextColumn::make('item.estimated_value')
                    ->label('Valor Estimado'),
                Tables\Columns\TextColumn::make('item.currency')
                    ->label('Moneda'),
                Tables\Columns\ImageColumn::make('item.image_url')
                    ->label('Imagen')
                    ->extraImgAttributes(['loading' => 'lazy']),

            ])

            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->label('Vincular Artículo'),

            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                //Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                ->label('Vincular Artículo')
                ->icon('heroicon-o-plus'),

            ]);
    }
}
