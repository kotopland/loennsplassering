<?php

use Illuminate\Support\Facades\Schedule;

return function (Schedule $schedule) {
    // Schedule the command to run daily at midnight.
    $schedule->command('employee-cvs:delete-old')->dailyAt('00:00');
    $schedule->command('queue:work --tries=3 --stop-when-empty')->everyMinute()->withoutOverlapping()->runInBackground();

};
