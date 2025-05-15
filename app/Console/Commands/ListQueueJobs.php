<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ListQueueJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:list-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists all jobs in the jobs and failed_jobs tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Jobs in the `jobs` table:');
        $jobs = DB::table('jobs')->get();

        if ($jobs->isEmpty()) {
            $this->line('No jobs found in the `jobs` table.');
        } else {
            $this->table(
                ['ID', 'Queue', 'Payload', 'Attempts', 'Reserved At', 'Available At', 'Created At'],
                $jobs->map(function ($job) {
                    return [$job->id, $job->queue, substr($job->payload, 0, 50) . '...', $job->attempts, $job->reserved_at, $job->available_at, $job->created_at];
                })->map(function ($job) {
                    // Convert timestamps to human-readable dates
                    $job[4] = $job[4] ? date('Y-m-d H:i:s', $job[4]) : null; // Reserved At
                    $job[5] = $job[5] ? date('Y-m-d H:i:s', $job[5]) : null; // Available At
                    $job[6] = $job[6] ? date('Y-m-d H:i:s', $job[6]) : null; // Created At

                    return $job;
                })->toArray()



            );
        }

        $this->info("\nJobs in the `failed_jobs` table:");
        $failedJobs = DB::table('failed_jobs')->get();

        if ($failedJobs->isEmpty()) {
            $this->line('No jobs found in the `failed_jobs` table.');
        } else {
            $this->table(
                ['ID', 'Connection', 'Queue', 'Payload', 'Exception', 'Failed At'],
                $failedJobs->map(function ($job) {
                    return [$job->id, $job->connection, $job->queue, substr($job->payload, 0, 50) . '...', substr($job->exception, 0, 50) . '...', $job->failed_at];
                })->map(function ($job) {
                    // Convert failed_at timestamp to a human-readable date
                    $job[5] = date('Y-m-d H:i:s', strtotime($job[5]));

                    return $job;
                })->toArray()
            );
        }
    }
}
