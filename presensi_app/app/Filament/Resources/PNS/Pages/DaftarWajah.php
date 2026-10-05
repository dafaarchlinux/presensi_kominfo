<?php

namespace App\Filament\Resources\PNS\Pages;

use App\Filament\Resources\PNS\PNSResource;
use App\Models\PNS;
use Filament\Notifications\Notification;
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
        return "Pendaftaran Wajah: " . $this->record->nama;
    }

    public function getSubheading(): string
    {
        return "Ambil dan simpan vektor wajah untuk absensi otomatis";
    }

    public function getView(): string
    {
        return "filament.resources.p-n-s.pages.daftar-wajah";
    }

    public function simpanWajahMulti($descriptors)
    {
        try {
            $this->record->update([
                "face_embedding" => is_array($descriptors) ? $descriptors : json_decode($descriptors, true),
            ]);

            Notification::make()
                ->title("Pendaftaran Berhasil")
                ->body("Data wajah pegawai berhasil disimpan ke database.")
                ->success()
                ->send();

            return true;
        } catch (\Exception $e) {
            Notification::make()
                ->title("Gagal Menyimpan")
                ->body("Terjadi kesalahan: " . $e->getMessage())
                ->danger()
                ->send();

            return false;
        }
    }
}
