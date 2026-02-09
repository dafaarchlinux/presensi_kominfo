<?php

namespace App\Filament\Resources\PNS;

use App\Filament\Resources\PNS\Pages\DaftarWajah;
use App\Filament\Resources\PNS\Pages\CreatePNS;
use App\Filament\Resources\PNS\Pages\EditPNS;
use App\Filament\Resources\PNS\Pages\ListPNS;
use App\Filament\Resources\PNS\Schemas\PNSForm;
use App\Filament\Resources\PNS\Tables\PNSTable;
use App\Models\PNS;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PNSResource extends Resource
{
    protected static ?string $model = PNS::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return PNSForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PNSTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPNS::route('/'),
            'create' => CreatePNS::route('/create'),
            'edit' => EditPNS::route('/{record}/edit'),

            'daftar-wajah' => DaftarWajah::route('/{record}/daftar-wajah'),
        ];
    }
}
