<?php

namespace App\Filament\Resources\PNS\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class PNSForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make("nip")
                ->label("NIP Pegawai")
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(30)
                ->placeholder("Masukkan NIP"),

            TextInput::make("nama")
                ->label("Nama Lengkap PNS")
                ->required()
                ->maxLength(255)
                ->placeholder("Masukkan Nama Lengkap beserta Gelar"),

            Select::make("unit_kerja_id")
                ->label("Unit Kerja / Bidang")
                ->relationship("unitKerja", "nama_unit_kerja")
                ->searchable()
                ->preload()
                ->required()
                ->placeholder("Pilih Unit Kerja")
                ->createOptionForm([
                    TextInput::make("nama_unit_kerja")
                        ->label("Nama Unit Kerja / Bidang Baru")
                        ->required()
                        ->maxLength(255),
                ]),

            TextInput::make("no_telp")
                ->label("Nomor Telepon / WhatsApp")
                ->tel()
                ->maxLength(20)
                ->placeholder("Contoh: 081234567890"),

            TextInput::make("password")
                ->label("Password Login PNS")
                ->password()
                ->revealable()
                ->helperText("Kosongkan jika tidak ingin mengubah password")
                ->maxLength(255)
                ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                ->dehydrated(fn ($state) => filled($state)),
        ]);
    }
}
