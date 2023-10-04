<?php

namespace App\Filament\Resources\LoanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    //protected static string $title = 'Pagos';


    public  function getTableHeading(): string
    {
        return 'Pagos del Contrato';
    }

    public function getTableModelLabel(): ?string
    {
        return 'Pago';
    }

    public function isReadOnly(): bool
    {
        return false;
    }




    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('type_payment')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type_payment')
            ->columns([
                Tables\Columns\TextColumn::make('type_payment'),
                Tables\Columns\TextColumn::make('amount'),
            ])
            ->recordTitle(fn ($record) => $record->type_payment)
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                /* Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), */
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->emptyStateHeading('No tiene pagos');
            /* ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]); */
    }
}
