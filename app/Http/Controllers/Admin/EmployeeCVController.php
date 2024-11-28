<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeCV;

class EmployeeCVController extends Controller
{
    public function index()
    {
        $employeeCV = EmployeeCV::select(['id', 'job_title', 'work_start_date', 'birth_date', 'email_sent', 'last_viewed'])->get();

        return view('admin.employee-cv.index', compact('employeeCV'));
    }

    public function destroy(EmployeeCV $employeeCV)
    {
        $employeeCV->delete();

        return redirect()->route('admin.employee-cv.index')->with('success', 'Lønnsskjema slettet!');
    }
}
