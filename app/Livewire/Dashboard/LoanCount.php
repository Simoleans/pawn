<?php

namespace App\Livewire\Dashboard;

use App\Models\Loan;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LoanCount extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Card::make('Contratos',Loan::count()),
        ];
    }
}
