<?php

namespace App\Jobs;

use App\Services\ExcelGenerationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $applicationId;
    public bool $notifyUser;
    public int $tries = 3;
    public int $timeout = 120; // Increased timeout for generation
    public int $backoff = 60;

    /**
     * Create a new job instance.
     *
     * @param string $applicationId
     * @param bool $notifyUser
     */
    public function __construct(string $applicationId, bool $notifyUser)
    {
        $this->applicationId = $applicationId;
        $this->notifyUser = $notifyUser;
    }

    /**
     * Execute the job.
     *
     * @param ExcelGenerationService $excelGenerationService
     * @return void
     */
    public function handle(ExcelGenerationService $excelGenerationService): void
    {
        $data = $excelGenerationService->generateExcel($this->applicationId);

        // Dispatch notification jobs
        if ($this->notifyUser) {
            ProcessUserSubmissionJob::dispatch($this->applicationId, $data);
        }
        NotifyAdminOfSubmissionJob::dispatch($this->applicationId, $data);
    }
}
