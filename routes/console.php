<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('employee-cvs:delete-old-records')->dailyAt('00:00');
Schedule::command('employee-cvs:delete-emtpy-records')->dailyAt('08:00');
Schedule::command('queue:work --timeout=180 --tries=3 --stop-when-empty')->everyMinute()->withoutOverlapping()->runInBackground();
