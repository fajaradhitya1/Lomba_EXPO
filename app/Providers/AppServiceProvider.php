<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Module;
use App\Models\Course;
use App\Observers\ModuleObserver;
use App\Observers\CourseObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        putenv(
            'GOOGLE_APPLICATION_CREDENTIALS=' .
            env('GOOGLE_APPLICATION_CREDENTIALS')
        );
    }

    public function boot(): void
    {
        Module::observe(ModuleObserver::class);
        Course::observe(CourseObserver::class);

        if (env('APP_ENV') === 'production') {
            \URL::forceScheme('https');
        }
    }
}