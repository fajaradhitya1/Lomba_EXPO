<?php

namespace App\Filament\Resources\ModuleResource\Pages;

use App\Filament\Resources\ModuleResource;
use App\Jobs\SyncModuleToFirebase;
use Filament\Resources\Pages\CreateRecord;

class CreateModule extends CreateRecord
{
    protected static string $resource = ModuleResource::class;

    protected function afterCreate(): void
    {
        // Kirim ke background job, TIDAK akan menyebabkan recursion
        SyncModuleToFirebase::dispatch($this->record);
    }
}