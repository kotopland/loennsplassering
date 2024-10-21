<?php

namespace App\Http\Controllers;

use App\Imports\EmployeeCVImport;
use App\Mail\SimpleEmail;
use App\Models\EmployeeCV;
use App\Services\SalaryEstimationService;
use Carbon\Carbon; // We'll create this import class later
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeCVController extends Controller
{
    public function index()
    {
        session()->forget('applicationId');

        return view('welcome');
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

    public function openApplication(EmployeeCV $employeeCV)
    {
        session(['applicationId' => $employeeCV->id]);
        session()->flash('message', 'Dine lagrede opplysninger er lastet inn.');
        session()->flash('alert-class', 'alert-success');

        return redirect()->route('enter-employment-information');
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
        $validatedData = $request->validate([
            'topic_and_school' => 'string|required',
            'start_date' => 'date|required',
            'end_date' => 'date|required',
            'study_points' => 'string|required',
            'highereducation' => 'string|nullable',
            'relevance' => 'boolean|sometimes',
        ]);

        $employeeCV = EmployeeCV::find(session('applicationId'));
        $relevance = $validatedData['relevance'] ?? 0;
        $education = $employeeCV->education ?? [];

        if ($validatedData['study_points'] == 'bestått') {
            $studyPercentage = '100';
        } else {
            $studyPercentage = SalaryEstimationService::calculateStudyPercentage($validatedData['start_date'], $validatedData['end_date'], $validatedData['study_points']);
        }
        // dd($validatedData['start_date'], $validatedData['end_date'], '60', $studyPercentage);

        $education[] = [
            'topic_and_school' => $validatedData['topic_and_school'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'study_points' => $validatedData['study_points'],
            'study_percentage' => $studyPercentage,
            'highereducation' => $validatedData['highereducation'],
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
            'workplace_type' => 'string|required',
            'relevance' => 'boolean|sometimes',
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
    // public function upload(Request $request)
    // {
    //     $request->validate([
    //         'excel_file' => 'required|mimes:xlsx,xls',
    //     ]);

    //     Excel::import(new EmployeeCVImport, $request->file('excel_file'));

    //     // Retrieve the latest uploaded data
    //     $employeeCV = EmployeeCV::latest()->first();

    //     // Access the education and work experience data
    //     $educationData = $employeeCV->education;
    //     $workExperienceData = $employeeCV->work_experience;

    //     // Perform your salary calculation logic here based on $educationData and $workExperienceData
    //     // This is where you'll apply your specific rules and algorithms

    //     // For now, let's just return a placeholder salary
    //     $calculatedSalary = 50000; // Replace with your actual calculation

    //     // You can store the calculated salary in the database or pass it to the view for display

    //     return redirect()->back()->with('success', 'File uploaded and processed successfully! Calculated salary: '.$calculatedSalary);
    // }

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
}
