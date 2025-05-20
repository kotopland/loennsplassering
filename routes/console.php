<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('employee-cvs:delete-old-records')->dailyAt('00:00');
Schedule::command('employee-cvs:delete-emtpy-records')->dailyAt('08:00');
Schedule::command('queue:work --stop-when-empty --timeout=540 --tries=3 ')->everyMinute()->withoutOverlapping()->runInBackground();
