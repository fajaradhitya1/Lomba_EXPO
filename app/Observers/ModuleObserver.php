<?php

namespace App\Observers;

use App\Models\Module;
use App\Jobs\SyncModuleToFirebase;
use App\Jobs\SyncQuizToFirebase;

class ModuleObserver
{
    public function created(Module $module): void
    {
        $this->dispatchSync($module);
    }

    public function updated(Module $module): void
    {
        $this->dispatchSync($module);
    }

    private function dispatchSync(Module $module): void
    {
        if ($module->type === 'quiz') {
            SyncQuizToFirebase::dispatch($module);
        } else {
            SyncModuleToFirebase::dispatch($module);
        }
    }
}

