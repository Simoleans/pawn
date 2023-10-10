<?php

namespace App\Filament\Resources\LoanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;
use App\Models\Client;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use App\Models\Item;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'articulos';


    protected function afterCreate(): void
    {
        dd('afterCreate');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('image_url')
                ->label('Foto del Artículo')
                ->image()
                ->columnSpanFull(),
                /* Forms\Components\Select::make('client_id')
                    ->label('Cliente')
                    ->required()
                    ->relationship('client', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Client $record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->preload(), */
                Forms\Components\Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                /* Forms\Components\Select::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->required(), */
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del Artículo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('condition')
                    ->label('Condición')
                    ->options([
                        'NEW' => 'Nuevo',
                        'USED' => 'Usado',
                        'DAMAGED' => 'Dañado',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('estimated_value')
                    ->label('Valor Estimado')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('currency')
                ->label('Moneda')
                ->placeholder('Seleccione una moneda')
                ->options([
                    'BOLIVIANOS' => 'BOB Bs',
                    'USD' => 'USD $',
                ])
                ->default('BOLIVIANOS')
                ->required(),
                Forms\Components\Select::make('state')
                ->label('Estado')
                ->options([
                    'pending' => 'Pendiente',
                    'pawned' => 'Pagado',
                    'retrievable' => 'Regresado',
                    'withdrawn' => 'Retirado',
                    'lost' => 'Perdido',
                    'for_sale' => 'En Venta',
                ])
                ->default('pending')
                //->disabled()
                ->hidden()
                ->dehydrated()
                ->required(),
                /* Forms\Components\TextInput::make('sale_price')
                    ->label('Precio de Venta')
                    ->required()
                    ->numeric(), */
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Artículo')
                    ->searchable(),

                Tables\Columns\TextColumn::make('estimated_value')
                    ->label('Valor Estimado')
                    ->summarize(Sum::make()->label('Valor Soportado'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency')
                    ->label('Moneda')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->hidden(fn (Item $item) => $item->loan->state == 'verified'),
                Tables\Actions\DeleteAction::make()
                    ->action(function ($record) {
                       //dd($record->loan->state);
                        if ($record->loan->state == 'verified') {
                            Notification::make()
                                ->title('No se puede eliminar el artículo, El préstamo ya fue verificado')
                                ->warning()
                                ->send();
                            return false;
                        }else{
                            $record->delete();
                            return true;
                        }

                    }),
            ])

            //->action(fn ($record) => $record->delete()),
           /*  ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->hidden(fn (Item $item) => dd($item)),
                ]),
            ]) */
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
