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
            Stat::make('Contratos Vencidos 16-30 Días', function () {
                if (auth()->user()->hasRole('super')) {
                    return \App\Models\Loan::where('date_contract_expiration', '>=', now()->subDays(30))->where('date_contract_expiration', '<', now()->subDays(16))->count();
                } else {
                    return \App\Models\Loan::whereIn('branch_id', auth()->user()->branches->pluck('id')->toArray())->where('date_contract_expiration', '>=', now()->subDays(30))->where('date_contract_expiration', '<', now()->subDays(16))->count();
                }
            })->description('Contratos Vencidos Entre 16 y 30 Días')
            ->descriptionIcon('heroicon-m-calendar')
            ->color('warning'),

            Stat::make('Contratos Vencidos 31-60 Días', function () {
                if (auth()->user()->hasRole('super')) {
                    return \App\Models\Loan::where('date_contract_expiration', '>=', now()->subDays(60))->where('date_contract_expiration', '<', now()->subDays(31))->count();
                } else {
                    return \App\Models\Loan::whereIn('branch_id', auth()->user()->branches->pluck('id')->toArray())->where('date_contract_expiration', '>=', now()->subDays(60))->where('date_contract_expiration', '<', now()->subDays(31))->count();
                }
            })->description('Contratos Vencidos Entre 31 y 60 Días')
            ->descriptionIcon('heroicon-m-calendar')
            ->color('warning'),

            Stat::make('Contratos Vencidos 61 Días en Adelante', function () {
                if (auth()->user()->hasRole('super')) {
                    return \App\Models\Loan::where('date_contract_expiration', '<', now())->count();
                } else {
                    return \App\Models\Loan::whereIn('branch_id', auth()->user()->branches->pluck('id')->toArray())->where('date_contract_expiration', '<', now())->count();
                }
            })->description('Contratos Vencidos Desde Hace 61 Días')
            ->descriptionIcon('heroicon-m-calendar')
            ->color('danger')
        ];

    }
}
