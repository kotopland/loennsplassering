<?php

namespace App\Observers;

use App\Models\EmployeeCV;
use Illuminate\Support\Facades\Log;

class EmployeeCvObserver
{
    /**
     * Handle the "retrieved" event.
     */
    public function retrieved(EmployeeCV $employeeCV)
    {
        try {
            $employeeCV->update(['last_viewed' => now()]);
            Log::info("EmployeeCV ID: {$employeeCV->id} - Last viewed updated.");
        } catch (\Exception $e) {
            Log::error("Failed to update last viewed for EmployeeCV ID: {$employeeCV->id} - ".$e->getMessage());
        }
    }
}
