<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';



    protected function getStats(): array
    {
        $sucursalesWords = auth()->user()->hasRole('super') ? 'Clientes Registrados' : 'Clientes Registrados en mis Sucursales';
        return [
            Stat::make('Clientes', function(){
                    if (auth()->user()->hasRole('super')) {
                        return \App\Models\Client::count();
                    }else{
                        return \App\Models\Client::whereIn('branch_id', auth()->user()->branches->pluck('id')->toArray())->count();
                    }
                })
                ->description($sucursalesWords)
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
                //sucursales
            Stat::make('Sucursales', function(){
                    if (auth()->user()->hasRole('super')) {
                        return \App\Models\Branch::count();
                    }else{
                        return \App\Models\Branch::whereIn('id', auth()->user()->branches->pluck('id')->toArray())->count();
                    }
                })
                ->description('Sucursales Registradas')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),
            //prestamos loan
            Stat::make('Prestamos', function(){
                    if (auth()->user()->hasRole('super')) {
                        return \App\Models\Loan::count();
                    }else{
                        return \App\Models\Loan::whereIn('branch_id', auth()->user()->branches->pluck('id')->toArray())->count();
                    }
                })
                ->description('Prestamos Registrados')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),
        ];

    }
}
