<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanResource\Pages;
use App\Filament\Resources\LoanResource\RelationManagers;
use App\Models\{Loan,Payments,DataAfterLoan};
use Filament\Forms;
use Filament\Forms\Components\Select;
use App\Enums\Department;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use App\Actions\ResetStars;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Table;
use App\Models\Client;
use Filament\Notifications\Notification;
use Filament\Forms\Set;
use Filament\Forms\Get;
use App\Models\Item;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Wizard;


class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('Gestión');
    }

    public static function getLabel(): ?string
    {
        return __('Préstamo');
    }

    public static function getNavigationLabel(): string
    {
        return __('Préstamos');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            Action::make('saveAnother')
                ->label('Save & create another')
                ->action('saveAnother')
                ->keyBindings(['mod+shift+s'])
                ->color('secondary'),
            $this->getCancelFormAction(),
        ];
    }

public function saveAnother()
    {
        $resources = static::getResource();
        $this->create();
        $this->redirect($resources::getUrl('create'));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (Client $record) => "{$record->first_name} {$record->last_name} / {$record->code}")
                    ->searchable(['first_name', 'last_name'])
                    ->createOptionForm(self::getFormClient())
                    ->preload(),

                Select::make('branch_id')
                    ->label('Sucursal')
                    ->placeholder('Seleccione una sucursal')
                    ->options(
                        \App\Models\Branch::all()->pluck('name', 'id')
                    )
                    ->required(),

                    Grid::make(4)
                        ->schema([

                            Select::make('state')
                                ->label('Estado')
                                ->placeholder('Seleccione un estado')
                                ->options([
                                    'borrador' => 'Borrador',
                                    'reviewing' => 'Reviewing',
                                    'published' => 'Published',
                                ])
                                ->default('borrador')
                                ->disabled()
                                ->required(),
                            Select::make('currency')
                                ->label('Moneda')
                                ->placeholder('Seleccione una moneda')
                                ->options([
                                    'BOLIVIANOS' => 'BOB Bs',
                                    'USD' => 'USD $',
                                ])
                                ->default('BOLIVIANOS')
                                ->required(),
                            TextInput::make('capital')
                                ->label('Capital')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {

                                    //porcent capital and interest_rate
                                    $set('utility', $get('capital') * $get('interest_rate')  / 100);
                                })
                                ->required()
                                ->numeric(),
                            TextInput::make('interest_rate')
                                ->label('Tasa de interés (%)')
                                ->live(onBlur: true)
                                //->afterStateUpdated(fn (Set $set, ?string $state) => $set('conservation_expense', $state - 3))
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {

                                    //porcent capital and interest_rate
                                    $set('utility', $get('capital') * $get('interest_rate') / 100);
                                    $set('conservation_expense', $state - 3);
                                    $set('balance_pay', $get('capital') + $get('utility'));
                                })
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
                            ->default(now()->format('Y-m-d'))
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        TextInput::make('date_contract_expiration')
                            ->label('Fecha de vencimiento')
                            ->type('date')
                            ->default(now()->addMonths(1)->format('Y-m-d'))
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

    public static function table(Table $table): Table
    {
        return $table
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
                    ->label('Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'borrador' => 'gray',
                        'reviewing' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_contract')
                    ->label('Fecha de contrato')
                    ->dateTime('Y-m-d')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_contract_expiration')
                    ->label('Fecha de vencimiento')
                    ->dateTime('Y-m-d')
                    ->searchable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('updateAuthor')
                ->label('Pagar')
                ->icon("heroicon-m-credit-card")
                ->modalIcon('heroicon-m-credit-card')
                ->modalHeading(fn (Loan $loan) => "Pagar el contrato {$loan->code_contract}")
                ->form(self::getFormModalPayment())
                ->action(function (array $data, Payments $payment,Loan $loan, DataAfterLoan $dataAfterLoan) {
                    $payment->fill($data); //fill save payment

                    if($data['type_payment'] == 'renovation') {
                        $loan->date_contract_expiration = $loan->date_contract_expiration->addMonths(1)->format('Y-m-d');
                        $loan->renovation = $loan->renovation + 1;
                        $loan->save();
                    }else if($data['type_payment'] == 'amortization') {
                        //save actually data loan in dataAfterLoan
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
                        //update data loan
                        $pay = $loan->capital - $data['amount'];
                        $loan->capital = $loan->capital - $data['amount'];
                        //$get('capital') * $get('interest_rate') / 100);
                        //$set('conservation_expense', $state - 3);
                        //$set('balance_pay', $get('capital') + $get('utility'));
                        $loan->utility = $pay * $loan->interest_rate / 100;
                        $loan->balance_pay = $pay + $loan->utility;
                        $loan->save();



                    }

                    $payment->save();

                })
                //->modalHidden(fn (Loan $loan,Payments $payment) => dd($loan,$payment))
                ->after(function (array $data,Payments $payment,Loan $loan)  {
                    //dd($payment,$data);
                    Notification::make()
                        ->title('Pago realizado')
                        ->success()
                        ->send();


                    //last payments
                    $last_payment = Payments::where('loan_id',$loan->id)->orderBy('id','desc')->first();
                    //dd($last_payment);
                    redirect()->route('print.payment', $last_payment->id);

                    return false;
                })
                ->slideOver()
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
            //RelationManagers\ContractArticlesRelationManager::class,
            RelationManagers\ItemsRelationManager::class,
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoan::route('/create'),
            'edit' => Pages\EditLoan::route('/{record}/edit'),
        ];
    }

    public static function getFormClient()
    {
        return [
        Grid::make(3)
            ->schema([
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
                    ->label('email')
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
            ]),
        ];
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
                            ->type('date')
                            ->default(fn (Loan $loan) => $loan->date_contract->format('Y-m-d'))
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        TextInput::make('date_contract_expiration')
                            ->label('Fecha de vencimiento')
                            ->type('date')
                            ->default(fn (Loan $loan) => $loan->date_contract_expiration->format('Y-m-d'))
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('type_payment')
                            ->label('Tipo de pago')
                            ->placeholder('Seleccione un tipo de pago')
                            ->options([
                                'amortization' => 'Amortizacion',
                                'renovation' => 'Renovación',
                            ])
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state, Loan $loan) {
                                if($state == 'renovation') {
                                    $set('amount', $get('utility'));
                                }else {
                                    $set('amount', '');
                                }
                            })
                            ->required(),

                        textInput::make('amount')
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
                            ->disabled(fn (Get $get, Set $set, Loan $loan) => $get('type_payment') == 'renovation' )
                            ->dehydrated()
                            ->numeric(),
                ]),


        ];
    }
}
