<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use Filament\Actions;
use App\Models\Loan;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
//use Filament\Forms\Components\Actions\Action;

class EditLoan extends EditRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Iniciar Contrato')
                ->requiresConfirmation()
                ->action(function(Loan $loan) {
                    $loan->state = 'verified';
                    $loan->save();
                }),
            //Actions\DeleteAction::make(),

        ];
    }
}
