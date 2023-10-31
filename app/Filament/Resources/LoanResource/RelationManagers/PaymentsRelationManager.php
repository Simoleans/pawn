<?php

namespace App\Filament\Resources\LoanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Loan;
use App\Models\Payments;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'pagos';

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
                Tables\Columns\TextColumn::make('type_payment')
                ->label('Tipo de Pago'),
                Tables\Columns\TextColumn::make('amount')
                ->label('Monto'),
                Tables\Columns\TextColumn::make('discount')
                ->label('Descuento')
                ->placeholder('Sin descuento.'),
                //created_at
                Tables\Columns\TextColumn::make('created_at')
                ->label('Fecha de Pago')
                ->sortable(),
            ])
            ->recordTitle(fn ($record) => $record->type_payment)
            ->filters([
                //
            ])
            ->filters([
                Filter::make('Amortizaciones')
                    ->query(fn (Builder $query): Builder => $query->where('type_payment','amortization')),
                Filter::make('Renovaciones')
                    ->query(fn (Builder $query): Builder => $query->where('type_payment','renovation')),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                        ->label('Desde'),
                        DatePicker::make('created_until')
                        ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                /* Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), */
                Tables\Actions\Action::make('print_garanty')
                    ->label('Imprimir')
                    //->hidden(fn (Loan $loan) => $loan->state != 'verified')
                    ->icon("heroicon-m-document")
                    ->url(fn (Payments $payments): string => route('print.payment', $payments->id)),
                Tables\Actions\Action::make('print_garanty')
                    ->label('Eliminar')
                    ->color('danger')
                    ->requiresConfirmation()
                    //->hidden(fn (Loan $loan) => $loan->state != 'verified')
                    ->icon("heroicon-m-trash")
                    ->action(function ($record) {
                       //dd($record->loan->renovation,$record->loan,$record);
                       $loan = Loan::find($record->loan_id);
                       if($record->type_payment == 'renovation'){
                            $renovation = $record->loan->renovation;

                            $loan->renovation = $renovation - 1;
                            $loan->date_contract_expiration = \Carbon\Carbon::parse($loan->date_contract_expiration)->subMonths(1)->format('Y-m-d');
                            $loan->save();
                       }else if($record->type_payment == 'amortization'){
                            $pay = $loan->capital + $record->amount;
                            $loan->capital = $loan->capital + $record->amount;

                            $loan->utility = $pay * $loan->interest_rate / 100;
                            $loan->balance_pay = $pay + $loan->utility;
                            //$loan->save();
                            $loan->save();
                        }else if($record->type_payment == 'complete'){
                            $loan->state = 'verified';
                            $loan->save();
                        }
                            $record->delete();

                        return redirect()->route('filament.admin.resources.loans.edit',  ['record' => $loan->id] + ['activeRelationManager' => 1]);
                    }),
                    //->url(fn (Payments $payments): string => route('print.payment', $payments->id)),
            ])
            ->emptyStateHeading('No tiene pagos');
            /* ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]); */
    }
}
