<?php

namespace App\Imports;

use App\Models\EmployeeCV;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeCVImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Process and transform the Excel data into the format expected by your model
        $educationData = []; // Extract education data from $row
        $workExperienceData = []; // Extract work experience data from $row

        return new EmployeeCV([
            'education' => $educationData,
            'work_experience' => $workExperienceData,
        ]);
    }
}
