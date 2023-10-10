<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use Filament\Actions;
use App\Models\Loan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use App\Models\Client;
use Filament\Forms\Components\Grid;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Forms\Components\ViewField;
//use Filament\Forms\Components\Actions\Action;

class EditLoan extends EditRecord
{
    protected static string $resource = LoanResource::class;
    protected static ?string $model = Loan::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Iniciar Contrato')
                ->requiresConfirmation()
                ->disabled(fn (Loan $loan) => $loan->state !== 'borrador')
                ->action(function(Loan $loan) {

                     //if exists articulos
                     if($loan->articulos->count() == 0){
                        Notification::make()
                            ->title('No existen artículos')
                            ->warning()
                            ->send();
                        return false;
                    }

                    if($loan->articulos->sum('estimated_value') < $loan->capital){
                        Notification::make()
                            ->title('El Valor soportado es menor al capital')
                            ->warning()
                            ->send();
                        return false;
                    }

                    $loan->state = 'verified';
                    $loan->save();
                    Notification::make()
                        ->title('El contrato ha sido iniciado')
                        ->success()
                        ->send();
                    redirect()->route('filament.admin.resources.loans.edit',  $loan->id);
                }),
                Action::make('Anular Contrato')
                ->requiresConfirmation()
                ->color('danger')
                ->disabled(fn (Loan $loan) => $loan->payments->count() > 0 || $loan->state !== 'verified')
                ->action(function(Loan $loan) {

                    $loan->state = 'borrador';
                    $loan->save();
                    Notification::make()
                        ->title('El contrato ha sido anulado')
                        ->success()
                        ->send();
                    redirect()->route('filament.admin.resources.loans.edit',  $loan->id);
                }),
            //Actions\DeleteAction::make(),

        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Client $record) => "{$record->first_name} {$record->last_name} / {$record->code}")
                    ->searchable(['first_name', 'last_name'])
                    ->disabled()
                    ->preload(),

                Select::make('branch_id')
                    ->label('Sucursal')
                    ->placeholder('Seleccione una sucursal')
                    ->options(
                        \App\Models\Branch::all()->pluck('name', 'id')
                    )
                    ->disabled()
                    ->required(),

                    Grid::make(3)
                        ->schema([
                            Select::make('currency')
                                ->label('Moneda')
                                ->placeholder('Seleccione una moneda')
                                ->options([
                                    'BOLIVIANOS' => 'BOB Bs',
                                    'USD' => 'USD $',
                                ])
                                ->disabled()
                                ->default('BOLIVIANOS')
                                ->required(),
                            TextInput::make('capital')
                                ->label('Capital')
                                ->disabled()
                                ->required()
                                ->numeric(),
                            TextInput::make('interest_rate')
                                ->label('Tasa de interés (%)')
                                ->live()
                                ->disabled()
                                ->required()
                                ->telRegex('^[0-9]*$')
                                ->minValue(5)
                                ->maxValue(10)
                                ->numeric(),
                        ]),


                Grid::make(4)
                    ->schema([
                        TextInput::make('legal_interest')
                            ->label('Interés legal (%)')
                            ->suffix('%')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->default(3)
                            ->numeric(),
                        TextInput::make('conservation_expense')
                            ->label('Gastos de conservación (%)')
                            ->suffix('%')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->numeric(),
                        TextInput::make('utility')
                            ->label('Utilidad')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('balance_pay')
                            ->label('Saldo a pagar')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),
                    ]),
                Grid::make(4)
                    ->schema([
                        TextInput::make('date_contract')
                            ->label('Fecha de contrato')
                            ->type('date')
                            //->default(now()->format('Y-m-d'))
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        TextInput::make('date_contract_expiration')
                            ->label('Fecha de vencimiento')
                            ->type('date')
                            //->default(now()->addMonths(1)->format('d/m/Y'))
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        TextInput::make('renovation')
                            ->label('Renovación')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                    ]),

            ]);
    }

}
