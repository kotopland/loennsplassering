<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('employee-cvs:delete-old')->dailyAt('00:00');
Schedule::command('queue:work --tries=3 --stop-when-empty')->everyMinute()->withoutOverlapping()->runInBackground();
