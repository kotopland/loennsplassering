<?php

namespace App\Console\Commands;

namespace App\Console\Commands;

use App\Models\EmployeeCV;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldEmployeeCVs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee-cvs:delete-old-viewed';

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
        $cutoffDate = Carbon::now()->subYear();

        // Delete old EmployeeCV records and log the count.
        $deletedCount = EmployeeCV::where('last_viewed', '<', $cutoffDate)->delete();

        // Output the result.
        $this->info("Deleted {$deletedCount} EmployeeCV records viewed older than 1 year.");
    }
}
