<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use Filament\Actions;
use App\Models\Loan;
use App\Models\Payments;
use App\Models\DataAfterLoan;
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
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Forms\Components\Hidden;
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
                Action::make('Amortizar')
                ->color('success')
                ->icon("heroicon-m-credit-card")
                ->hidden(fn (Loan $loan) =>  $loan->state !== 'verified')
                ->disabled(fn (Loan $loan) =>  $loan->state !== 'verified')
                ->form(self::getFormModalPayment('amortization'))
                    ->action(function (array $data, Payments $payment,Loan $loan, DataAfterLoan $dataAfterLoan) {
                        if($loan->state == 'borrador'){
                            Notification::make()
                                ->title('El contrato no esta verificado')
                                ->warning()
                                ->send();
                            return false;
                        }else if($loan->state == 'payment_complete'){
                            Notification::make()
                                ->title('El contrato ya fue pagado')
                                ->warning()
                                ->send();
                            return false;
                        }

                        if($loan->date_contract_expiration < now()){
                            Notification::make()
                                ->title('El contrato esta vencido')
                                ->warning()
                                ->send();
                            return false;
                        }

                        $payment->fill($data); //fill save payment

                        if($data['type_payment'] == 'renovation') {
                            $loan->date_contract_expiration = \Carbon\Carbon::parse($loan->date_contract_expiration)->addMonths(1)->format('Y-m-d');
                            $loan->renovation = $loan->renovation + 1;
                            $loan->save();
                        }else if($data['type_payment'] == 'amortization') {
                            //save actually data loan in dataAfterLoan

                            //update data loan
                            if($data['amount'] > $loan->capital){
                                Notification::make()
                                    ->title('El monto a pagar es mayor al capital')
                                    ->warning()
                                    ->send();
                                return false;
                            }


                            $pay = $loan->capital - $data['amount'];
                            $loan->capital = $loan->capital - $data['amount'];

                            $loan->utility = $pay * $loan->interest_rate / 100;
                            $loan->balance_pay = $pay + $loan->utility;
                            $loan->save();

                        }else if($data['type_payment'] == 'complete') {

                            $loan->state = 'payment_complete';
                            $loan->save();

                        }

                        Notification::make()
                            ->title('Pago realizado')
                            ->success()
                            ->send();

                        $payment->save();

                        $dataAfterLoan->fill([
                            'capital' => $loan->capital,
                            'interest_rate' => $loan->interest_rate,
                            'conservation_expense' => $loan->conservation_expense,
                            'legal_interest' => $loan->legal_interest,
                            'utility' => $loan->utility,
                            'balance_pay' => $loan->balance_pay,
                            'loan_id' => $loan->id,
                            'payment_id' => $payment->id,
                        ]);
                        $dataAfterLoan->save();

                    })
                    ->after(function (array $data,Loan $loan)  {
                        //redirect view loan
                        return redirect()->route('filament.admin.resources.loans.edit',  ['record' => $loan->id] + ['activeRelationManager' => 1]);

                    })
                    ->slideOver(),
                Action::make('Renovar')
                    ->color('success')
                    ->icon("heroicon-m-credit-card")
                    ->hidden(fn (Loan $loan) =>  $loan->state !== 'verified')
                    ->disabled(fn (Loan $loan) =>  $loan->state !== 'verified')
                    ->form(self::getFormModalPayment('renovation'))
                        ->action(function (array $data, Payments $payment,Loan $loan, DataAfterLoan $dataAfterLoan) {
                            if($loan->state == 'borrador'){
                                Notification::make()
                                    ->title('El contrato no esta verificado')
                                    ->warning()
                                    ->send();
                                return false;
                            }else if($loan->state == 'payment_complete'){
                                Notification::make()
                                    ->title('El contrato ya fue pagado')
                                    ->warning()
                                    ->send();
                                return false;
                            }

                            if($loan->date_contract_expiration < now()){
                                Notification::make()
                                    ->title('El contrato esta vencido')
                                    ->warning()
                                    ->send();
                                return false;
                            }

                            $payment->fill($data); //fill save payment

                            if($data['type_payment'] == 'renovation') {
                                $loan->date_contract_expiration = \Carbon\Carbon::parse($loan->date_contract_expiration)->addMonths(1)->format('Y-m-d');
                                $loan->renovation = $loan->renovation + 1;
                                $loan->save();
                            }else if($data['type_payment'] == 'amortization') {
                                //save actually data loan in dataAfterLoan

                                //update data loan
                                if($data['amount'] > $loan->capital){
                                    Notification::make()
                                        ->title('El monto a pagar es mayor al capital')
                                        ->warning()
                                        ->send();
                                    return false;
                                }

                                $pay = $loan->capital - $data['amount'];
                                $loan->capital = $loan->capital - $data['amount'];

                                $loan->utility = $pay * $loan->interest_rate / 100;
                                $loan->balance_pay = $pay + $loan->utility;
                                $loan->save();

                            }else if($data['type_payment'] == 'complete') {

                                $loan->state = 'payment_complete';
                                $loan->save();

                            }

                            Notification::make()
                                ->title('Pago realizado')
                                ->success()
                                ->send();

                            $payment->save();

                            $dataAfterLoan->fill([
                                'capital' => $loan->capital,
                                'interest_rate' => $loan->interest_rate,
                                'conservation_expense' => $loan->conservation_expense,
                                'legal_interest' => $loan->legal_interest,
                                'utility' => $loan->utility,
                                'balance_pay' => $loan->balance_pay,
                                'loan_id' => $loan->id,
                                'payment_id' => $payment->id,
                            ]);
                            $dataAfterLoan->save();

                    })
                    ->after(function (array $data,Payments $payment,Loan $loan)  {
                        //redirect view loan
                        return redirect()->route('filament.admin.resources.loans.edit',  ['record' => $loan->id] + ['activeRelationManager' => 1]);

                    })
                    ->slideOver(),
            Action::make('Pagar Completo')
                ->color('success')
                ->hidden(fn (Loan $loan) =>  $loan->state !== 'verified')
                ->icon("heroicon-m-credit-card")
                ->disabled(fn (Loan $loan) =>  $loan->state !== 'verified')
                ->form(self::getFormModalPayment('complete'))
                    ->action(function (array $data, Payments $payment,Loan $loan, DataAfterLoan $dataAfterLoan) {
                        if($loan->state == 'borrador'){
                            Notification::make()
                                ->title('El contrato no esta verificado')
                                ->warning()
                                ->send();
                            return false;
                        }else if($loan->state == 'payment_complete'){
                            Notification::make()
                                ->title('El contrato ya fue pagado')
                                ->warning()
                                ->send();
                            return false;
                        }

                        if($loan->date_contract_expiration < now()){
                            Notification::make()
                                ->title('El contrato esta vencido')
                                ->warning()
                                ->send();
                            return false;
                        }

                        $payment->fill($data); //fill save payment

                        if($data['type_payment'] == 'renovation') {
                            $loan->date_contract_expiration = \Carbon\Carbon::parse($loan->date_contract_expiration)->addMonths(1)->format('Y-m-d');
                            $loan->renovation = $loan->renovation + 1;
                            $loan->save();
                        }else if($data['type_payment'] == 'amortization') {
                            //save actually data loan in dataAfterLoan

                            //update data loan
                            if($data['amount'] > $loan->capital){
                                Notification::make()
                                    ->title('El monto a pagar es mayor al capital')
                                    ->warning()
                                    ->send();
                                return false;
                            }

                            $pay = $loan->capital - $data['amount'];
                            $loan->capital = $loan->capital - $data['amount'];

                            $loan->utility = $pay * $loan->interest_rate / 100;
                            $loan->balance_pay = $pay + $loan->utility;
                            $loan->save();

                        }else if($data['type_payment'] == 'complete') {

                            $loan->state = 'payment_complete';
                            $loan->save();

                        }

                        Notification::make()
                            ->title('Pago realizado')
                            ->success()
                            ->send();

                        $payment->save();

                        $dataAfterLoan->fill([
                            'capital' => $loan->capital,
                            'interest_rate' => $loan->interest_rate,
                            'conservation_expense' => $loan->conservation_expense,
                            'legal_interest' => $loan->legal_interest,
                            'utility' => $loan->utility,
                            'balance_pay' => $loan->balance_pay,
                            'loan_id' => $loan->id,
                            'payment_id' => $payment->id,
                        ]);
                        $dataAfterLoan->save();

                    })
                    ->after(function (array $data,Payments $payment,Loan $loan)  {
                        //redirect view loan
                        return redirect()->route('filament.admin.resources.loans.edit',  ['record' => $loan->id] + ['activeRelationManager' => 1]);

                    })
                    ->slideOver(),
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
                            //->required(),

                ]),
                Grid::make(2)
                    ->schema([
                        TextInput::make('total_discount')
                            ->label('Total con descuento')
                            ->numeric()
                            ->disabled()
                            ->hidden(fn (Get $get, Set $set, Loan $loan) => $get('type_payment') != 'complete')
                            ->columnStart(2)
                            //->required(),

                ]),


        ];
    }

}
