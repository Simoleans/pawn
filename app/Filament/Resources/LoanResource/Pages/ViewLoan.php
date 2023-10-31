<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Loan;
use Filament\Notifications\Notification;
use App\Models\Payments;
use App\Models\DataAfterLoan;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Grid;

class ViewLoan extends ViewRecord
{
    protected static string $resource = LoanResource::class;

    //protected static string $view = 'filament.resources.loans.pages.view-loan';


    /* public function getTabs(): array
    {
        return [
            'all' => Tab::make('All customers'),
            'active' => Tab::make('Active customers')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true)),
            'inactive' => Tab::make('Inactive customers')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', false)),
        ];
    } */

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Iniciar Contrato')
                ->requiresConfirmation()
                ->icon('heroicon-m-clipboard-document-check')
                //->disabled(fn (Loan $loan) => $loan->state !== 'borrador')
                ->hidden(fn (Loan $loan) => $loan->state != 'borrador')
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
                    //redirect()->route('filament.admin.resources.loans.edit',  $loan->id);
                }),

            Action::make('Imprimir Garantía')
                ->hidden(fn (Loan $loan) => $loan->state != 'verified')
                ->icon('heroicon-m-document')
                ->color('warning')
                ->url(fn (Loan $loan): string => route('print.garanty', $loan->id)),

            Action::make('Anular Contrato')
                ->requiresConfirmation()
                ->color('danger')
                ->icon('heroicon-m-no-symbol')
                ->disabled(fn (Loan $loan) => $loan->pagos->count() > 0 || $loan->state !== 'verified')
                ->action(function(Loan $loan) {

                    $loan->state = 'borrador';
                    $loan->save();
                    Notification::make()
                        ->title('El contrato ha sido anulado')
                        ->success()
                        ->send();
                    redirect()->route('filament.admin.resources.loans.edit',  $loan->id);
                }),

        ];
    }

    public static function getFormModalPayment($default = null) {
        return [
            Grid::make(2)
                ->schema([
                        TextInput::make('contract')
                            ->label('Contrato')
                            ->disabled()
                            ->dehydrated()
                            ->default(fn (Loan $loan) => $loan->code_contract)
                            ->required(),
                        Hidden::make('loan_id')
                            ->default(fn (Loan $loan) => $loan->id),
                        Hidden::make('user_id')
                            ->default(fn () => auth()->user()->id),
                        TextInput::make('client')
                            ->label('Cliente')
                            ->disabled()
                            ->dehydrated()
                            ->default(fn (Loan $loan) => $loan->client->code)
                            ->required(),
                        TextInput::make('client_name')
                            ->label('Nombre del cliente')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull()
                            ->default(fn (Loan $loan) => $loan->client->full_name)
                            ->required(),
                        TextInput::make('capital')
                            ->label('Capital')
                            ->disabled()
                            ->dehydrated()
                            ->default(fn (Loan $loan) => $loan->capital)
                            ->required(),
                        TextInput::make('utility')
                            ->label('Utilidad')
                            ->disabled()
                            ->dehydrated()
                            ->default(fn (Loan $loan) => $loan->utility)
                            ->required(),
                        TextInput::make('date_contract')
                            ->label('Fecha de contrato')
                            //->type('date')
                            ->default(fn (Loan $loan) => $loan->date_contract)
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        TextInput::make('date_contract_expiration')
                            ->label('Fecha de vencimiento')
                            //->type('date')
                            ->default(fn (Loan $loan) => $loan->date_contract_expiration)
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('type_payment')
                            ->label('Tipo de pago')
                            ->placeholder('Seleccione un tipo de pago')
                            ->options([
                                'amortization' => 'Amortizacion',
                                'renovation' => 'Renovación',
                                'complete' => 'Pago completo',
                            ])
                            ->default($default)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state, Loan $loan) {
                                if($state == 'renovation') {
                                    $set('amount', $get('utility'));
                                }else if($state == 'complete') {
                                    $set('amount', $get('capital') + $get('utility'));
                                    $set('discount', null);
                                }else {
                                    $set('amount', '');
                                }
                            })
                            ->required(),

                        TextInput::make('amount')
                            ->label('Monto a pagar')
                            ->required()
                            ->minValue(function (Get $get, Set $set, Loan $loan) {
                                if($get('type_payment') == 'renovation') {
                                    return $loan->utility;
                                }else {
                                    return 1;
                                }
                            })
                            ->maxValue(function (Get $get, Set $set, Loan $loan) {
                                if($get('type_payment') == 'renovation') {
                                    return $loan->utility;
                                }
                            })
                            ->default(function (Get $get, Set $set, Loan $loan) {
                                if($get('type_payment') == 'complete') {
                                    return $loan->utility + $loan->capital;
                                }else if($get('type_payment') == 'renovation') {
                                    return $loan->utility;
                                }
                            })
                            ->disabled(fn (Get $get, Set $set, Loan $loan) => $get('type_payment') == 'renovation' || $get('type_payment') == 'complete')
                            ->dehydrated()
                            ->numeric(),
                ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('discount')
                            ->label('Descuento por pronto pago')
                            ->numeric()
                            ->hidden(fn (Get $get, Set $set, Loan $loan) => $get('type_payment') != 'complete')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state, Loan $loan) {
                                if($state) {
                                    $set('total_discount', ($get('capital') + $get('utility')) -  $state ?? 0);
                                }
                            })
                            ->columnStart(2)
                            ->required(),

                ]),
                Grid::make(2)
                    ->schema([
                        TextInput::make('total_discount')
                            ->label('Total con descuento')
                            ->numeric()
                            ->disabled()
                            ->hidden(fn (Get $get, Set $set, Loan $loan) => $get('type_payment') != 'complete')
                            ->columnStart(2)
                            ->required(),

                ]),


        ];
    }




}
