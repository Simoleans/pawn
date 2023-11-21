<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use Filament\Resources\Pages\Page;

class Query extends Page
{
    protected static string $resource = LoanResource::class;

    protected static string $view = 'filament.resources.loan-resource.pages.query';
}
