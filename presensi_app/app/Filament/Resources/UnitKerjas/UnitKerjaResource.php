<?php

namespace App\Filament\Resources\UnitKerjas;

use App\Filament\Resources\UnitKerjas\Pages\ManageUnitKerjas;
use App\Models\UnitKerja;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnitKerjaResource extends Resource
{
    protected static ?string $model = UnitKerja::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nama_unit_kerja';

    /* =========================
     | FORM (CREATE / EDIT)
     ========================= */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama_unit_kerja')
                ->label('Nama Unit Kerja')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
        ]);
    }

    /* =========================
     | TABLE (LIST DATA)
     ========================= */
    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_unit_kerja')

            ->columns([
                TextColumn::make('nama_unit_kerja')
                    ->label('Nama Unit Kerja')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pns_count')
                    ->counts('pns')
                    ->label('Jumlah PNS')
                    ->sortable()
                    ->suffix(' Orang'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])

            ->filters([
                // Filter unit kerja yang punya PNS
                Filter::make('memiliki_pns')
                    ->label('Memiliki PNS')
                    ->query(fn (Builder $query) =>
                        $query->has('pns')
                    ),

                // Filter unit kerja kosong
                Filter::make('tanpa_pns')
                    ->label('Tanpa PNS')
                    ->query(fn (Builder $query) =>
                        $query->doesntHave('pns')
                    ),
            ])

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('nama_unit_kerja');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUnitKerjas::route('/'),
        ];
    }
}
