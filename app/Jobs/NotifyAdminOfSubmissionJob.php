<?php

namespace App\Jobs;

use App\Mail\SimpleEmail;
use App\Models\Setting;
use App\Services\ExcelGenerationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyAdminOfSubmissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $applicationId;
    public int $tries = 3;
    public int $timeout = 0;
    public int $backoff = 60;

    public function __construct(string $applicationId)
    {
        $this->applicationId = $applicationId;
    }

    public function handle(ExcelGenerationService $excelGenerationService): void
    {
        try {
            $data = $excelGenerationService->generateExcel($this->applicationId);

            $this->sendAdminEmail($data);

            Log::channel('info_log')->info("Admin notification email sent for Application ID: {$this->applicationId}");
        } catch (Exception $e) {
            Log::error("Error in NotifyAdminOfSubmissionJob for Application ID: {$this->applicationId} - " . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function sendAdminEmail(array $data): void
    {
        $reportEmail = Setting::where('key', 'report_email')->first()?->value;
        if (!$reportEmail) {
            Log::channel('info_log')->warning("Report email address missing for admin notification on application {$this->applicationId}.");
            return;
        }

        $subject = 'Lønnsskjemaet ferdig generert';
        $body = $this->generateEmailBodyForAdmin($data);
        Mail::to($reportEmail)->send(new SimpleEmail($subject, $body, null));
    }

    private function generateEmailBodyForAdmin(array $data): string
    {
        $body = 'Denne eposten ble generert på nettstedet ' . config('app.name') . '<br/><br/>';
        $body .= 'Et lønnsskjema for stillingen ' . $data['application']->job_title . ' på ' . $data['application']->personal_info['employer_and_place'] . ' er nå generert og klart for nedlasting. <br/><br/><a href="' . route('admin.employee-cv.index') . '">Du finner det på admin sidene i lønnsberegner webappen.</a> <br/><br/>';
        $body .= ' Du kan også <a href="' . route('open-application', $this->applicationId) . '">se eller endre skjemaet på web ved å trykke her</a>.';

        return $body;
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('NotifyAdminOfSubmissionJob failed permanently after retries.', [
            'job_id' => $this->job?->getJobId(),
            'application_id' => $this->applicationId,
            'message' => $exception->getMessage(),
            'class' => get_class($exception),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
