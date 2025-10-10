<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateExcelJob;
use App\Mail\SimpleEmail; // We'll create this import class later
use App\Models\EmployeeCV;
use App\Services\ExcelImportService;
use App\Services\SalaryEstimationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use Illuminate\Support\Facades\Log;


class EmployeeCVController extends Controller
{
    public function index()
    {

        return view('welcome');
    }

    public function signout()
    {
        request()->session()->invalidate();

        $this->flashMessage('Du er nå logget ut.');

        return redirect()->route('welcome');
    }

    public function showOpenApplicationForm($applicationId)
    {
        $application = EmployeeCV::find($applicationId);
        if (! $application) {
            abort(404, 'Skjemaet ble ikke funnet.');
        }

        return view('open-application-form', compact('application'));
    }

    public function openApplication(Request $request, EmployeeCV $application, SalaryEstimationService $salaryEstimationService)
    {
        $request->validate([
            'birth_date' => 'nullable|date',
            'postal_code' => 'nullable|string',
        ]); //using nullable instead of required for backward compatibility for lønnsskjemaer who do not have the postal address. This is ONLY for admin


        // Verify credentials
        $birthDateMatch = $application->birth_date === $request->birth_date;
        $postalCodeMatch = ($application->personal_info['postal_code'] ?? null) === $request->postal_code;

        if (! $birthDateMatch || ! $postalCodeMatch) {
            return back()->withErrors(['credentials' => 'Ugyldig fødselsdato eller postnummer.'])->withInput();
        }

        session(['applicationId' => $application->id]);

        $this->flashMessage('Lønnsskjemaet er nå åpnet og lastet inn.');

        // update missing attributes
        $updatedEducation = $salaryEstimationService->updateMissingDatasetItems($application->education);
        $updatedWorkExperience = $salaryEstimationService->updateMissingDatasetItems($application->work_experience);

        // Update the education field in the application model
        $application->education = $updatedEducation->toArray();
        $application->work_experience = $updatedWorkExperience->toArray();
        $application->save();

        return redirect()->route('enter-employment-information');
    }

    public function sendEmailLink(Request $request)
    {
        $validatedData = $request->validate([
            'email_address' => 'email|required',
        ], [
            'email_address.email' => 'E-postadressen må være en gyldig e-postadresse.',
            'email_address.required' => 'E-postadressefeltet er obligatorisk.',
        ]);

        $subject = 'Lenke til foreløpig lønnsberegning';
        $body = 'Denne lenken går til dine registrerte opplysninger <a href="' . route('open-application', session('applicationId')) . '">' . route('open-application', session('applicationId')) . '</a>';
        Mail::to($validatedData['email_address'])->send(new SimpleEmail($subject, $body, ''));

        // Remeber that an email has been sent
        $application = EmployeeCV::find(session('applicationId'));
        $application->email_sent = true;
        $application->save();

        $this->flashMessage('Lenke til dette skjemaet er påkrevet. Vennligst sjekk at du har fått e-posten.');

        return response('Lenke til dette skjemaet er nå sendt. Vennligst sjekk at du har fått e-posten.')->header('Content-Type', 'text/html');
    }

    public function enterEmploymentInformation(?EmployeeCV $application, SalaryEstimationService $salaryEstimationService)
    {
        if (request()->createNew) {
            session()->forget('applicationId');
        }

        $application = $salaryEstimationService->getOrCreateApplication($application);
        $application->job_title = $application->job_title ?? null;
        $application->work_start_date = $application->work_start_date ?? null;
        $application->birth_date = $application->birth_date ?? null;
        $application->save();

        $allPositions = (new EmployeeCV)->getPositionsLaddersGroups();
        ksort($allPositions);

        $groupedPositions = [
            'Menighet' => [],
            'FriBU' => [],
            'Hovedkontoret' => [],
        ];

        foreach ($allPositions as $position => $details) {
            $parts = explode(':', $position, 2);
            $category = $parts[0];
            if (isset($groupedPositions[$category])) {
                $groupedPositions[$category][$position] = $details;
            } elseif (in_array($category, ['Lederstilling Fellesarbeidet'])) {
                $groupedPositions['Hovedkontoret'][$position] = $details;
            }
        }

        $hasNull = false; // Initialize the flag to false

        return view('enter-employment-information', compact('application', 'groupedPositions'));
    }

    public function postEmploymentInformation(Request $request)
    {
        $request->validate([
            'job_title' => 'required|string',
            'birth_date' => 'required|date',
            'work_start_date' => 'required|date',
        ], [
            'job_title.required' => 'Type stilling er et obligatorisk felt.',
            'birth_date.required' => 'Fødselsdato er et obligatorisk felt.',
            'birth_date.date' => 'Fødselsdato må være en gyldig dato.',
            'work_start_date.required' => 'Start på stilling er et obligatorisk felt.',
            'work_start_date.date' => 'Start på stilling må være en gyldig dato.',
        ]);
        $application = EmployeeCV::find(session('applicationId'));
        $application->job_title = $request->job_title;
        $application->birth_date = $request->birth_date;
        $application->work_start_date = $request->work_start_date;
        $application->status = 'modified';
        $application->save();

        return redirect()->route('enter-education-information', compact('application'));
    }

    public function enterEducationInformation(EmployeeCV $application, SalaryEstimationService $salaryEstimationService)
    {
        if (! session('applicationId')) {
            $this->flashMessage('Din sesjon er utløpt og du må starte på nytt.', 'danger');

            return redirect()->route('welcome');
        }
        $application = $salaryEstimationService->getOrCreateApplication($application);

        $hasErrors = false; // Initialize the flag to false

        foreach ($application->education ?? [] as $item) {
            if (in_array(null, [
                @$item['topic_and_school'],
                @$item['start_date'],
                @$item['end_date'],
                @$item['study_points'],
                @$item['percentage'],
                @$item['relevance'],
            ], true)) {

                $hasErrors = true; // Set the flag if any null is found
                break; // Exit the loop early since we found a null value
            }
        }

        return view('enter-education-information', compact('application', 'hasErrors'));
    }

    public function postEducationInformation(Request $request)
    {

        $request->validate(
            [
                'topic_and_school' => 'string|required',
                'start_date' => 'date|required',
                'end_date' => 'date|required',
                'study_points' => [
                    'required', // Field is required
                    'regex:/^(bestått|[1-9][0-9]{0,2}|1000)$/',
                ],
                'highereducation' => 'string|sometimes|nullable|in:bachelor,master,cand.theol.',
                'relevance' => 'in:true,false|nullable',
            ],
            [
                'topic_and_school.required' => 'Vennligst fyll inn navnet på studiet og skolen.',
                'topic_and_school.string' => 'Navnet på studiet og skolen må være tekst.',
                'start_date.required' => 'Vennligst velg en startdato.',
                'start_date.date' => 'Startdato må være en gyldig dato.',
                'end_date.required' => 'Vennligst velg en sluttdato.',
                'end_date.date' => 'Sluttdato må være en gyldig dato.',
                'study_points.required' => 'Studiepoeng eller bestått mangler.',
                'study_points.regex' => 'Ugyldig antall studiepoeng. 0-1000 eller "bestått".',
                'highereducation.in' => 'Ugyldig type studie.',
                'relevance.in' => 'Ugyldig verdi for relevans.',
            ]
        );

        $application = EmployeeCV::find(session('applicationId'));

        // saving not allowed when generated
        if ($application->isReadOnly()) {
            return redirect()->route('enter-education-information', compact('application'));
        }

        $relevance = $request->relevance === 'true' ? 1 : 0;
        $education = $application->education ?? [];

        if (strtolower($request->study_points) === 'bestått') {
            $studyPercentage = '100';
        } else {

            $studyPercentage = SalaryEstimationService::calculateStudyPercentage($request->start_date, $request->end_date, intval($request->study_points));
        }

        $education[] = [
            'topic_and_school' => $request->topic_and_school,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'study_points' => $request->study_points,
            'percentage' => $studyPercentage,
            'highereducation' => $request->highereducation,
            'relevance' => $relevance,
            'id' => Str::uuid()->toString(),
        ];
        $application->education = $education;
        $application->status = 'modified';
        $application->save();

        return redirect()->route('enter-education-information', compact('application'));
    }

    public function updateSingleEducationInformation(Request $request)
    {

        $request->validate(
            [
                'edit' => 'numeric|required',
                'topic_and_school' => 'string|required',
                'start_date' => 'date|required',
                'end_date' => 'date|required',
                'study_points' => [
                    'required', // Field is required
                    'regex:/^(bestått|[1-9][0-9]{0,2}|1000)$/',
                ],
                'highereducation' => 'string|sometimes|nullable|in:bachelor,master,cand.theol.',
                'relevance' => 'in:true,false|nullable',
            ],
            [
                'edit.required' => 'ID mangler.', // Litt mer presis
                'edit.numeric' => 'ID må være et tall.', // Ny valideringsmelding
                'topic_and_school.required' => 'Vennligst fyll inn navnet på studiet og skolen.',
                'topic_and_school.string' => 'Navnet på studiet og skolen må være tekst.',
                'start_date.required' => 'Vennligst velg en startdato.',
                'start_date.date' => 'Startdato må være en gyldig dato.',
                'end_date.required' => 'Vennligst velg en sluttdato.',
                'end_date.date' => 'Sluttdato må være en gyldig dato.',
                'study_points.required' => 'Studiepoeng eller bestått mangler.',
                'study_points.regex' => 'Ugyldig antall studiepoeng. 1-1000 eller "bestått".',
                'highereducation.in' => 'Ugyldig type studie.',
                'relevance.in' => 'Ugyldig verdi for relevans.',
            ]
        );

        $application = EmployeeCV::find(session('applicationId'));
        // saving not allowed when generated
        if ($application->isReadOnly()) {
            return redirect()->route('enter-education-information', compact('application'));
        }

        $relevance = $request->relevance === 'true' ? 1 : 0;
        $educationData = $application->education;

        if (strtolower($request->study_points) === 'bestått') {
            $studyPercentage = '100';
        } else {
            $studyPercentage = SalaryEstimationService::calculateStudyPercentage($request->start_date, $request->end_date, intval($request->study_points));
        }

        $educationItem = $educationData[$request->edit];
        $educationItem = [
            'topic_and_school' => $request->topic_and_school,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'study_points' => $request->study_points,
            'percentage' => $studyPercentage,
            'highereducation' => $request->highereducation,
            'relevance' => $relevance,
            'id' => Str::uuid()->toString(),
        ];

        // Update the model and save
        $educationData[$request->edit] = $educationItem;
        $application->education = $educationData;
        $application->status = 'modified';
        $application->save();

        return redirect()->route('enter-education-information', compact('application'));
    }

    public function updateRelevanceOnEducationInformation(Request $request)
    {

        $request->validate(
            [
                'changeEdit' => 'numeric|required',
                'changeRelevance' => 'in:true,false|nullable',
            ],
            [
                'changeEdit.required' => 'ID mangler.', // Litt mer presis
                'changeEdit.numeric' => 'ID må være et tall.', // Ny valideringsmelding
                'changeRelevance.in' => 'Ugyldig verdi for relevans.',
            ]
        );

        $application = EmployeeCV::find(session('applicationId'));

        // saving not allowed when generated
        if ($application->isReadOnly()) {
            return redirect()->route('enter-education-information', compact('application'));
        }

        $relevance = $request->changeRelevance === 'true' ? 1 : 0;
        $educationData = $application->education;

        $educationItem = $educationData[$request->changeEdit];
        $educationItem = [
            'topic_and_school' => $educationItem['topic_and_school'],
            'start_date' => $educationItem['start_date'],
            'end_date' => $educationItem['end_date'],
            'study_points' => $educationItem['study_points'],
            'percentage' => $educationItem['percentage'],
            'highereducation' => @$educationItem['highereducation'],
            'relevance' => $relevance,
            'id' => $educationItem['id'],
        ];

        // Update the model and save
        $educationData[$request->changeEdit] = $educationItem;
        $application->education = $educationData;
        $application->status = 'modified';
        $application->save();

        return redirect()->route('enter-education-information', compact('application'));
    }

    public function enterExperienceInformation(EmployeeCV $application, SalaryEstimationService $salaryEstimationService)
    {
        if (! session('applicationId')) {
            $this->flashMessage('Din sesjon er utløpt og du må starte på nytt.', 'danger');

            return redirect()->route('welcome');
        }
        $application = $salaryEstimationService->getOrCreateApplication($application);

        $hasErrors = false; // Initialize the flag to false
        foreach ($application->work_experience ?? [] as $item) {
            if (in_array(null, [
                @$item['title_workplace'],
                $item['percentage'] == '' ? null : $item['percentage'],
                $item['start_date'] == '' ? null : $item['start_date'],
                $item['end_date'] == '' ? null : $item['end_date'],
                @$item['relevance'],
            ], true)) {
                $hasErrors = true; // Set the flag if any null is found
                break; // Exit the loop early since we found a null value
            }
        }

        return view('enter-experience-information', compact('application', 'hasErrors'));
    }

    public function postExperienceInformation(Request $request)
    {

        $validatedData = $request->validate([
            'title_workplace' => 'string|required',
            'percentage' => 'required|numeric|between:0,100',
            'start_date' => 'date|required',
            'end_date' => 'date|required',
            'workplace_type' => 'string|sometimes|nullable|in:normal,freechurch,other_christian',
            'relevance' => 'in:true,false|nullable',
        ], [
            'title_workplace.required' => 'Vennligst fyll inn tittel og arbeidssted.',
            'title_workplace.string' => 'Tittel og arbeidssted må være tekst.',
            'percentage.required' => 'Vennligst fyll inn arbeidsprosent.',
            'percentage.numeric' => 'Arbeidsprosent må være et tall.',
            'percentage.between' => 'Arbeidsprosent må være mellom 0 og 100.',
            'start_date.required' => 'Vennligst velg en startdato.',
            'start_date.date' => 'Startdato må være en gyldig dato.',
            'end_date.required' => 'Vennligst velg en sluttdato.',
            'end_date.date' => 'Sluttdato må være en gyldig dato.',
            'workplace_type.in' => 'Ugyldig type arbeidssted. Velg et av de tilgjengelige alternativene.',
            'relevance.in' => 'Ugyldig verdi for relevans.',
        ]);
        $application = EmployeeCV::find(session('applicationId'));

        // saving not allowed when generated
        if ($application->isReadOnly()) {
            return redirect()->route('enter-experience-information', compact('application'));
        }

        $relevance = $validatedData['workplace_type'] === 'freechurch' ? 1 : $validatedData['relevance'] ?? 0;
        $work_experience = $application->work_experience ?? [];
        $work_experience[] = [
            'title_workplace' => $validatedData['title_workplace'],
            'percentage' => $validatedData['percentage'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'workplace_type' => $validatedData['workplace_type'],
            'relevance' => $relevance,
            'id' => Str::uuid()->toString(),
        ];
        $application->work_experience = $work_experience;
        $application->status = 'modified';
        $application->save();

        return redirect()->route('enter-experience-information', compact('application'));
    }

    public function updateSingleExperienceInformation(Request $request)
    {
        $validatedData = $request->validate([
            'edit' => 'numeric|required',
            'title_workplace' => 'string|required',
            'percentage' => 'required|numeric|between:0,100',
            'start_date' => 'date|required',
            'end_date' => 'date|required',
            'workplace_type' => 'string|sometimes|nullable|in:normal,freechurch,other_christian',
            'relevance' => 'in:true,false|nullable',
        ], [
            'edit.required' => 'ID mangler.',
            'edit.numeric' => 'ID må være et tall.',
            'title_workplace.required' => 'Vennligst fyll inn tittel og arbeidssted.',
            'title_workplace.string' => 'Tittel og arbeidssted må være tekst.',
            'percentage.required' => 'Vennligst fyll inn arbeidsprosent.',
            'percentage.numeric' => 'Arbeidsprosent må være et tall.',
            'percentage.between' => 'Arbeidsprosent må være mellom 0 og 100.',
            'start_date.required' => 'Vennligst velg en startdato.',
            'start_date.date' => 'Startdato må være en gyldig dato.',
            'end_date.required' => 'Vennligst velg en sluttdato.',
            'end_date.date' => 'Sluttdato må være en gyldig dato.',
            'workplace_type.in' => 'Ugyldig type arbeidssted. Velg et av de tilgjengelige alternativene.',
            'relevance.in' => 'Ugyldig verdi for relevans.',
        ]);

        $application = EmployeeCV::find(session('applicationId'));

        // saving not allowed when generated
        if ($application->isReadOnly()) {
            return redirect()->route('enter-experience-information', compact('application'));
        }

        $relevance = $validatedData['workplace_type'] === 'freechurch' ? 1 : $validatedData['relevance'] ?? 0;
        $workExperienceData = $application->work_experience ?? [];
        $workExperienceItem = $workExperienceData[$request->edit];

        $workExperienceItem = [
            'title_workplace' => $validatedData['title_workplace'],
            'percentage' => $validatedData['percentage'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'workplace_type' => $validatedData['workplace_type'],
            'relevance' => $relevance,
            'id' => Str::uuid()->toString(),
        ];

        $workExperienceData[$request->edit] = $workExperienceItem;
        $application->work_experience = $workExperienceData;
        $application->status = 'modified';
        $application->save();

        return redirect()->route('enter-experience-information', compact('application'));
    }

    public function updateRelevanceOnExperienceInformation(Request $request)
    {

        $validatedData = $request->validate(
            [
                'changeEdit' => 'numeric|required',
                'changeEelevance' => 'in:true,false|nullable',
            ],
            [
                'changeEdit.required' => 'ID mangler.', // Litt mer presis
                'changeEdit.numeric' => 'ID må være et tall.', // Ny valideringsmelding
                'changeEelevance.in' => 'Ugyldig verdi for relevans.',
            ]
        );

        $application = EmployeeCV::find(session('applicationId'));

        // saving not allowed when generated
        if ($application->isReadOnly()) {
            return redirect()->route('enter-experience-information', compact('application'));
        }

        $relevance = $request->changeRelevance === 'true' ? 1 : 0;
        $educationData = $application->education;

        $workExperienceData = $application->work_experience ?? [];
        $workExperienceItem = $workExperienceData[$request->changeEdit];

        $workExperienceItem = [
            'title_workplace' => $workExperienceItem['title_workplace'],
            'percentage' => $workExperienceItem['percentage'],
            'start_date' => $workExperienceItem['start_date'],
            'end_date' => $workExperienceItem['end_date'],
            'workplace_type' => @$workExperienceItem['workplace_type'],
            'relevance' => $relevance,
            'id' => $workExperienceItem['id'],
        ];

        // Update the model and save
        $workExperienceData[$request->changeEdit] = $workExperienceItem;
        $application->work_experience = $workExperienceData;
        $application->status = 'modified';
        $application->save();

        return redirect()->route('enter-experience-information', compact('application'));
    }

    public function enterCoursesAndActivityInformation(EmployeeCV $application)
    {
        return view('enter-courses-and-activities', compact('application'));
    }

    public function previewAndEstimatedSalary(EmployeeCV $application, SalaryEstimationService $salaryEstimationService)
    {
        if (! session('applicationId')) {
            $this->flashMessage('Din sesjon er utløpt og du må starte på nytt.', 'danger');

            return redirect()->route('welcome');
        }

        $application = $salaryEstimationService->getOrCreateApplication($application);

        $adjustedDataset = $salaryEstimationService->adjustEducationAndWork($application);

        $timelineData = $salaryEstimationService->createTimelineData($adjustedDataset->education, $adjustedDataset->work_experience);
        $timelineData_adjusted = $salaryEstimationService->createTimelineData($adjustedDataset->education_adjusted, $adjustedDataset->work_experience_adjusted);
        $workStartDate = Carbon::parse($application->work_start_date);
        $calculatedTotalWorkExperienceMonths = SalaryEstimationService::calculateTotalWorkExperienceMonths($adjustedDataset->work_experience_adjusted);

        $salaryCategory = (new EmployeeCV)->getPositionsLaddersGroups()[$application->job_title];

        // Calculating the ladder position based on the employee’s total work experience in years, rounded down to the nearest integer
        $ladderPosition = SalaryEstimationService::ladderPosition($workStartDate, $calculatedTotalWorkExperienceMonths);

        return view('preview-and-estimated-salary', [
            'application' => $application,
            'adjustedDataset' => $adjustedDataset,
            'timeline' => $timelineData['timeline'],
            'tableData' => $timelineData['tableData'],
            'timeline_adjusted' => $timelineData_adjusted['timeline'],
            'tableData_adjusted' => $timelineData_adjusted['tableData'],
            'calculatedTotalWorkExperienceMonths' => SalaryEstimationService::calculateTotalWorkExperienceMonths($adjustedDataset->work_experience_adjusted),
            'ansiennitetFromDate' => $workStartDate->subDays($calculatedTotalWorkExperienceMonths * 365.25 / 12)->format('Y-m-d'),
            'ladder' => $salaryCategory['ladder'],
            'group' => $salaryCategory['group'] !== ('B' || 'D') ? $salaryCategory['group'] : '',
            'salaryPlacement' => EmployeeCV::getSalary($salaryCategory['ladder'], $salaryCategory['group'], $ladderPosition),
        ]);
    }

    public function loadExcel(Request $request, ExcelImportService $excelImportService)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ], [
            'excel_file.required' => 'Du må velge en fil.',
            'excel_file.file' => 'Den opplastede filen må være en fil.',
            'excel_file.mimes' => 'Filen må være en Excel-fil (xlsx, xls) eller en CSV-fil (csv).',
        ]);

        try {
            $application = $excelImportService->processExcelFile($request->file('excel_file'));
            session(['applicationId' => $application->id]);

            $this->flashMessage('Excel dokumentet er lastet inn og du kan arbeide videre med den her.');

            return redirect()->route('enter-employment-information');
        } catch (PhpSpreadsheetException $e) {
            $this->flashMessage('En ukjent feil oppstod. Bruk alltid siste utgave av lønnsskjemaet.', 'danger');

            return redirect()->back();
        } catch (\InvalidArgumentException $e) {
            $this->flashMessage($e->getMessage(), 'danger');

            return redirect()->back();
        }
    }

    public function destroyEducationInformation(Request $request)
    {
        $itemId = $request->input('id');
        $application = EmployeeCV::find(session('applicationId'));

        // destroying not allowed when generated
        if ($application->isReadOnly()) {
            return redirect()->back();
        }

        if ($application) {
            $educationData = $application->education;

            // Remove the item with the matching ID (key)
            unset($educationData[$itemId]);

            // Update the model and save
            $application->education = $educationData;
            $application->status = 'modified';
            $application->save();

            return redirect()->back();
        }

        return redirect()->back();
    }

    public function destroyWorkExperienceInformation(Request $request)
    {
        $itemId = $request->input('id');
        $application = EmployeeCV::find(session('applicationId'));

        // destroying not allowed when generated
        if ($application->isReadOnly()) {
            return redirect()->back();
        }

        if ($application) {
            $workExperienceData = $application->work_experience;

            // Remove the item with the matching ID (key)
            unset($workExperienceData[$itemId]);

            // Update the model and save
            $application->work_experience = $workExperienceData;
            $application->status = 'modified';
            $application->save();

            return redirect()->back();
        }

        return redirect()->back();
    }

    // public function exportAsXls()
    // {
    //     if (! session('applicationId')) {
    //         $this->flashMessage('Din sesjon er utløpt og du må starte på nytt.', 'danger');

    //         return redirect()->route('welcome');
    //     }

    //     request()->validate([
    //         'email' => 'email|required',
    //     ], [
    //         'email.required' => 'E-postadressefeltet er obligatorisk.',
    //         'email.email' => 'E-postadressen må være en gyldig e-postadresse.',
    //     ]);

    //     // Get the user's email from the request
    //     $email = request()->email;

    //     // Remeber that an email has been sent
    //     $application = EmployeeCV::find(session('applicationId'));
    //     $application->email_sent = true;
    //     $application->save();

    //     // Dispatch the job
    //     $processedByAdmin = auth()->check();

    //     ExportExcelJob::dispatch(session('applicationId'), $email, $processedByAdmin);
    //     $this->flashMessage('En epost med et excel dokument blir sendt i løpet av et par minutter.');

    //     return redirect()->back();
    // }

    public function submitForProcessing(Request $request)
    {
        if (! session('applicationId')) {
            $this->flashMessage('Din sesjon er utløpt og du må starte på nytt.', 'danger');

            return redirect()->route('welcome');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:255',
            'postal_place' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'employer_and_place' => 'required|string|max:255',
            'position_size' => 'required|integer|min:0|max:100',
            'manager_name' => 'required|string|max:255',
            'bank_account' => 'nullable|string|max:255', // Added bank_account validation
            'manager_mobile' => 'required|string|max:255',
            'manager_email' => 'required|email|max:255',
            'congregation_name' => 'required|string|max:255',
            'congregation_mobile' => 'required|string|max:255',
            'congregation_email' => 'required|email|max:255',
        ]);

        $application = EmployeeCV::find(session('applicationId'));

        // submitting not allowed when generated
        if ($application->isReadOnly()) {
            return redirect()->route('welcome');
        }

        $application->personal_info = $validatedData;
        $application->status = 'submitted';
        $application->save();

        // Dispatch a single job to generate excel and then notify
        GenerateExcelJob::dispatch(session('applicationId'), !auth()->check());

        // Clear the session and redirect the user to a thank you page
        $request->session()->forget('applicationId');

        $this->flashMessage(
            'Takk! Ditt skjema er mottatt og vil bli behandlet av Frikirkens hovedkontor.',
            'success'
        );

        return redirect()->route('welcome');
    }

    public function showDownloadForm($applicationId)
    {
        $application = EmployeeCV::find($applicationId);
        if (! $application) {
            abort(404, 'Filen ble ikke funnet.');
        }

        // Check if the file has been generated and the status is correct
        if ($application->status !== 'generated' || ! $application->generated_file_path) {
            abort(404, 'Filen er ikke tilgjengelig for nedlasting.');
        }

        return view('download-form', compact('application'));
    }

    public function downloadFile(Request $request, EmployeeCV $application)
    {
        $request->validate([
            'birth_date' => 'required|date',
            'postal_code' => 'required|string',
        ]);

        // Verify credentials
        $birthDateMatch = $application->birth_date === $request->birth_date;
        $postalCodeMatch = ($application->personal_info['postal_code'] ?? null) === $request->postal_code;

        if (! $birthDateMatch || ! $postalCodeMatch) {
            return back()->withErrors(['credentials' => 'Ugyldig fødselsdato eller postnummer.'])->withInput();
        }
        // Check if file exists
        if ($application->status !== 'generated' || ! $application->generated_file_path || ! \Storage::disk('public')->exists($application->generated_file_path)) {
            abort(404, 'Filen ble ikke funnet.');
        }

        // Log the download and then offer the file
        Log::channel('info_log')->info("File downloaded for Application ID: {$application->id}");

        // Invalidate the link after download by updating status
        $application->status = 'downloaded';
        $application->save();

        return \Storage::disk('public')->download($application->generated_file_path);
    }


    private function isValidExcelDate($dateString)
    {
        return is_numeric($dateString);
    }

    private function flashMessage($message, $type = 'success')
    {
        session()->flash('message', $message);
        session()->flash('alert-class', 'alert-' . $type); // Adjust your alert classes accordingly
    }
}
