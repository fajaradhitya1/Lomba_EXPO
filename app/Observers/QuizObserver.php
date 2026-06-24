<?php

namespace App\Observers;

use App\Models\Quiz; // Pastikan kamu punya Model Quiz
use App\Jobs\SyncQuizToFirebase;

class QuizObserver
{
    public function created(Quiz $quiz): void
    {
        SyncQuizToFirebase::dispatch($quiz);
    }

    public function updated(Quiz $quiz): void
    {
        SyncQuizToFirebase::dispatch($quiz);
    }

    public function deleted(Quiz $quiz): void
    {
        // SyncDeleteQuizToFirebase::dispatch($quiz);
    }
}