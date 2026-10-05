<?php

namespace App\Filament\Resources\PNS\Tables;

use App\Filament\Resources\PNS\PNSResource;
use App\Models\PNS;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PNSTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("nip")
                    ->label("NIP")
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage("NIP disalin ke clipboard"),

                TextColumn::make("nama")
                    ->label("Nama PNS")
                    ->searchable()
                    ->sortable()
                    ->weight("bold"),

                TextColumn::make("unitKerja.nama_unit_kerja")
                    ->label("Unit Kerja")
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color("info"),

                TextColumn::make("face_embedding")
                    ->label("Status Wajah")
                    ->state(fn (PNS $record): string => filled($record->face_embedding) ? "Terdaftar" : "Belum Terdaftar")
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        "Terdaftar" => "success",
                        "Belum Terdaftar" => "danger",
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        "Terdaftar" => "heroicon-m-check-circle",
                        "Belum Terdaftar" => "heroicon-m-x-circle",
                    }),

                TextColumn::make("no_telp")
                    ->label("No. Telepon")
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make("created_at")
                    ->label("Terdaftar Pada")
                    ->dateTime("d M Y H:i")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make("unit_kerja_id")
                    ->label("Unit Kerja")
                    ->relationship("unitKerja", "nama_unit_kerja")
                    ->searchable()
                    ->preload(),
            ])

            ->recordActions([
                EditAction::make(),

                Action::make("daftar-wajah")
                    ->label(fn (PNS $record): string => filled($record->face_embedding) ? "Update Wajah" : "Daftar Wajah")
                    ->icon("heroicon-o-camera")
                    ->color(fn (PNS $record): string => filled($record->face_embedding) ? "warning" : "success")
                    ->url(fn (PNS $pns): string =>
                        route("filament.admin.resources.p-n-s.daftar-wajah", $pns)),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort("nama");
    }
}

