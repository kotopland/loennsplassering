<?php

namespace App\Console\Commands;

namespace App\Console\Commands;

use App\Models\EmployeeCV;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteEmptyRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee-cvs:delete-emtpy-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete empty EmployeeCV records older than one day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Calculate the cutoff date (1 year ago from today).
        $cutoffDate = Carbon::now()->subHour();

        // Delete old EmployeeCV records and log the count.
        $deletedCount = EmployeeCV::whereNull('work_start_date')
            ->whereNull('birth_date')
            ->whereNull('job_title')
            ->whereNull('education')
            ->whereNull('work_experience')
            ->where('email_sent', 0)
            ->where('created_at', '<', $cutoffDate)->delete();

        // Output the result.
        $this->info("Deleted {$deletedCount} EmployeeCV records with empty fields older than 1 day.");
    }
}
