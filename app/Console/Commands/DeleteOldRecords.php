<?php

namespace App\Console\Commands;

use App\Models\EmployeeCV;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee-cvs:delete-old-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete EmployeeCV records viewed older than 1 year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Calculate the cutoff date (1 year ago from today).
        $cutoffDate = Carbon::now()->subYear()->toDateString();

        // Delete old EmployeeCV records that have been viewed before the cutoff date.
        $deletedCount = EmployeeCV::where('last_viewed', '<', $cutoffDate)
            ->whereNotNull('last_viewed')
            ->delete();

        // Output the result.
        $this->info("Deleted {$deletedCount} EmployeeCV records viewed before {$cutoffDate}.");

        // Count the number of records with a NULL 'last_viewed' value.
        $nullLastViewedCount = EmployeeCV::whereNull('last_viewed')->count();

        // Output a warning if there are records with a NULL 'last_viewed' value.
        if ($nullLastViewedCount > 0) {
            $this->warn("{$nullLastViewedCount} EmployeeCV records have a NULL 'last_viewed' value and were not deleted.");
        }

    }
}
