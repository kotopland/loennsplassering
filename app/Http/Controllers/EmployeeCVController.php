<?php

namespace App\Http\Controllers;

use App\Exports\ExistingSheetExport;
use App\Mail\SimpleEmail;
use App\Models\EmployeeCV; // We'll create this import class later
use App\Services\SalaryEstimationService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

    public function openApplication(EmployeeCV $employeeCV)
    {
        session(['applicationId' => $employeeCV->id]);
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
        session()->flash('message', 'Lenke til dette skjemaet er nå sendt. Vennligst sjekk at du har fått e-posten.');
        session()->flash('alert-class', 'alert-success');

        return response('Lenke til dette skjemaet er nå sendt. Vennligst sjekk at du har fått e-posten.')->header('Content-Type', 'text/html');

    }

    public function EnterEmploymentInformation()
    {
        // dd(session('applicationId'));
        if (! session('applicationId')) {
            $employeeCV = EmployeeCV::create();
            session(['applicationId' => $employeeCV->id]);
        } else {
            $employeeCV = EmployeeCV::find(session('applicationId'));
        }
        $employeeCV->job_title = $employeeCV->job_title ?? 'Menighet: Menighetsarbeider';
        $employeeCV->work_start_date = $employeeCV->work_start_date ?? '2024-11-02';
        $employeeCV->birth_date = $employeeCV->birth_date ?? '1990-10-02';
        $employeeCV->save();

        $positionsLaddersGroups = EmployeeCV::positionsLaddersGroups;
        ksort($positionsLaddersGroups);

        return view('enter-employment-information', compact('employeeCV', 'positionsLaddersGroups'));
    }

    public function PostEmploymentInformation(Request $request)
    {
        $validatedData = $request->validate([
            'job_title' => 'required',
            'birth_date' => 'required|date',
        ]);
        $employeeCV = EmployeeCV::find(session('applicationId'));
        $employeeCV->job_title = $validatedData['job_title'];
        $employeeCV->birth_date = $validatedData['birth_date'];
        $employeeCV->save();

        return redirect()->route('enter-education-information', compact('employeeCV'));
    }

    public function EnterEducationInformation()
    {

        if (! session('applicationId')) {
            session()->flash('message', 'Din sesjon er utløpt og du må starte på nytt.');
            session()->flash('alert-class', 'alert-danger');

            return redirect()->route('welcome');
        }
        $employeeCV = EmployeeCV::find(session('applicationId'));
        // dd($employeeCV->education);
        // $employeeCV->education = null;
        if ($employeeCV->education == null) {
            $jsonData = [
                1 => [
                    'topic_and_school' => 'Bachelor i Teologi',
                    'start_date' => '2013-09-01',
                    'end_date' => '2016-06-01',
                    'study_points' => 180,
                    'study_percentage' => 100,
                    'highereducation' => 'bachelor',
                    'relevance' => 1,
                ],
                2 => [
                    'topic_and_school' => 'Master i Teologi',
                    'start_date' => '2016-09-01',
                    'end_date' => '2019-06-01',
                    'study_points' => 120,
                    'study_percentage' => 100,
                    'highereducation' => 'master',
                    'relevance' => 1,
                ],
                3 => [
                    'topic_and_school' => 'Bibelskole',
                    'start_date' => '2012-09-01',
                    'end_date' => '2013-06-01',
                    'study_percentage' => 100,
                    'study_points' => 'bestått',
                    'highereducation' => null,
                    'relevance' => 1,
                ],
                4 => [
                    'topic_and_school' => 'Videregående skole',
                    'start_date' => '2008-09-01',
                    'end_date' => '2011-06-01',
                    'study_percentage' => 100,
                    'study_points' => 'bestått',
                    'highereducation' => null,
                    'relevance' => 0,
                ],
                5 => [
                    'topic_and_school' => 'Ledelse og Teologi',
                    'start_date' => '2020-08-15',
                    'end_date' => '2022-06-15',
                    'study_points' => 60,
                    'study_percentage' => 50,
                    'highereducation' => 'master',
                    'relevance' => 1,
                ],
            ];
            $employeeCV->education = $jsonData;
            $employeeCV->save();
        }

        // dd($employeeCV);

        return view('enter-education-information', compact('employeeCV'));
    }

    public function PostEducationInformation(Request $request)
    {

        $request->validate(
            [
                'topic_and_school' => 'string|required',
                'start_date' => 'date|required',
                'end_date' => 'date|required',
                'study_points' => 'string|in:bestått,30,60,120,180,240,300,0|required', // Changed to numeric and in
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

        $employeeCV = EmployeeCV::find(session('applicationId'));
        $relevance = $request->relevance === 'true' ? 1 : 0;
        $education = $employeeCV->education ?? [];

        if (strtolower($request->study_points) === 'bestått') {
            $studyPercentage = '100';
        } else {

            $studyPercentage = SalaryEstimationService::calculateStudyPercentage($request->start_date, $request->end_date, intval($request->study_points));
        }
        // dd($validatedData['start_date'], $validatedData['end_date'], '60', $studyPercentage);

        $education[] = [
            'topic_and_school' => $request->topic_and_school,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'study_points' => $request->study_points,
            'study_percentage' => $studyPercentage,
            'highereducation' => $request->highereducation,
            'relevance' => $relevance,
        ];
        $employeeCV->education = $education;
        $employeeCV->save();

        return redirect()->route('enter-education-information', compact('employeeCV'));
    }

    public function EnterExperienceInformation()
    {
        if (! session('applicationId')) {
            session()->flash('message', 'Din sesjon er utløpt og du må starte på nytt.');
            session()->flash('alert-class', 'alert-danger');

            return redirect()->route('welcome');
        }
        $employeeCV = EmployeeCV::find(session('applicationId'));

        if ($employeeCV->work_experience == null) {
            $jsonData = [
                2 => [
                    'title_workplace' => 'Butikkmedarbeider Rema',
                    'workplace_type' => null,
                    'work_percentage' => 20,
                    'start_date' => '2006-09-01',
                    'end_date' => '2018-07-01',
                    'relevance' => 0,
                ],
                3 => [
                    'title_workplace' => 'Ungdomsarbeider',
                    'work_percentage' => 50,
                    'start_date' => '2012-09-01',
                    'end_date' => '2017-07-01',
                    'workplace_type' => 'freechurch',
                    'relevance' => 1,
                ],
                4 => [
                    'title_workplace' => 'Speiderleder',
                    'work_percentage' => 40,
                    'start_date' => '2015-08-01',
                    'end_date' => '2020-08-01',
                    'workplace_type' => 'other_christian',
                    'relevance' => 1,
                ],
            ];
            $employeeCV->work_experience = $jsonData;
            $employeeCV->save();
        }

        return view('enter-experience-information', compact('employeeCV'));
    }

    public function PostExperienceInformation(Request $request)
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
        $employeeCV = EmployeeCV::find(session('applicationId'));
        $relevance = $validatedData['relevance'] ?? 0;
        $work_experience = $employeeCV->work_experience ?? [];
        $work_experience[] = [
            'title_workplace' => $validatedData['title_workplace'],
            'work_percentage' => $validatedData['work_percentage'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'workplace_type' => $validatedData['workplace_type'],
            'relevance' => $relevance,
        ];
        $employeeCV->work_experience = $work_experience;
        $employeeCV->save();

        return redirect()->route('enter-experience-information', compact('employeeCV'));
    }

    public function previewAndEstimatedSalary(SalaryEstimationService $salaryEstimationService)
    {
        if (! session('applicationId')) {
            session()->flash('message', 'Din sesjon er utløpt og du må starte på nytt.');
            session()->flash('alert-class', 'alert-danger');

            return redirect()->route('welcome');
        }
        $employeeCV = EmployeeCV::find(session('applicationId'));

        $adjustedDataset = $salaryEstimationService->adjustEducationAndWork($employeeCV);

        // dd($adjustedDataset);
        // dd($test->education, $test->work_experience, $test->education_adjusted, $test->work_experience_adjusted);

        // $salaryEstimation = $salaryEstimationService->getSalaryEstimation();

        // dd($employeeCV);
        // dd($employeeCV->work_experience, $employeeCV->work_experience_adjusted);
        $timelineData = $this->createTimelineData($adjustedDataset->education, $adjustedDataset->work_experience);
        $timelineData_adjusted = $this->createTimelineData($adjustedDataset->education_adjusted, $adjustedDataset->work_experience_adjusted);

        $workStartDate = Carbon::parse($employeeCV->work_start_date);
        $calculatedTotalWorkExperienceMonths = $this->calculateTotalWorkExperienceMonths($adjustedDataset->work_experience_adjusted);
        $ansiennitetFromDate = $workStartDate->subMonths($calculatedTotalWorkExperienceMonths);

        return view('preview-and-estimated-salary', [
            'employeeCV' => $employeeCV,
            'adjustedDataset' => $adjustedDataset,
            'timeline' => $timelineData['timeline'],
            'tableData' => $timelineData['tableData'],
            'timeline_adjusted' => $timelineData_adjusted['timeline'],
            'tableData_adjusted' => $timelineData_adjusted['tableData'],
            'calculatedTotalWorkExperienceMonths' => $this->calculateTotalWorkExperienceMonths($adjustedDataset->work_experience_adjusted),
            'ansiennitetFromDate' => $ansiennitetFromDate,
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

    public function calculateTotalWorkExperienceMonths($workExperienceData)
    {
        $totalMonths = 0;

        foreach ($workExperienceData as $workExperience) {
            $startDate = new DateTime($workExperience['start_date']);
            $endDate = new DateTime($workExperience['end_date']);

            // Calculate the difference in months
            $diffInMonths = ($endDate->format('Y') - $startDate->format('Y')) * 12 + $endDate->format('n') - $startDate->format('n') + 1;

            // Multiply by work percentage and add to the total
            $totalMonths += ($diffInMonths * $workExperience['work_percentage']) / 100;
        }

        return $totalMonths;
    }

    public function loadExcel(Request $request)
    {
        // Validate that an Excel file is provided
        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            // Load the uploaded file
            $file = $request->file('excel_file');

            // Use Maatwebsite Excel to read the data from the first sheet
            $data = Excel::toArray([], $file)[0]; // Get the first sheet
            // Optional: Log or view the extracted data (useful for debugging)
            // Log::info($data);
            $employeeCV = EmployeeCV::create();
            session(['applicationId' => $employeeCV->id]);

            $employeeCV->birth_date = Date::excelToDateTimeObject($data[6][4])->format('Y-m-d');
            $employeeCV->job_title = $data[7][4];
            $employeeCV->work_start_date = Date::excelToDateTimeObject($data[8][4])->format('Y-m-d');

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
                        $work_experience[] = ['title_workplace' => $column[1], 'work_percentage' => floatval($column[15]) * 100, 'start_date' => Date::excelToDateTimeObject($column[16])->format('Y-m-d'), 'end_date' => Date::excelToDateTimeObject($column[17])->format('Y-m-d')];
                    }
                }
            }
            $employeeCV->education = $education;
            $employeeCV->work_experience = $work_experience;
            $employeeCV->save();

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
        $employeeCV = EmployeeCV::find(session('applicationId'));

        if ($employeeCV) {
            $educationData = $employeeCV->education;

            // Remove the item with the matching ID (key)
            unset($educationData[$itemId]);

            // Update the model and save
            $employeeCV->education = $educationData;
            $employeeCV->save();

            return redirect()->back();
        }

        return redirect()->back();
    }

    public function destroyWorkExperienceInformation(Request $request)
    {
        $itemId = $request->input('id');
        $employeeCV = EmployeeCV::find(session('applicationId'));

        if ($employeeCV) {
            $workExperienceData = $employeeCV->work_experience;

            // Remove the item with the matching ID (key)
            unset($workExperienceData[$itemId]);

            // Update the model and save
            $employeeCV->work_experience = $workExperienceData;
            $employeeCV->save();

            return redirect()->back();
        }

        return redirect()->back();
    }

    public function exportAsXls(SalaryEstimationService $salaryEstimationService, Excel $excel)
    {

        if (! session('applicationId')) {
            session()->flash('message', 'Din sesjon er utløpt og du må starte på nytt.');
            session()->flash('alert-class', 'alert-danger');

            return redirect()->route('welcome');
        }
        $employeeCV = EmployeeCV::find(session('applicationId'));
        $employeeCV = $salaryEstimationService->adjustEducationAndWork($employeeCV);
        $timelineData = $this->createTimelineData($employeeCV->education, $employeeCV->work_experience);
        $timelineData_adjusted = $this->createTimelineData($employeeCV->education_adjusted, $employeeCV->work_experience_adjusted);

        $workStartDate = Carbon::parse($employeeCV->work_start_date);
        $calculatedTotalWorkExperienceMonths = $this->calculateTotalWorkExperienceMonths($employeeCV->work_experience_adjusted);
        $ansiennitetFromDate = $workStartDate->subMonths($calculatedTotalWorkExperienceMonths);

        // Prepare the data to be inserted
        $data = [
            ['row' => 8, 'column' => 'E', 'value' => $employeeCV->job_title, 'datatype' => 'text'],
            ['row' => 7, 'column' => 'E', 'value' => $employeeCV->birth_date, 'datatype' => 'date'],
            ['row' => 9, 'column' => 'R', 'value' => $employeeCV->work_start_date, 'datatype' => 'date'],
        ];

        $row = 15;
        foreach ($employeeCV->education_adjusted as $item) {

            $data[] = ['row' => $row, 'column' => 'B', 'value' => $item['topic_and_school'], 'datatype' => 'text'];
            $data[] = ['row' => $row, 'column' => 'S', 'value' => $item['start_date'], 'datatype' => 'date'];
            $data[] = ['row' => $row, 'column' => 'T', 'value' => $item['end_date'], 'datatype' => 'date'];
            $data[] = ['row' => $row, 'column' => 'U', 'value' => $item['study_points'], 'datatype' => 'text'];
            $data[] = ['row' => $row, 'column' => 'AA', 'value' => $item['highereducation'].($item['relevance'] ? 'relevant' : ''), 'datatype' => 'text'];
            $row++;
        }

        $row = 28;
        foreach ($employeeCV->work_experience as $enteredItem) {
            $data[] = ['row' => $row, 'column' => 'B', 'value' => $enteredItem['title_workplace'], 'datatype' => 'text'];
            $data[] = ['row' => $row, 'column' => 'P', 'value' => $enteredItem['work_percentage'] / 100, 'datatype' => 'number'];
            $data[] = ['row' => $row, 'column' => 'Q', 'value' => $enteredItem['start_date'], 'datatype' => 'date'];
            $data[] = ['row' => $row, 'column' => 'R', 'value' => $enteredItem['end_date'], 'datatype' => 'date'];
            $data[] = ['row' => $row, 'column' => 'AB', 'value' => 'Opprinnelig registrert', 'datatype' => 'text'];
            $data[] = ['row' => $row, 'column' => 'AC', 'value' => @$enteredItem['relevance'] ? 'relevant' : '', 'datatype' => 'text'];
            $row++;
        }

        foreach ($employeeCV->work_experience_adjusted as $adjustedItem) {
            $data[] = ['row' => $row, 'column' => 'B', 'value' => $adjustedItem['title_workplace'], 'datatype' => 'text'];
            $data[] = ['row' => $row, 'column' => 'P', 'value' => $adjustedItem['work_percentage'] / 100, 'datatype' => 'number'];
            $data[] = ['row' => $row, 'column' => 'Q', 'value' => $adjustedItem['start_date'], 'datatype' => 'date'];
            $data[] = ['row' => $row, 'column' => 'R', 'value' => $adjustedItem['end_date'], 'datatype' => 'date'];
            $data[] = ['row' => $row, 'column' => 'T', 'value' => @$adjustedItem['relevance'] ? 1 : 0.5, 'datatype' => 'number'];
            $data[] = ['row' => $row, 'column' => 'AB', 'value' => 'Maskinelt modifisert', 'datatype' => 'text'];
            $row++;
        }

        $salaryCategory = EmployeeCV::positionsLaddersGroups[$employeeCV->job_title];

        $ladder = $salaryCategory['ladder'];
        $group = $salaryCategory['group'] !== ('B' || 'D') ? $salaryCategory['group'] : '';
        $salaryPlacement = EmployeeCV::salaryLadders[$salaryCategory['ladder']][$salaryCategory['group']][intval($calculatedTotalWorkExperienceMonths / 12) + 2];
        $data[] = ['row' => 62, 'column' => 'S', 'value' => $ladder, 'datatype' => 'text'];
        $data[] = ['row' => 64, 'column' => 'S', 'value' => $group, 'datatype' => 'text'];
        $data[] = ['row' => 67, 'column' => 'S', 'value' => $salaryPlacement, 'datatype' => 'text'];
        $data[] = ['row' => 71, 'column' => 'S', 'value' => $employeeCV->competence_points, 'datatype' => 'text'];

        // Define the path to the original file and the modified file
        $originalFilePath = '14lonnsskjema.xlsx'; // Stored in storage/app/public
        $modifiedFilePath = 'modified_14lonnsskjema.xlsx'; // New modified file path

        $export = new ExistingSheetExport($data, $originalFilePath);

        // Modify and save the Excel file
        $export->modifyAndSave($modifiedFilePath);

        // Download the saved file
        return response()->download(storage_path('app/public/'.$modifiedFilePath));

    }

    private function performCalculations(array $data)
    {
        $total = 0;
        $validRows = 0;

        foreach ($data as $index => $row) {
            // Skip header row (assuming it's the first row)
            if ($index === 0) {
                continue;
            }

            // Example calculation: Sum a specific numeric column (e.g., column 2)
            $value = is_numeric($row[1]) ? $row[1] : 0;
            $total += $value;

            if ($value > 0) {
                $validRows++;
            }
        }

        // Return summary calculations
        return [
            'total_sum' => $total,
            'valid_rows' => $validRows,
            'average' => $validRows > 0 ? $total / $validRows : 0,
        ];
    }
}
