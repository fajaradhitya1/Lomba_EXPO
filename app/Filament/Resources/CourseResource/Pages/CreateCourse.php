<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use App\Jobs\SyncCourseToFirebase; // Import Job-nya
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function afterCreate(): void
    {
        // Jalankan sinkronisasi course ke Firebase
        SyncCourseToFirebase::dispatch($this->record);
    }
}