<?php

namespace App\Jobs;

use App\Mail\SimpleEmail;
use App\Services\ExcelGenerationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessUserSubmissionJob implements ShouldQueue
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

            $this->sendUserEmail($data);

            Log::channel('info_log')->info("User notification email sent for Application ID: {$this->applicationId}");
        } catch (Exception $e) {
            Log::error("Error in ProcessUserSubmissionJob for Application ID: {$this->applicationId} - " . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            $this->sendErrorNotification($e->getMessage());
            throw $e;
        }
    }

    private function sendUserEmail(array $data): void
    {
        $userEmail = $data['application']->personal_info['email'];
        $subject = 'Det beregnede lønnsskjema er klart for nedlasting';
        $body = $this->generateEmailBodyForUser($data);
        Mail::to($userEmail)->send(new SimpleEmail($subject, $body, null));
    }

    private function generateEmailBodyForUser(array $data): string
    {
        $downloadLink = route('download-form', $this->applicationId);
        $body = 'Denne eposten ble generert på nettstedet ' . config('app.name') . '<br/><br/>';
        $body .= 'Takk for din innsending. Ditt lønnsskjema er nå generert og klart for nedlasting.<br/><br/>';
        $body .= 'Din foreløpige plassering er lønnstrinn ' . $data['data']['salaryPlacement'] . '.<br/><br/>';
        $body .= '<strong><a href="' . $downloadLink . '">Klikk her for å laste ned ditt lønnsskjema</a></strong><br/><br/>';
        $body .= 'For å få tilgang til filen må du oppgi din fødselsdato og postnummeret du registrerte.<br/><br/>';
        $body .= '<strong>MERK:</strong> Dette er en maskinberegnet, foreløpig lønnsplassering og kan ha avvik. For endelig fastsettelse, send det utfylte skjemaet til HR.<br/><br/>';
        $body .= ' Du kan <a href="' . route('open-application-form', $this->applicationId) . '">se og endre ditt skjema ved å trykke her</a>.';
        $body .= ' Skjemaer slettes ett år etter at det er blitt åpnet.';

        return $body;
    }

    public function sendErrorNotification(string $message): void
    {
        // In a real scenario, you might want to fetch the user email differently if generation fails early
        // For now, we assume we can't get it and just log. A more robust solution could be needed.
        Log::error("Could not send error notification for user job for application {$this->applicationId} as email is unknown at this stage of failure.");
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('ProcessUserSubmissionJob failed permanently after retries.', [
            'job_id' => $this->job?->getJobId(),
            'application_id' => $this->applicationId,
            'message' => $exception->getMessage(),
            'class' => get_class($exception),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
