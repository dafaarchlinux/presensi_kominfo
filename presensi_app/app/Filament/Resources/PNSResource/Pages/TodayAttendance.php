<?php

namespace App\Filament\Resources\PNSResource\Pages;

use App\Filament\Resources\PNSResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;

class TodayAttendance extends ListRecords
{
    protected static string $resource = PNSResource::class;

    protected static ?string $title = 'Rekap Absensi Hari Ini';

    public function table(Table $table): Table
    {
        $today = now()->toDateString();

        return $table
            ->query(
                DB::table('absensis')
                    ->join('p_n_s', 'absensis.pns_id', '=', 'p_n_s.id')
                    ->where('absensis.tanggal', $today)
                    ->select('absensis.*', 'p_n_s.nama', 'p_n_s.nip')
            )
            ->columns([
                TextColumn::make('nama')->label('Nama Pegawai')->searchable()->sortable(),
                TextColumn::make('nip')->label('NIP')->searchable(),
                TextColumn::make('jam_masuk')->label('Jam Masuk')->badge()->color('success'),
                TextColumn::make('status')->label('Status Masuk')->badge()->color(fn (string $state): string => match ($state) {
                    'Tepat Waktu' => 'success',
                    'Terlambat' => 'danger',
                    default => 'gray',
                }),
                TextColumn::make('jam_pulang')->label('Jam Pulang')->badge()->color('info')->default('-'),
                TextColumn::make('metode')->label('Metode')->badge(),
            ]);
    }
}
