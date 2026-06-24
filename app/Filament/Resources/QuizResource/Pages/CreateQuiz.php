<?php

namespace App\Filament\Resources\QuizResource\Pages;

use App\Filament\Resources\QuizResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuiz extends CreateRecord
{
    protected static string $resource = QuizResource::class;

    // Tambahkan fungsi ini untuk memastikan 'type' selalu terisi 'quiz'
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'quiz';
        return $data;
    }

    public function getHeading(): string
    {
        return 'Tambah Kuis';
    }
}