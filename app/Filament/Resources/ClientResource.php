<?php

namespace App\Filament\Resources;

use App\Enums\Department;
use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use IbrahimBougaoua\FilamentRatingStar\Actions\RatingStar;
use IbrahimBougaoua\FilamentRatingStar\Actions\RatingStarColumn;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'bi-person-video2';

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
        return __('Cliente');
    }

    public static function getNavigationLabel(): string
    {
        return __('Clientes');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                RatingStar::make('rating')
                    ->label('Calificaciòn'),
                Forms\Components\TextInput::make('code')
                    ->label('Código')
                    ->required()
                    ->disabled()
                    ->default('-'),
                Forms\Components\TextInput::make('first_name')
                    ->label('Nombres')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('last_name')
                    ->label('Apellidos')
                    ->required()
                    ->maxLength(50),
                Forms\Components\Select::make('branch_id')
                    ->label('Sucursal')
                    ->placeholder('Seleccione una sucursal')
                    ->options(function () {
                        if (auth()->user()->hasRole('super')) {
                            return \App\Models\Branch::pluck('name', 'id');
                        }else{
                            return auth()->user()->branches->pluck('name', 'id');
                        }
                    })
                    ->required(),
                Forms\Components\TextInput::make('document')
                    ->label('Cédula de identidad')
                    ->required()
                    ->maxLength(20),
                Forms\Components\Select::make('issued')
                    ->label(__('Expedido en'))
                    ->required()
                    ->options(Department::toArray()),
                Forms\Components\TextInput::make('mobile')
                    ->label('Celular')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(64),
                Forms\Components\Textarea::make('address')
                    ->label('Dirección')
                    ->required()
                    ->maxLength(1024)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('address_references')
                    ->label('Referencias adicionales a la dirección')
                    ->maxLength(1024)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                RatingStarColumn::make('rating')
                ->label('Calificaciòn'),
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Apellido')
                    ->searchable(),
                Tables\Columns\TextColumn::make('document')
                    ->label('Cédula')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mobile')
                    ->label('Celular')
                    ->searchable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creado por')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\PersonalReferencesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
