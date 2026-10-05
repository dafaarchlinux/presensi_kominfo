<?php

namespace App\Filament\Resources\PNS\Pages;

use App\Filament\Resources\PNS\PNSResource;
use App\Filament\Resources\PNS\Schemas\PNSForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditPNS extends EditRecord
{
    protected static string $resource = PNSResource::class;

    public function form(Schema $schema): Schema
    {
        return PNSForm::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
