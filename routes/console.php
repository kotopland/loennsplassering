<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('employee-cvs:delete-old-records')->dailyAt('00:00');
Schedule::command('employee-cvs:delete-emtpy-records')->dailyAt('08:00');
// Schedule::command('queue:work --timeout=180 --tries=3 --stop-when-empty')->everyMinute()->withoutOverlapping()->runInBackground();
Schedule::command('queue:work --tries=3 --stop-when-empty')->everyMinute()->withoutOverlapping()->runInBackground();
// Schedule::command("queue:work --once --name=default --queue=default --backoff=0 --memory=128 --sleep=3 --tries=1")
//     ->everyMinute()
//     ->withoutOverlapping(10)
//     ->timeout(540);
