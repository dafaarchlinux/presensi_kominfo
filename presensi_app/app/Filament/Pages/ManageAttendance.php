<?php

namespace App\Filament\Pages;

use App\Models\Presensi;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManageAttendance extends Page implements HasTable
{
    use InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Rekapitulasi Presensi';
    protected static ?string $title = 'Rekapitulasi Presensi Pegawai';
    protected static ?string $slug = 'manage-attendance';
    
    protected string $view = 'filament.pages.manage-attendance';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak_laporan')
                ->label('Cetak Laporan PDF')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->form([
                    DatePicker::make('dari_tanggal')->label('Dari Tanggal')->default(Carbon::now()->startOfMonth()),
                    DatePicker::make('sampai_tanggal')->label('Sampai Tanggal')->default(Carbon::today()),
                    Select::make('unit_kerja_id')
                        ->label('Unit Kerja')
                        ->options(UnitKerja::pluck('nama_unit_kerja', 'id'))
                        ->placeholder('Semua Unit Kerja'),
                ])
                ->action(function (array $data) {
                    $params = http_build_query([
                        'dari_tanggal' => $data['dari_tanggal'],
                        'sampai_tanggal' => $data['sampai_tanggal'],
                        'unit_kerja_id' => $data['unit_kerja_id'] ?? null,
                    ]);
                    return redirect()->away(route('admin.laporan.cetak.pdf') . '?' . $params);
                }),

            Action::make('ekspor_excel')
                ->label('Ekspor Excel (CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    DatePicker::make('dari_tanggal')->label('Dari Tanggal')->default(Carbon::now()->startOfMonth()),
                    DatePicker::make('sampai_tanggal')->label('Sampai Tanggal')->default(Carbon::today()),
                ])
                ->action(function (array $data) {
                    $params = http_build_query([
                        'dari_tanggal' => $data['dari_tanggal'],
                        'sampai_tanggal' => $data['sampai_tanggal'],
                    ]);
                    return redirect()->away(route('admin.laporan.ekspor.excel') . '?' . $params);
                }),
        ];
    }

    public function getViewData(): array
    {
        $today = Carbon::today()->toDateString();
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $totalHariIni = Presensi::whereDate('tanggal', $today)->count();
        $tepatWaktuHariIni = Presensi::whereDate('tanggal', $today)->whereIn('status', ['Tepat Waktu', 'Lebih Awal'])->count();
        $terlambatHariIni = Presensi::whereDate('tanggal', $today)->where('status', 'Terlambat')->count();

        $totalMingguIni = Presensi::whereBetween('tanggal', [$startOfWeek, $endOfWeek])->count();
        $totalBulanIni = Presensi::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->count();
        $tepatWaktuBulanIni = Presensi::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->whereIn('status', ['Tepat Waktu', 'Lebih Awal'])->count();
        $rasioDisiplin = $totalBulanIni > 0 ? round(($tepatWaktuBulanIni / $totalBulanIni) * 100) : 100;

        return [
            'totalHariIni'      => $totalHariIni,
            'tepatWaktuHariIni' => $tepatWaktuHariIni,
            'terlambatHariIni'  => $terlambatHariIni,
            'totalMingguIni'    => $totalMingguIni,
            'totalBulanIni'     => $totalBulanIni,
            'rasioDisiplin'     => $rasioDisiplin,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Presensi::query()->with(['pns.unitKerja'])->latest('tanggal')->latest('jam_masuk'))
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d M Y') : '-')
                    ->sortable(),

                TextColumn::make('pns.nama')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Presensi $record): string => 'NIP: ' . ($record->pns->nip ?? '-')),

                TextColumn::make('pns.unitKerja.nama_unit_kerja')
                    ->label('Unit Kerja')
                    ->badge()
                    ->color('info')
                    ->default('-')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('jam_masuk')
                    ->label('Jam Masuk')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('H:i:s') : '-')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'secondary')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status Masuk')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Tepat Waktu' => 'success',
                        'Lebih Awal'  => 'info',
                        'Terlambat'   => 'danger',
                        default       => 'secondary',
                    })
                    ->default('-'),

                TextColumn::make('jam_pulang')
                    ->label('Jam Pulang')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('H:i:s') : 'Belum Pulang')
                    ->badge()
                    ->color(fn ($state) => $state ? 'info' : 'gray')
                    ->sortable(),

                TextColumn::make('status_pulang')
                    ->label('Status Pulang')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Tepat Waktu'  => 'success',
                        'Lembur'       => 'primary',
                        'Pulang Cepat' => 'warning',
                        default        => 'secondary',
                    })
                    ->default('-'),

                TextColumn::make('metode')
                    ->label('Metode')
                    ->badge()
                    ->color('primary')
                    ->default('Face Recognition'),
            ])
            ->filters([
                Filter::make('periode')
                    ->form([
                        DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'], fn (Builder $q, $date) => $q->whereDate('tanggal', '>=', $date))
                            ->when($data['sampai_tanggal'], fn (Builder $q, $date) => $q->whereDate('tanggal', '<=', $date));
                    }),

                SelectFilter::make('unit_kerja')
                    ->label('Unit Kerja')
                    ->relationship('pns.unitKerja', 'nama_unit_kerja')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                DeleteAction::make('delete')
                    ->label('Hapus')
                    ->modalHeading('Hapus Data Presensi'),
            ])
            ->bulkActions([
                DeleteBulkAction::make('bulk_delete')
                    ->label('Hapus Terpilih'),
            ])
            ->defaultSort('tanggal', 'desc');
    }
}
