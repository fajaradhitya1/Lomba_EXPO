<?php

namespace App\Observers;

use App\Models\Course;
use App\Jobs\SyncCourseToFirebase;

class CourseObserver
{
    public function created(Course $course): void
    {
        SyncCourseToFirebase::dispatch($course);
    }

    public function updated(Course $course): void
    {
        SyncCourseToFirebase::dispatch($course);
    }
}