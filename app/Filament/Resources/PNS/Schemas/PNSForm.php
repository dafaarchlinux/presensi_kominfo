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

            /*
            =============================
            NIP
            =============================
            */
            TextInput::make('nip')
                ->label('NIP')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(30),

            /*
            =============================
            NAMA
            =============================
            */
            TextInput::make('nama')
                ->label('Nama PNS')
                ->required()
                ->maxLength(255),

            /*
            =============================
            UNIT KERJA
            =============================
            */
            Select::make('unit_kerja_id')
                ->label('Unit Kerja')
                ->relationship('unitKerja', 'nama_unit_kerja')
                ->required()
                ->searchable()
                ->preload(),

            /*
            =============================
            NOMOR TELEPON
            =============================
            */
            TextInput::make('no_telp')
                ->label('Nomor Telepon')
                ->tel()
                ->maxLength(20),

            /*
            =============================
            PASSWORD LOGIN PNS (OPSIONAL)
            =============================
            */
            TextInput::make('password')
                ->label('Password Login PNS')
                ->password()
                ->revealable()
                ->helperText('Kosongkan jika tidak ingin mengubah password')
                ->maxLength(255)
                ->dehydrateStateUsing(
                    fn ($state) => filled($state)
                        ? Hash::make($state)
                        : null
                )
                ->dehydrated(
                    fn ($state) => filled($state)
                ),
        ]);
    }
}
