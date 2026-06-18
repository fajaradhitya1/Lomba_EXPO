<?php
namespace App\Observers;

use App\Models\Module;
use App\Jobs\SyncModuleToFirestoreJob;

class ModuleObserver
{
    public function created(Module $module): void
    {
        SyncModuleToFirestoreJob::dispatch(
            $module->id,
            'created'
        );
    }

    public function updated(Module $module): void
    {
        SyncModuleToFirestoreJob::dispatch(
            $module->id,
            'updated'
        );
    }

    public function deleted(Module $module): void
    {
        SyncModuleToFirestoreJob::dispatch(
            $module->id,
            'deleted'
        );
    }
}