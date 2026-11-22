<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Submit quiz attempts whose time ran out, even if the student never came back (needs the usual cron: php artisan schedule:run).
Schedule::command('quizzes:close-expired')->everyMinute();
