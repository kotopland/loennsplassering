<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('employee-cvs:delete-old-records')->dailyAt('00:00')->timezone('Europe/Oslo');;
Schedule::command('employee-cvs:delete-emtpy-records')->dailyAt('08:00')->timezone('Europe/Oslo');;
Schedule::command('backup:run')->dailyAt('23:30')->timezone('Europe/Oslo');
