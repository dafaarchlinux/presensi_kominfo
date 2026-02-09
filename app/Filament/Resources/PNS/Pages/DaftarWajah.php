<?php

namespace App\Filament\Resources\PNS\Pages;

use App\Filament\Resources\PNS\PNSResource;
use App\Models\PNS;
use Filament\Resources\Pages\Page;

class DaftarWajah extends Page
{
    protected static string $resource = PNSResource::class;

    protected static bool $shouldRegisterNavigation = false;

    public PNS $record;

    public function mount(PNS $record): void
    {
        $this->record = $record;
    }

    public function getHeading(): string
    {
        return 'Daftar Wajah ' . $this->record->nama;
    }

    public function getSubheading(): string
    {
        return 'Pendaftaran Wajah';
    }

    public function getView(): string
    {
        return 'filament.resources.p-n-s.pages.daftar-wajah';
    }
}
