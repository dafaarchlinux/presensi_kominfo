<?php

namespace App\Filament\Resources\PNS\Pages;

use App\Filament\Resources\PNS\PNSResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPNS extends ListRecords
{
    protected static string $resource = PNSResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
