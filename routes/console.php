<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Log::info('✅ Scheduler is working every miniute! Time: ' . now());
})->everyMinute();

// Example: প্রতি 5 মিনিটে
Schedule::call(function () {
    Log::info('Running every 5 minutes');
})->everyFiveMinutes();

// Example: প্রতিদিন রাত 2টায়
Schedule::call(function () {
    Log::info('Daily task at 2 AM');
})->dailyAt('02:00');
