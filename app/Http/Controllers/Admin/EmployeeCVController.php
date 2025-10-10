<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeCV;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class EmployeeCVController extends Controller
{
    public function index()
    {
        $employeeCV = EmployeeCV::select(['id', 'job_title', 'work_start_date', 'birth_date', 'email_sent', 'last_viewed', 'status', 'generated_file_path', 'personal_info', 'updated_at'])->get();

        return view('admin.employee-cv.index', compact('employeeCV'));
    }

    public function destroy(EmployeeCV $employeeCv)
    {
        $employeeCv->delete();

        return redirect()->route('admin.employee-cv.index')->with('success', 'Lønnsskjema slettet!');
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
}
