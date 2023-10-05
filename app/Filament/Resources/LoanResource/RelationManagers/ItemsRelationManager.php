<?php

namespace App\Filament\Resources\LoanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Set;
use Filament\Forms\Get;
use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;

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
                    //->createOptionForm(self::formItem())
                    ->unique()
                    ->required(),
            ]);
    }

    public static function formItem()
    {
        return [
                FileUpload::make('image_url')
                ->label('Foto del Artículo')
                ->image()
                ->columnSpanFull(),
                Forms\Components\Select::make('client_id')
                    ->label('Cliente')
                    ->options(
                        \App\Models\Client::getFullNameQuery()->get()->pluck('name', 'id')
                    )
                    //->relationship('client', 'first_name')
                    //->getOptionLabelFromRecordUsing(fn (Client $record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->preload(),
                Forms\Components\Select::make('category_id')
                    ->label('Categoría')
                    ->options(
                        \App\Models\Category::all()->pluck('name', 'id')
                    )
                    //->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('branch_id')
                    ->label('Sucursal')
                    ->options(
                        \App\Models\Branch::all()->pluck('name', 'id')
                    )
                    //->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
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
                ->disabled()
                ->dehydrated()
                ->required(),
                Forms\Components\TextInput::make('sale_price')
                    ->label('Precio de Venta')
                    ->required()
                    ->numeric(),
        ];
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
