<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class MontoSoportado extends Field
{
    protected string $view = 'forms.components.monto-soportado';
    public $sum = 0;

    public function mount()
    {
        dd("sss");
        $this->sum =2500;
        //return $this;
    }
}
