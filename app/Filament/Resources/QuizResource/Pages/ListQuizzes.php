<?php

namespace App\Filament\Resources\QuizResource\Pages;

use App\Filament\Resources\QuizResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuizzes extends ListRecords
{
    protected static string $resource = QuizResource::class;

    // Mengubah judul halaman utama daftar kuis
    public function getHeading(): string
    {
        return 'Daftar Kuis';
    }

    protected function getHeaderActions(): array
    {
        return [
            // Mengubah label tombol agar tidak tertulis "New module"
            Actions\CreateAction::make()->label('Tambah Kuis'),
        ];
    }
}