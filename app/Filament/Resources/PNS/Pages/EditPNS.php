<?php

namespace App\Filament\Resources\PNS\Pages;

use App\Filament\Resources\PNS\PNSResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPNS extends EditRecord
{
    protected static string $resource = PNSResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
