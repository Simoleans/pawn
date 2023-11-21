<?php

namespace App\Filament\Pages;
use Filament\Actions\Action;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\Loans;
use Livewire\Attributes\Url;


use Filament\Pages\Page;

class LoanQuery extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.loan-query';

    protected static ?string $title = 'Prestamos';



    public $titulo = [];

    #[Url]
    public $from = '';
    #[Url]
    public $to = '';

    //protected static ?string $slug = 'loan-query?from='.$this->from.'&to='.$this->to;

    public static function shouldRegisterNavigation(): bool
{
    return false;
}

    public function getAttrs()
    {
        return [
            'attr-nombre' => '',
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //StatsOverview::class,
            Loans::make(['from' => $this->from, 'to' => $this->to]),
        ];
    }
    public function mount()
    {
        $titulo = $this->getAttrs('titulo');

        $this->titulo = $titulo;

    }

}
