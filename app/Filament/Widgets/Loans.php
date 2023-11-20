<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use IbrahimBougaoua\FilamentRatingStar\Actions\RatingStarColumn;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Models\Client;
use Filament\Notifications\Notification;
use Filament\Forms\Set;
use Filament\Forms\Get;
use App\Models\Payments;
use App\Models\DataAfterLoan;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Filament\Forms\Components\Select;
use App\Enums\Department;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Actions;



class Loans extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    //title of the widget
    protected static ?string $heading = 'Últimos Préstamos';

    protected function getTableHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),

            ExportAction::make()
                ->exports([
                    ExcelExport::make()
                        ->fromTable()
                        ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                        ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                        ->withColumns([
                            Column::make('updated_at'),
                        ])
                ]),
        ];
    }


    public function table(Table $table): Table
    {
        return $table
        ->query(
            Loan::query()
                    ->where('state', '!=', 'completed')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code_contract')
                    ->label('Contrato')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.code')
                    ->label('Código Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Cliente'),
                    //->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'borrador' => 'gray',
                        'reviewing' => 'warning',
                        'payment_complete' => 'success',
                        'verified' => 'success',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.document')
                    ->label('CI Cliente')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_contract')
                    ->label('Fecha de contrato')
                    ->dateTime('Y-m-d')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_contract_expiration')
                    ->label('Fecha de vencimiento')
                    ->dateTime('Y-m-d')
                    ->searchable(),

            ])

            ->actions([
                //Tables\Actions\EditAction::make(),
                //Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('updateAuthor')
                    ->label('Pagar')
                    ->hidden(fn (Loan $loan) => $loan->state == 'payment_complete' || $loan->state == 'rejected' || $loan->state == 'borrador' || $loan->date_contract_expiration < now())
                    ->icon("heroicon-m-credit-card")
                    ->modalIcon('heroicon-m-credit-card')
                    ->modalHeading(fn (Loan $loan) => "Pagar el contrato {$loan->code_contract}")
                    ->form(self::getFormModalPayment())
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
                        //last payments
                        $last_payment = Payments::where('loan_id',$loan->id)->orderBy('id','desc')->first();
                        if($last_payment){


                            redirect()->route('print.payment', $last_payment->id);
                        }

                    })
                    ->slideOver(),
                Tables\Actions\Action::make('verified')
                    ->label('Verificar Contrato')
                    ->visible(fn (Loan $loan) => $loan->state != 'verified')
                    ->icon("heroicon-m-check-circle")
                    //change label button
                    ->requiresConfirmation()
                    ->modalHeading(fn (Loan $loan) => "Verificar el contrato {$loan->code_contract}")
                    ->modalDescription('Esta seguro de verificar este contrato?.')
                    ->modalSubmitActionLabel('Si, verificar')
                    ->form(self::getFormModalVerified())
                        ->action(function (array $data, Payments $payment,Loan $loan, DataAfterLoan $dataAfterLoan) {
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

                        })
                    ->slideOver(),
                    Tables\Actions\Action::make('print_garanty')
                        ->label('Imprimir Garantia')
                        ->hidden(fn (Loan $loan) => $loan->state != 'verified')
                        ->icon("heroicon-m-document")
                        ->url(fn (Loan $loan): string => route('print.garanty', $loan->id)),

            ])->filters([
                DateRangeFilter::make('created_at'),

            ])
            ->bulkActions([
                ExportBulkAction::make()
            ])

            /* ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]) */
            /* ->recordAction(Tables\Actions\ViewAction::class)
            ->recordUrl(null) */
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getFormModalPayment() {
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

    public static function getFormModalVerified() {
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
                            ->default(fn (Loan $loan) => $loan->date_contract)
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        TextInput::make('date_contract_expiration')
                            ->label('Fecha de vencimiento')
                            ->default(fn (Loan $loan) => $loan->date_contract_expiration)
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        TextInput::make('interest_rate')
                            ->label('Tasa de interés (%)')
                            ->default(fn (Loan $loan) => $loan->interest_rate)
                            ->suffix('%')
                            ->required()
                            ->disabled(),

                        TextInput::make('balance_pay')
                            ->label('Saldo a pagar')
                            ->default(fn (Loan $loan) => $loan->balance_pay)
                            ->disabled()
                            ->dehydrated()
                            ->required(),


                ]),


        ];
    }
}
