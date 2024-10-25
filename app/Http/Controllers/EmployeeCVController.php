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
        session()->forget('applicationId');

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
        ]);

        $subject = 'Lenke til foreløpig lønnsberegning';
        $body = 'Denne lenken går til dine registrerte opplysninger <a href="'.route('open-application', session('applicationId')).'">'.route('open-application', session('applicationId')).'</a>';
        Mail::to($validatedData['email_address'])->send(new SimpleEmail($subject, $body));

        // Remeber that an email has been sent
        $application = EmployeeCV::find(session('applicationId'));
        $application->email_sent = true;
        $application->save();

        session()->flash('message', 'Lenke til dette skjemaet er nå sendt. Vennligst sjekk at du har fått e-posten.');
        session()->flash('alert-class', 'alert-success');

        return response('Lenke til dette skjemaet er nå sendt. Vennligst sjekk at du har fått e-posten.')->header('Content-Type', 'text/html');

    }

    public function enterEmploymentInformation(?EmployeeCV $application)
    {
        $this->checkForSavedApplication($application);

        $application->job_title = $application->job_title ?? 'Menighet: Menighetsarbeider';
        $application->work_start_date = $application->work_start_date ?? '2024-11-02';
        $application->birth_date = $application->birth_date ?? '1990-10-02';
        $application->save();

        $positionsLaddersGroups = EmployeeCV::positionsLaddersGroups;
        ksort($positionsLaddersGroups);

        return view('enter-employment-information', compact('application', 'positionsLaddersGroups'));
    }

    private function checkForSavedApplication($application)
    {
        if (is_null($application->id)) {
            if (! session('applicationId') && ! request()->filled('applicationId')) {
                $application = EmployeeCV::create();
                session(['applicationId' => $application->id]);

                return redirect()->route('enter-employment-information', $application->id);
            } else {
                if (session('applicationId')) {
                    $application = EmployeeCV::find(session('applicationId'));
                } else {
                    $application = EmployeeCV::find(request()->applicationId);
                }

                return redirect()->route('enter-employment-information', $application->id);
            }
        }
    }

    public function postEmploymentInformation(Request $request)
    {
        $request->validate([
            'job_title' => 'required',
            'birth_date' => 'required|date',
        ]);
        $application = EmployeeCV::find(session('applicationId'));
        $application->job_title = $request->job_title;
        $application->birth_date = $request->birth_date;
        $application->save();

        return redirect()->route('enter-education-information', compact('application'));
    }

    public function enterEducationInformation(EmployeeCV $application)
    {
        if (! session('applicationId')) {
            session()->flash('message', 'Din sesjon er utløpt og du må starte på nytt.');
            session()->flash('alert-class', 'alert-danger');

            return redirect()->route('welcome');
        }
        $this->checkForSavedApplication($application);

        $application = EmployeeCV::find(session('applicationId'));
        // dd($application->education);
        // $application->education = null;
        // if ($application->education == null) {
        //     $jsonData = [
        //         1 => [
        //             'topic_and_school' => 'Bachelor i Teologi',
        //             'start_date' => '2013-09-01',
        //             'end_date' => '2016-06-01',
        //             'study_points' => 180,
        //             'study_percentage' => 100,
        //             'highereducation' => 'bachelor',
        //             'relevance' => 1,
        //         ],
        //         2 => [
        //             'topic_and_school' => 'Master i Teologi',
        //             'start_date' => '2016-09-01',
        //             'end_date' => '2019-06-01',
        //             'study_points' => 120,
        //             'study_percentage' => 100,
        //             'highereducation' => 'master',
        //             'relevance' => 1,
        //         ],
        //         3 => [
        //             'topic_and_school' => 'Bibelskole',
        //             'start_date' => '2012-09-01',
        //             'end_date' => '2013-06-01',
        //             'study_percentage' => 100,
        //             'study_points' => 'bestått',
        //             'highereducation' => null,
        //             'relevance' => 1,
        //         ],
        //         4 => [
        //             'topic_and_school' => 'Videregående skole',
        //             'start_date' => '2008-09-01',
        //             'end_date' => '2011-06-01',
        //             'study_percentage' => 100,
        //             'study_points' => 'bestått',
        //             'highereducation' => null,
        //             'relevance' => 0,
        //         ],
        //         5 => [
        //             'topic_and_school' => 'Ledelse og Teologi',
        //             'start_date' => '2020-08-15',
        //             'end_date' => '2022-06-15',
        //             'study_points' => 60,
        //             'study_percentage' => 50,
        //             'highereducation' => 'master',
        //             'relevance' => 1,
        //         ],
        //     ];
        //     $application->education = $jsonData;
        //     $application->save();
        // }

        // dd($application);

        return view('enter-education-information', compact('application'));
    }

    public function postEducationInformation(Request $request)
    {

        $request->validate(
            [
                'topic_and_school' => 'string|required',
                'start_date' => 'date|required',
                'end_date' => 'date|required',
                'study_points' => 'string|in:bestått,10,20,30,60,120,180,240,300,0|required', // Changed to numeric and in
                'highereducation' => 'string|sometimes|nullable|in:bachelor,master', // Added in validation
                'relevance' => 'in:true,false|nullable', // Removed required
            ],
            [
                'topic_and_school.required' => 'Vennligst fyll inn navnet på studiet og skolen.',
                'topic_and_school.string' => 'Navnet på studiet må være tekst.',
                'start_date.required' => 'Vennligst velg en startdato.',
                'start_date.date' => 'Ugyldig dato format.',
                'end_date.required' => 'Vennligst velg en sluttdato.',
                'end_date.date' => 'Ugyldig dato format.',
                'study_points.required' => 'Vennligst velg antall studiepoeng.',
                'study_points.numeric' => 'Studiepoeng må være et tall.',
                'study_points.in' => 'Ugyldig antall studiepoeng.',
                'highereducation.in' => 'Ugyldig type studie.',
                'relevance.boolean' => 'Relevanse må være avkrysset eller ikke avkrysset.',
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
                'study_points' => 'string|in:bestått,10,20,30,60,120,180,240,300,0|required', // Changed to numeric and in
                'highereducation' => 'string|sometimes|nullable|in:bachelor,master', // Added in validation
                'relevance' => 'in:true,false|nullable', // Removed required
            ],
            [
                'edit.required' => 'Mangler id',
                'topic_and_school.required' => 'Vennligst fyll inn navnet på studiet og skolen.',
                'topic_and_school.string' => 'Navnet på studiet må være tekst.',
                'start_date.required' => 'Vennligst velg en startdato.',
                'start_date.date' => 'Ugyldig dato format.',
                'end_date.required' => 'Vennligst velg en sluttdato.',
                'end_date.date' => 'Ugyldig dato format.',
                'study_points.required' => 'Vennligst velg antall studiepoeng.',
                'study_points.numeric' => 'Studiepoeng må være et tall.',
                'study_points.in' => 'Ugyldig antall studiepoeng.',
                'highereducation.in' => 'Ugyldig type studie.',
                'relevance.boolean' => 'Relevanse må være avkrysset eller ikke avkrysset.',
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

    public function enterExperienceInformation(EmployeeCV $application)
    {
        if (! session('applicationId')) {
            session()->flash('message', 'Din sesjon er utløpt og du må starte på nytt.');
            session()->flash('alert-class', 'alert-danger');

            return redirect()->route('welcome');
        }
        $this->checkForSavedApplication($application);

        $application = EmployeeCV::find(session('applicationId'));

        // if ($application->work_experience == null) {
        //     $jsonData = [
        //         2 => [
        //             'title_workplace' => 'Butikkmedarbeider Rema',
        //             'workplace_type' => null,
        //             'work_percentage' => 20,
        //             'start_date' => '2006-09-01',
        //             'end_date' => '2018-07-01',
        //             'relevance' => 0,
        //         ],
        //         3 => [
        //             'title_workplace' => 'Ungdomsarbeider',
        //             'work_percentage' => 50,
        //             'start_date' => '2012-09-01',
        //             'end_date' => '2017-07-01',
        //             'workplace_type' => 'freechurch',
        //             'relevance' => 1,
        //         ],
        //         4 => [
        //             'title_workplace' => 'Speiderleder',
        //             'work_percentage' => 40,
        //             'start_date' => '2015-08-01',
        //             'end_date' => '2020-08-01',
        //             'workplace_type' => 'other_christian',
        //             'relevance' => 1,
        //         ],
        //     ];
        //     $application->work_experience = $jsonData;
        //     $application->save();
        // }

        return view('enter-experience-information', compact('application'));
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
        ],
            [
                'title_workplace.required' => 'Vennligst fyll inn tittel og arbeidssted.',
                'title_workplace.string' => 'Navnet må være tekst.',
                'start_date.required' => 'Vennligst velg en startdato.',
                'start_date.date' => 'Ugyldig dato format.',
                'end_date.required' => 'Vennligst velg en sluttdato.',
                'end_date.date' => 'Ugyldig dato format.',
                'workplace_type.in' => 'Ugyldig type type arbeidssted.',
                'relevance.boolean' => 'Relevanse må være avkrysset eller ikke avkrysset.',
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
        ],
            [
                'edit.required' => 'Mangler id',
                'title_workplace.required' => 'Vennligst fyll inn tittel og arbeidssted.',
                'title_workplace.string' => 'Navnet må være tekst.',
                'start_date.required' => 'Vennligst velg en startdato.',
                'start_date.date' => 'Ugyldig dato format.',
                'end_date.required' => 'Vennligst velg en sluttdato.',
                'end_date.date' => 'Ugyldig dato format.',
                'workplace_type.in' => 'Ugyldig type type arbeidssted.',
                'relevance.boolean' => 'Relevanse må være avkrysset eller ikke avkrysset.',
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

        $this->checkForSavedApplication($application);

        $application = EmployeeCV::find(session('applicationId'));
        if (is_null($application->education) || is_null($application->work_experience)) {
            $message = 'Vi kan ikke beregne en midlertidig lønnsplassering før følgende er fylt ut: ';
            $message .= is_null($application->education) ? 'kompetanse' : '';
            $message .= is_null($application->work_experience) ? ' - ansiennitet' : '';

            session()->flash('message', $message);
            session()->flash('alert-class', 'alert-danger');

            return redirect()->route('enter-employment-information');
        }
        $adjustedDataset = $salaryEstimationService->adjustEducationAndWork($application);

        $timelineData = $this->createTimelineData($adjustedDataset->education, $adjustedDataset->work_experience);
        $timelineData_adjusted = $this->createTimelineData($adjustedDataset->education_adjusted, $adjustedDataset->work_experience_adjusted);

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

    private function createTimelineData($educationData, $workExperienceData)
    {
        $allData = [];
        foreach ($educationData as $education) {
            $allData[] = [
                'title' => $education['topic_and_school'],
                'start_date' => $education['start_date'],
                'end_date' => $education['end_date'],
                'percentage' => $education['study_percentage'],
                'type' => 'education',
            ];
        }
        foreach ($workExperienceData as $workExperience) {
            $allData[] = [
                'title' => $workExperience['title_workplace'],
                'start_date' => $workExperience['start_date'],
                'end_date' => $workExperience['end_date'],
                'percentage' => $workExperience['work_percentage'],
                'type' => 'work',
            ];
        }

        // 2. Determine the timeline

        $earliestMonth = min(array_map(function ($item) {
            return strtotime($item['start_date']);
        }, $allData));
        $latestMonth = max(array_map(function ($item) {
            return strtotime($item['end_date']);
        }, $allData));
        $timeline = [];
        $currentMonth = $earliestMonth;
        while ($currentMonth <= $latestMonth) {
            $timeline[] = date('Y-m', $currentMonth);
            $currentMonth = strtotime('+1 month', $currentMonth);
        }

        return [
            'timeline' => $timeline,
            'tableData' => $allData,
        ];
    }

    public function loadExcel(Request $request)
    {
        // Validate that an Excel file is provided
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
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

            $application->birth_date = Date::excelToDateTimeObject($data[6][4])->format('Y-m-d');
            $application->job_title = $data[7][4];
            $application->work_start_date = Date::excelToDateTimeObject($data[8][4])->format('Y-m-d');

            $education = [];
            $work_experience = [];

            foreach ($data as $row => $column) {
                if ($row >= 14 && $row <= 24) {
                    if (! empty(trim($column[1]))) {
                        if (strtolower($column[20]) == 'bestått') {
                            $studyPercentage = '100';
                        } else {
                            $studyPercentage = SalaryEstimationService::calculateStudyPercentage(Date::excelToDateTimeObject($column[18])->format('Y-m-d'), Date::excelToDateTimeObject($column[19])->format('Y-m-d'), intval($column[20]));
                        }
                        $education[] = ['topic_and_school' => $column[1], 'start_date' => Date::excelToDateTimeObject($column[18])->format('Y-m-d'), 'end_date' => Date::excelToDateTimeObject($column[19])->format('Y-m-d'), 'study_points' => $column[20], 'study_percentage' => $studyPercentage];
                    }
                }

                if ($row >= 27 && $row <= 41) {
                    if (! empty(trim($column[1]))) {
                        $work_experience[] = ['title_workplace' => $column[1], 'work_percentage' => is_numeric($column[15]) ? floatval($column[15]) * 100 : '', 'start_date' => Date::excelToDateTimeObject($column[16])->format('Y-m-d'), 'end_date' => Date::excelToDateTimeObject($column[17])->format('Y-m-d')];
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
}
