<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class FaceAttendance extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-face-smile';
    protected static ?string $navigationLabel = 'Presensi Wajah';
    protected static ?string $title = 'Presensi Wajah';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.face-attendance';
}