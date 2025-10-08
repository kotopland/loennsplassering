<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

Schedule::command('employee-cvs:delete-old-records')->dailyAt('00:00')->timezone('Europe/Oslo');;
Schedule::command('employee-cvs:delete-emtpy-records')->dailyAt('08:00')->timezone('Europe/Oslo');;
Schedule::command('backup:run')->dailyAt('23:30')->timezone('Europe/Oslo');
Schedule::command('storage:group-read-access')->dailyAt('23:40')->timezone('Europe/Oslo');

Artisan::command('storage:group-read-access', function () {
    $this->info('Setting group read access for storage/app...');
    $storageAppPath = base_path('storage/app' . DIRECTORY_SEPARATOR . config('app.name'));
    $webGroup = 'www-data';
    $currentUser = exec('whoami'); // Get the current user running the artisan command

    try {
        // Set ownership to current user and web group
        $processChown = Process::fromShellCommandline("sudo chown -R {$currentUser}:{$webGroup} \"{$storageAppPath}\"");
        $processChown->run();
        if (!$processChown->isSuccessful()) {
            throw new \RuntimeException($processChown->getErrorOutput());
        }

        // Set directory permissions: rwx for owner/group, rx for others (775)
        $processDir = Process::fromShellCommandline("sudo find \"{$storageAppPath}\" -type d -exec chmod 775 {} \\;");
        $processDir->run();
        if (!$processDir->isSuccessful()) {
            throw new \RuntimeException($processDir->getErrorOutput());
        }

        // Set file permissions: rw for owner/group, r for others (664)
        $processFile = Process::fromShellCommandline("sudo find \"{$storageAppPath}\" -type f -exec chmod 664 {} \\;");
        $processFile->run();
        if (!$processFile->isSuccessful()) {
            throw new \RuntimeException($processFile->getErrorOutput());
        }

        $this->info('Permissions set successfully.');
    } catch (\RuntimeException $e) {
        $this->error('Failed to set permissions: ' . $e->getMessage());
        $this->warn("Ensure the user '{$currentUser}' has sudo privileges configured to run 'chmod' and 'chown' without a password for '{$storageAppPath}'.");
    }
})->purpose('Sets read/write permissions for the web server group on storage/app directory.'); // Laravel 8+ for purpose()

Artisan::command('app:restoredb', function () {
    $path = base_path('storage/app/databaseimport/latest_live_database.sql');

    if (App::environment('production') && ! $this->confirm('Heads Up! This App (' . config('app.name') . ') is running in PRODUCTION MODE!! The database will be wiped with the backup. Do you wish to continue?')) {
        exit();
    }

    if (file_exists($path)) {
        Artisan::call('db:wipe');
        $sql = file_get_contents($path);
        DB::unprepared($sql);
        if (App::environment('local') && $this->confirm('Would you like to reset all the passwords?')) {
            DB::table('users')->update(['password' => Hash::make('password')]);
            dump('Backup Restored completely. All passwords are set to  \'password\' for testing purpose.');
        } else {

            dump('Backup Restored completely');
        }
    } else {
        dump('File does not exist.');
    }
})->purpose('Restore a backup of the database');
