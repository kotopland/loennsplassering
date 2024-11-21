<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalaryLadder;
use Illuminate\Http\Request;

class SalaryLadderController extends Controller
{
    public function index()
    {
        $salaryLadders = SalaryLadder::all();

        return view('admin.salary-ladders.index', compact('salaryLadders'));
    }

    public function create()
    {
        return view('admin.salary-ladders.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'ladder' => 'required|string|max:255',
            'group' => 'required|integer',
            'salaries' => ['required', 'regex:/^\d+(,\d+)*$/'],
        ]);

        $salaries = array_filter(explode(',', $request->salaries), fn ($value) => $value !== '');
        $request->merge(['salaries' => $salaries]);

        SalaryLadder::create($request->all());

        return redirect()->route('salary-ladders.index')->with('success', 'Salary ladder created successfully!');
    }

    public function edit(SalaryLadder $salaryLadder)
    {
        return view('admin.salary-ladders.edit', compact('salaryLadder'));
    }

    public function update(Request $request, SalaryLadder $salaryLadder)
    {
        $request->validate([
            'ladder' => 'required|string|max:255',
            'group' => 'required|integer',
            'salaries' => ['required', 'regex:/^\d+(,\d+)*$/'],
        ]);
        $salaries = array_filter(explode(',', $request->salaries), fn ($value) => $value !== '');
        $request->merge(['salaries' => $salaries]);

        $salaryLadder->update($request->all());

        return redirect()->route('admin.salary-ladders.index')->with('success', 'Salary ladder updated successfully!');
    }

    public function destroy(SalaryLadder $salaryLadder)
    {
        $salaryLadder->delete();

        return redirect()->route('admin.salary-ladders.index')->with('success', 'Salary ladder deleted successfully!');
    }
}
