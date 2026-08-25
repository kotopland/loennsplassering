<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SimpleEmail;
use App\Models\EmployeeCV;
use App\Services\SalaryEstimationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmployeeCVController extends Controller
{
    public function index(SalaryEstimationService $salaryEstimationService)
    {
        $employeeCV = EmployeeCV::select([
            'id', 'job_title', 'work_start_date', 'birth_date', 'email_sent',
            'last_viewed', 'status', 'processing_status', 'generated_file_path',
            'personal_info', 'education', 'work_experience', 'updated_at'
        ])
            ->whereNotNull('work_start_date')
            ->whereNotNull('job_title')
            ->whereNotNull('birth_date')
            ->get();

        foreach ($employeeCV as $employee) {
            $employee->employer_email_data = $this->calculateEmployerEmailData($employee, $salaryEstimationService);
        }

        $processingStatuses = EmployeeCV::getProcessingStatuses();

        return view('admin.employee-cv.index', compact('employeeCV', 'processingStatuses'));
    }

    public function destroy(EmployeeCV $employeeCv)
    {
        $employeeCv->delete();

        return redirect()->route('admin.employee-cv.index')->with('success', 'Lønnsskjema slettet!');
    }

    public function updateProcessingStatus(Request $request, EmployeeCV $employeeCv)
    {
        $validated = $request->validate([
            'processing_status' => 'required|string|in:innsendt,behandles,godkjent',
        ]);

        $employeeCv->processing_status = $validated['processing_status'];

        if (in_array($validated['processing_status'], ['behandles', 'godkjent'])) {
            $employeeCv->status = 'generated';
        }

        $employeeCv->save();

        return redirect()->route('admin.employee-cv.index')->with('success', 'Behandlingsstatus er oppdatert!');
    }

    public function toggleStatus(EmployeeCV $employeeCv)
    {
        // Toggle status between 'generated' and null
        $employeeCv->status = $employeeCv->status === 'generated' ? null : 'generated';
        $employeeCv->save();

        return redirect()->route('admin.employee-cv.index')->with('success', 'Status for lønnsskjema er endret!');
    }

    public function downloadFile(Request $request, EmployeeCV $application)
    {
        // Log the download and then offer the file
        Log::channel('info_log')->info("Admin: File downloaded for Application ID: {$application->id}");

        return Storage::disk('public')->download($application->generated_file_path);
    }

    public function sendEmployerEmail(Request $request, EmployeeCV $employeeCv)
    {
        $validated = $request->validate([
            'recipient_email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'email_body' => 'required|string',
            'attach_file' => 'nullable|boolean',
        ]);

        $filePath = null;
        if ($request->boolean('attach_file') && $employeeCv->generated_file_path) {
            if (Storage::disk('public')->exists($employeeCv->generated_file_path)) {
                $filePath = $employeeCv->generated_file_path;
            }
        }

        $formattedBody = nl2br(e($validated['email_body']));
        $formattedBody = str_replace(
            ['https://frikirken.no/arbeid'],
            ['<a href="https://frikirken.no/arbeid" target="_blank">https://frikirken.no/arbeid</a>'],
            $formattedBody
        );

        Mail::to($validated['recipient_email'])->send(
            new SimpleEmail($validated['subject'], $formattedBody, $filePath)
        );

        Log::channel('info_log')->info("Admin sent employer placement email for Application ID: {$employeeCv->id} to {$validated['recipient_email']}");

        return redirect()->route('admin.employee-cv.index')->with('success', "E-post med lønnsplassering er sendt til arbeidsgiver ({$validated['recipient_email']})!");
    }

    public function calculateEmployerEmailData(EmployeeCV $application, SalaryEstimationService $salaryEstimationService): array
    {
        try {
            $adjustedDataset = $salaryEstimationService->adjustEducationAndWork($application);
            $workStartDate = Carbon::parse($application->work_start_date ?? now());
            $calculatedTotalWorkExperienceMonths = SalaryEstimationService::calculateTotalWorkExperienceMonths($adjustedDataset->work_experience_adjusted ?? []);

            $positionsMap = (new EmployeeCV)->getPositionsLaddersGroups();
            $salaryCategory = $positionsMap[$application->job_title] ?? ['ladder' => 'B', 'group' => 1];

            $ladderPosition = SalaryEstimationService::ladderPosition($workStartDate, $calculatedTotalWorkExperienceMonths);
            $baseSalary = EmployeeCV::getSalary($salaryCategory['ladder'], $salaryCategory['group'], $ladderPosition) ?? 0;
            $competencePoints = $adjustedDataset->competence_points ?? 0;
            $totalPlacement = $baseSalary + $competencePoints;

            $ansiennitetFromDate = SalaryEstimationService::subMonthsWithDecimals($workStartDate, $calculatedTotalWorkExperienceMonths);

            $nextPromotionDate = Carbon::create($workStartDate->year, $ansiennitetFromDate->month, $ansiennitetFromDate->day);
            if ($nextPromotionDate->lessThanOrEqualTo($workStartDate)) {
                $nextPromotionDate->addYear();
            }

            $ladderString = $salaryCategory['ladder'] . (!in_array($salaryCategory['ladder'], ['B', 'D']) && !empty($salaryCategory['group']) ? ' ' . $salaryCategory['group'] : '');
            $ansiennitetFromFormatted = $ansiennitetFromDate->format('d.m.Y');
            $nextPromotionFormatted = $nextPromotionDate->format('d.m.Y');
        } catch (\Exception $e) {
            $ladderString = '-';
            $competencePoints = 0;
            $totalPlacement = 0;
            $ansiennitetFromFormatted = '-';
            $nextPromotionFormatted = '-';
        }

        $recipientEmail = $application->personal_info['manager_email']
            ?? $application->personal_info['congregation_email']
            ?? $application->personal_info['email']
            ?? '';

        $employeeName = $application->personal_info['name'] ?? 'Kandidat';
        $employerName = $application->personal_info['employer_and_place'] ?? ($application->personal_info['congregation_name'] ?? '');

        $subject = "Lønnsplassering for {$employeeName} - {$application->job_title}";

        $bodyText = "Stige: {$ladderString}\n"
            . "Kompetansetillegg: {$competencePoints}\n"
            . "Ansvarstillegg*: 0\n"
            . "Lønnsplassering inkl tillegg: {$totalPlacement}\n"
            . "Ansiennitet fra: {$ansiennitetFromFormatted}\n"
            . "Neste ansiennitetsopprykk: {$nextPromotionFormatted}\n\n"
            . "Vi minner om at arbeidsgiver er ansvarlig for å:\n"
            . "- sjekke at informasjonen er riktig i lønnsskjema og i mail (stemmer alt overens, er alle datoer osv riktig oppgitt)\n"
            . "- sjekke at attester og vitnemål stemmer overens med det som er oppgitt (er studier bestått, og start og sluttdatoer samt omfang (studiepoeng/stillingsprosent) av ansiennitetsopplysninger og utdanning)\n"
            . "- gjennomgå lønnsplasseringen med ansatt slik at evt. feil avdekkes med en gang (lønnsplasseringsskjema kan videresendes den ansatte).\n"
            . "- Legge inn lønnsinformasjonen i SDWorks\n"
            . "- følge med på lønnsavtalen og at arbeidstaker får de opprykk og justeringer som gjøres på bakgrunn av ansiennitet eller endringer i avtalen. (SD works skal gjøre dette hvis det er lagt riktig inn)\n"
            . "- oppbevare lønnsplassering og annen dokumentasjon om den ansatte i sitt personalarkiv.\n"
            . "- sende inn skjema ved ansettelse, endringer og opphør av stilling (for registrering av pensjon og forsikringsordningene for ansatte)\n\n"
            . "Lønnsplasseringen er kun gjort på bakgrunn av hva som er oppgitt i skjemaet (med forbehold om feil). Variasjoner i stillingen, utdanningen eller arbeidserfaringen kan gjøre at det vurderes annerledes. Arbeidsgiver har mulighet å innstille hvordan dette vurderes, der det er en skjønnsmessig vurdering vil arbeidsgivers innstilling bli tatt hensyn til. Det er ikke lokale lønnsforhandlinger i frikirken, slik at plasseringen skal ikke forhandles med arbeidstaker. Gjennomgang er for å avdekke evt feil og mangler, der hvor det er spørsmål om vurderingen, kan dette tas gjennom arbeidsgiver til undertegnede. Dette kan føre til ny plassering på bakgrunn av ny informasjon. Dersom arbeidsgiver eller arbeidstaker deretter er uenig i vedtaket kan en klage til Lønnsutvalget, som fatter endelig vedtak. Se lønnsavtalen, lønnstabell og annet materiell for arbeidsgivere og arbeidstakere her: https://frikirken.no/arbeid\n\n"
            . "* Ansvarstillegg er innført fra mai 2019. men er normalt 0.";

        return [
            'ladder' => $ladderString,
            'competence_points' => $competencePoints,
            'salary_placement' => $totalPlacement,
            'ansiennitet_from' => $ansiennitetFromFormatted,
            'next_promotion_date' => $nextPromotionFormatted,
            'recipient_email' => $recipientEmail,
            'employee_name' => $employeeName,
            'employer_name' => $employerName,
            'subject' => $subject,
            'body_text' => $bodyText,
        ];
    }
}
