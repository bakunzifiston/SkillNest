<?php

namespace App\Console\Commands;

use App\Models\QuizAttempt;
use Illuminate\Console\Command;

class CloseExpiredQuizAttempts extends Command
{
    protected $signature = 'quizzes:close-expired';

    protected $description = 'Submit quiz attempts whose time limit has run out (using the answers saved so far)';

    public function handle(): int
    {
        $closed = 0;

        QuizAttempt::query()
            ->whereNull('submitted_at')
            ->whereHas('quiz', fn ($q) => $q->whereNotNull('time_limit_minutes'))
            ->with('quiz')
            ->chunkById(100, function ($attempts) use (&$closed) {
                foreach ($attempts as $attempt) {
                    if ($attempt->isExpired()) {
                        $attempt->closeAsTimedOut();
                        $closed++;
                    }
                }
            });

        $this->info("Closed {$closed} expired quiz attempt(s).");

        return self::SUCCESS;
    }
}
