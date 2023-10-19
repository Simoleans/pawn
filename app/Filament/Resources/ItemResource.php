<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Client;
use App\Models\Item;
use App\Models\Loan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Actions\Action;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'gameicon-jewel-crown';

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('super')) {
            return $query;
        }else{
            return $query->whereIn('branch_id', auth()->user()->branches->pluck('id')->toArray());
        }
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Gestión');
    }

    public static function getLabel(): ?string
    {
        return __('Articulo');
    }

    public static function getNavigationLabel(): string
    {
        return __('Articulos');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('image_url')
                    ->label('Foto del Artículo')
                    ->image()
                    ->columnSpanFull(),
                Forms\Components\Select::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Client $record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->preload(),
                Forms\Components\Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Artículo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('condition')
                    ->label('Condición')
                    ->view('tables.columns.condition-item')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state_label')
                    ->label('Estado'),
                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Cliente'),
                Tables\Columns\TextColumn::make('client.document')
                    ->label('Cliente')
                    ->hidden()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable(),
                ImageColumn::make('image_url')
                    ->label('Imagen')
                    ->extraImgAttributes(['loading' => 'lazy']),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('client.document')
                    ->label('CI Cliente')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    //loan
                Tables\Columns\TextColumn::make('loan.code_contract')
                    ->label('Prestamo')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                //ReplicateAction::make(),

            ]);
            /* ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]); */
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
