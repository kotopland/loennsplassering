<?php

namespace App\Http\Controllers;

use App\Jobs\ExportExcelJob;
use App\Mail\SimpleEmail; // We'll create this import class later
use App\Models\EmployeeCV;
use App\Services\SalaryEstimationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class EmployeeCVController extends Controller
{
    public function index()
    {

        return view('welcome');
    }

    public function openApplication(EmployeeCV $application)
    {
        session(['applicationId' => $application->id]);
        session()->flash('message', 'Dine lagrede opplysninger er lastet inn.');
        session()->flash('alert-class', 'alert-success');

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
        $body = 'Denne lenken går til dine registrerte opplysninger <a href="'.route('open-application', session('applicationId')).'">'.route('open-application', session('applicationId')).'</a>';
        Mail::to($validatedData['email_address'])->send(new SimpleEmail($subject, $body, ''));

        // Remeber that an email has been sent
        $application = EmployeeCV::find(session('applicationId'));
        $application->email_sent = true;
        $application->save();

        session()->flash('message', 'Lenke til dette skjemaet er nå sendt. Vennligst sjekk at du har fått e-posten.');
        session()->flash('alert-class', 'alert-success');

        return response('Lenke til dette skjemaet er nå sendt. Vennligst sjekk at du har fått e-posten.')->header('Content-Type', 'text/html');

    }

    public function enterEmploymentInformation(?EmployeeCV $application, SalaryEstimationService $salaryEstimationService)
    {
        if (request()->createNew) {
            session()->forget('applicationId');
        }

        $application = $salaryEstimationService->checkForSavedApplication($application);
        $application->job_title = $application->job_title ?? 'Menighet: Menighetsarbeider';
        $application->work_start_date = $application->work_start_date ?? '2024-11-02';
        $application->birth_date = $application->birth_date ?? '1990-10-02';
        $application->save();

        $positionsLaddersGroups = EmployeeCV::positionsLaddersGroups;
        ksort($positionsLaddersGroups);

        $hasNull = false; // Initialize the flag to false

        return view('enter-employment-information', compact('application', 'positionsLaddersGroups'));
    }

    public function postEmploymentInformation(Request $request)
    {
        $request->validate([
            'job_title' => 'required',
            'birth_date' => 'required|date',
        ], [
            'job_title.required' => 'Stillingstittelfeltet er obligatorisk.',
            'birth_date.required' => 'Fødselsdato er obligatorisk.',
            'birth_date.date' => 'Fødselsdato må være en gyldig dato.',
        ]);
        $application = EmployeeCV::find(session('applicationId'));
        $application->job_title = $request->job_title;
        $application->birth_date = $request->birth_date;
        $application->save();

        return redirect()->route('enter-education-information', compact('application'));
    }

    public function enterEducationInformation(EmployeeCV $application, SalaryEstimationService $salaryEstimationService)
    {
        if (! session('applicationId')) {
            session()->flash('message', 'Din sesjon er utløpt og du må starte på nytt.');
            session()->flash('alert-class', 'alert-danger');

            return redirect()->route('welcome');
        }
        $application = $salaryEstimationService->checkForSavedApplication($application);

        $hasErrors = false; // Initialize the flag to false

        foreach ($application->education ?? [] as $item) {
            if (in_array(null, [
                @$item['topic_and_school'],
                @$item['start_date'],
                @$item['end_date'],
                @$item['study_points'],
                @$item['study_percentage'],
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
                'study_points' => 'string|in:bestått,5,10,20,30,60,120,180,240,300,0|required',
                'highereducation' => 'string|sometimes|nullable|in:bachelor,master',
                'relevance' => 'in:true,false|nullable',
            ],
            [
                'topic_and_school.required' => 'Vennligst fyll inn navnet på studiet og skolen.',
                'topic_and_school.string' => 'Navnet på studiet og skolen må være tekst.',
                'start_date.required' => 'Vennligst velg en startdato.',
                'start_date.date' => 'Startdato må være en gyldig dato.',
                'end_date.required' => 'Vennligst velg en sluttdato.',
                'end_date.date' => 'Sluttdato må være en gyldig dato.',
                'study_points.required' => 'Vennligst velg antall studiepoeng.',
                'study_points.in' => 'Ugyldig antall studiepoeng. Velg et av de tilgjengelige alternativene.',
                'highereducation.in' => 'Ugyldig type studie. Velg "bachelor" eller "master".',
                'relevance.in' => 'Ugyldig verdi for relevans.',
            ]
        );

        $application = EmployeeCV::find(session('applicationId'));
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
            'study_percentage' => $studyPercentage,
            'highereducation' => $request->highereducation,
            'relevance' => $relevance,
        ];
        $application->education = $education;
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
                'study_points' => 'string|in:bestått,5,10,20,30,60,120,180,240,300,0|required',
                'highereducation' => 'string|sometimes|nullable|in:bachelor,master',
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
                'study_points.required' => 'Vennligst velg antall studiepoeng.',
                'study_points.in' => 'Ugyldig antall studiepoeng. Velg et av de tilgjengelige alternativene.',
                'highereducation.in' => 'Ugyldig type studie. Velg "bachelor" eller "master".',
                'relevance.in' => 'Ugyldig verdi for relevans.',
            ]
        );

        $application = EmployeeCV::find(session('applicationId'));
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
            'study_percentage' => $studyPercentage,
            'highereducation' => $request->highereducation,
            'relevance' => $relevance,
        ];

        // Update the model and save
        $educationData[$request->edit] = $educationItem;
        $application->education = $educationData;
        $application->save();

        return redirect()->route('enter-education-information', compact('application'));
    }

    public function enterExperienceInformation(EmployeeCV $application, SalaryEstimationService $salaryEstimationService)
    {
        if (! session('applicationId')) {
            session()->flash('message', 'Din sesjon er utløpt og du må starte på nytt.');
            session()->flash('alert-class', 'alert-danger');

            return redirect()->route('welcome');
        }
        $application = $salaryEstimationService->checkForSavedApplication($application);

        $hasErrors = false; // Initialize the flag to false

        foreach ($application->work_experience ?? [] as $item) {
            if (in_array(null, [
                @$item['title_workplace'],
                @$item['work_percentage'],
                @$item['start_date'],
                @$item['end_date'],
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
            'work_percentage' => 'required|numeric|between:0,100',
            'start_date' => 'date|required',
            'end_date' => 'date|required',
            'workplace_type' => 'string|sometimes|nullable|in:normal,freechurch,other_christian',
            'relevance' => 'in:true,false|nullable',
        ], [
            'title_workplace.required' => 'Vennligst fyll inn tittel og arbeidssted.',
            'title_workplace.string' => 'Tittel og arbeidssted må være tekst.',
            'work_percentage.required' => 'Vennligst fyll inn arbeidsprosent.',
            'work_percentage.numeric' => 'Arbeidsprosent må være et tall.',
            'work_percentage.between' => 'Arbeidsprosent må være mellom 0 og 100.',
            'start_date.required' => 'Vennligst velg en startdato.',
            'start_date.date' => 'Startdato må være en gyldig dato.',
            'end_date.required' => 'Vennligst velg en sluttdato.',
            'end_date.date' => 'Sluttdato må være en gyldig dato.',
            'workplace_type.in' => 'Ugyldig type arbeidssted. Velg et av de tilgjengelige alternativene.',
            'relevance.in' => 'Ugyldig verdi for relevans.',
        ]);
        $application = EmployeeCV::find(session('applicationId'));
        $relevance = $validatedData['relevance'] ?? 0;
        $work_experience = $application->work_experience ?? [];
        $work_experience[] = [
            'title_workplace' => $validatedData['title_workplace'],
            'work_percentage' => $validatedData['work_percentage'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'workplace_type' => $validatedData['workplace_type'],
            'relevance' => $relevance,
        ];
        $application->work_experience = $work_experience;
        $application->save();

        return redirect()->route('enter-experience-information', compact('application'));
    }

    public function updateSingleExperienceInformation(Request $request)
    {
        $validatedData = $request->validate([
            'edit' => 'numeric|required',
            'title_workplace' => 'string|required',
            'work_percentage' => 'required|numeric|between:0,100',
            'start_date' => 'date|required',
            'end_date' => 'date|required',
            'workplace_type' => 'string|sometimes|nullable|in:normal,freechurch,other_christian',
            'relevance' => 'in:true,false|nullable',
        ], [
            'edit.required' => 'ID mangler.',
            'edit.numeric' => 'ID må være et tall.',
            'title_workplace.required' => 'Vennligst fyll inn tittel og arbeidssted.',
            'title_workplace.string' => 'Tittel og arbeidssted må være tekst.',
            'work_percentage.required' => 'Vennligst fyll inn arbeidsprosent.',
            'work_percentage.numeric' => 'Arbeidsprosent må være et tall.',
            'work_percentage.between' => 'Arbeidsprosent må være mellom 0 og 100.',
            'start_date.required' => 'Vennligst velg en startdato.',
            'start_date.date' => 'Startdato må være en gyldig dato.',
            'end_date.required' => 'Vennligst velg en sluttdato.',
            'end_date.date' => 'Sluttdato må være en gyldig dato.',
            'workplace_type.in' => 'Ugyldig type arbeidssted. Velg et av de tilgjengelige alternativene.',
            'relevance.in' => 'Ugyldig verdi for relevans.',
        ]);

        $application = EmployeeCV::find(session('applicationId'));
        $relevance = $validatedData['relevance'] ?? 0;
        $workExperienceData = $application->work_experience ?? [];
        $workExperienceItem = $workExperienceData[$request->edit];

        $workExperienceItem = [
            'title_workplace' => $validatedData['title_workplace'],
            'work_percentage' => $validatedData['work_percentage'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'workplace_type' => $validatedData['workplace_type'],
            'relevance' => $relevance,
        ];

        $workExperienceData[$request->edit] = $workExperienceItem;
        $application->work_experience = $workExperienceData;
        $application->save();

        return redirect()->route('enter-experience-information', compact('application'));
    }

    public function previewAndEstimatedSalary(EmployeeCV $application, SalaryEstimationService $salaryEstimationService)
    {
        if (! session('applicationId')) {
            session()->flash('message', 'Din sesjon er utløpt og du må starte på nytt.');
            session()->flash('alert-class', 'alert-danger');

            return redirect()->route('welcome');
        }

        $application = $salaryEstimationService->checkForSavedApplication($application);

        $adjustedDataset = $salaryEstimationService->adjustEducationAndWork($application);
        // dd($adjustedDataset);
        $timelineData = $salaryEstimationService->createTimelineData($adjustedDataset->education, $adjustedDataset->work_experience);
        $timelineData_adjusted = $salaryEstimationService->createTimelineData($adjustedDataset->education_adjusted, $adjustedDataset->work_experience_adjusted);
        $workStartDate = Carbon::parse($application->work_start_date);
        $calculatedTotalWorkExperienceMonths = SalaryEstimationService::calculateTotalWorkExperienceMonths($adjustedDataset->work_experience_adjusted);

        $salaryCategory = EmployeeCV::positionsLaddersGroups[$application->job_title];
        // Calculating the ladder position based on the employee’s total work experience in years, rounded down to the nearest integer
        $ladderPosition = intval(SalaryEstimationService::getYearsDifferenceWithDecimals(
            SalaryEstimationService::addMonthsWithDecimals(Carbon::parse($application->work_start_date), $calculatedTotalWorkExperienceMonths),
            Carbon::now())
        );

        return view('preview-and-estimated-salary', [
            'application' => $application,
            'adjustedDataset' => $adjustedDataset,
            'timeline' => $timelineData['timeline'],
            'tableData' => $timelineData['tableData'],
            'timeline_adjusted' => $timelineData_adjusted['timeline'],
            'tableData_adjusted' => $timelineData_adjusted['tableData'],
            'calculatedTotalWorkExperienceMonths' => SalaryEstimationService::calculateTotalWorkExperienceMonths($adjustedDataset->work_experience_adjusted),
            'ansiennitetFromDate' => $workStartDate->subMonths($calculatedTotalWorkExperienceMonths)->format('Y-m-d'),
            'ladder' => $salaryCategory['ladder'],
            'group' => $salaryCategory['group'] !== ('B' || 'D') ? $salaryCategory['group'] : '',
            'salaryPlacement' => EmployeeCV::salaryLadders[$salaryCategory['ladder']][$salaryCategory['group']][$ladderPosition],
        ]);
    }

    public function loadExcel(Request $request)
    {
        // Validate that an Excel file is provided
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ], [
            'excel_file.required' => 'Du må velge en fil.',
            'excel_file.file' => 'Den opplastede filen må være en fil.', // Dette er litt redundant, men kan være nyttig
            'excel_file.mimes' => 'Filen må være en Excel-fil (xlsx, xls) eller en CSV-fil (csv).',
        ]);

        try {
            // Load the uploaded file
            $file = $request->file('excel_file');

            // Use Maatwebsite Excel to read the data from the first sheet
            $data = Excel::toArray([], $file)[0]; // Get the first sheet
            // Optional: Log or view the extracted data (useful for debugging)
            // Log::info($data);
            $application = EmployeeCV::create();
            session(['applicationId' => $application->id]);
            $application->birth_date = $this->isValidExcelDate($data[6][4]) ? Date::excelToDateTimeObject($data[6][4])->format('Y-m-d') : '';
            $application->job_title = $data[7][4];
            $application->work_start_date = $this->isValidExcelDate($data[8][4]) ? Date::excelToDateTimeObject($data[8][4])->format('Y-m-d') : '';

            $education = [];
            $work_experience = [];

            foreach ($data ?? [] as $row => $column) {
                if ($row >= 14 && $row <= 24) {
                    if (! empty(trim($column[1]))) {
                        if (strtolower($column[20]) == 'bestått') {
                            $studyPercentage = '100';
                        } else {
                            if ($this->isValidExcelDate($column[18]) && $this->isValidExcelDate($column[19]) && is_numeric($column[20])) {
                                $studyPercentage = SalaryEstimationService::calculateStudyPercentage(Date::excelToDateTimeObject($column[18])->format('Y-m-d'), Date::excelToDateTimeObject($column[19])->format('Y-m-d'), intval($column[20]));
                            } else {
                                $studyPercentage = '';
                            }
                        }
                        $education[] = ['topic_and_school' => $column[1], 'start_date' => $this->isValidExcelDate($column[18]) ? Date::excelToDateTimeObject($column[18])->format('Y-m-d') : '', 'end_date' => $this->isValidExcelDate($column[19]) ? Date::excelToDateTimeObject($column[19])->format('Y-m-d') : '', 'study_points' => $column[20], 'study_percentage' => $studyPercentage];
                    }
                }

                if ($row >= 27 && $row <= 41) {
                    if (! empty(trim($column[1]))) {
                        $work_experience[] = ['title_workplace' => $column[1], 'work_percentage' => is_numeric($column[15]) ? floatval($column[15]) * 100 : '', 'start_date' => $this->isValidExcelDate($column[16]) ? Date::excelToDateTimeObject($column[16])->format('Y-m-d') : '', 'end_date' => $this->isValidExcelDate($column[17]) ? Date::excelToDateTimeObject($column[17])->format('Y-m-d') : ''];
                    }
                }
            }
            $application->education = $education;
            $application->work_experience = $work_experience;
            $application->save();

            // Example: Perform calculations based on the extracted data
            // $results = $this->performCalculations($data);
            session()->flash('message', 'Excel dokumentet er lastet inn og du kan arbeide videre med den i her.');
            session()->flash('alert-class', 'alert-success');

            return redirect()->route('enter-employment-information');

        } catch (PhpSpreadsheetException $e) {
            return response()->json([
                'message' => 'Error processing the Excel file.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyEducationInformation(Request $request)
    {
        $itemId = $request->input('id');
        $application = EmployeeCV::find(session('applicationId'));

        if ($application) {
            $educationData = $application->education;

            // Remove the item with the matching ID (key)
            unset($educationData[$itemId]);

            // Update the model and save
            $application->education = $educationData;
            $application->save();

            return redirect()->back();
        }

        return redirect()->back();
    }

    public function destroyWorkExperienceInformation(Request $request)
    {
        $itemId = $request->input('id');
        $application = EmployeeCV::find(session('applicationId'));

        if ($application) {
            $workExperienceData = $application->work_experience;

            // Remove the item with the matching ID (key)
            unset($workExperienceData[$itemId]);

            // Update the model and save
            $application->work_experience = $workExperienceData;
            $application->save();

            return redirect()->back();
        }

        return redirect()->back();
    }

    public function exportAsXls()
    {
        if (! session('applicationId')) {
            session()->flash('message', 'Din sesjon er utløpt og du må starte på nytt.');
            session()->flash('alert-class', 'alert-danger');

            return redirect()->route('welcome');
        }

        request()->validate([
            'email' => 'email|required',
        ], [
            'email.required' => 'E-postadressefeltet er obligatorisk.',
            'email.email' => 'E-postadressen må være en gyldig e-postadresse.',
        ]);

        // Get the user's email from the request
        $email = request()->email;

        // Remeber that an email has been sent
        $application = EmployeeCV::find(session('applicationId'));
        $application->email_sent = true;
        $application->save();

        // Dispatch the job
        ExportExcelJob::dispatch(session('applicationId'), $email);
        session()->flash('message', 'En epost med et excel dokument blir sendt i løpet av et par minutter.');
        session()->flash('alert-class', 'alert-success');

        return redirect()->back();

    }

    private function isValidExcelDate($dateString)
    {
        return is_numeric($dateString);
    }
}
