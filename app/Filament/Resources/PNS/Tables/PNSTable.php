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
                // NIP
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),

                // Nama PNS
                TextColumn::make('nama')
                    ->label('Nama PNS')
                    ->searchable()
                    ->sortable(),

                // Unit Kerja (relasi)
                TextColumn::make('unitKerja.nama_unit_kerja')
                    ->label('Unit Kerja')
                    ->sortable()
                    ->searchable(),

                // Nomor Telepon
                TextColumn::make('no_telp')
                    ->label('No. Telepon')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Tanggal dibuat
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])

            ->filters([
                // Filter berdasarkan Unit Kerja
                SelectFilter::make('unit_kerja_id')
                    ->label('Unit Kerja')
                    ->relationship('unitKerja', 'nama_unit_kerja')
                    ->searchable()
                    ->preload(),
            ])

            ->recordActions([
                EditAction::make(),
                
                 Action::make('daftar-wajah')
                    ->label('Daftar Wajah')
                    ->icon('heroicon-o-camera')
                    ->color('success')
                    ->url(fn (PNS $pns): string =>
                    route('filament.admin.resources.p-n-s.daftar-wajah', $pns))
                
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('nama');
    }
}
