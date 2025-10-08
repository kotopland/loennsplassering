<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'report_email' => 'required|email',
        ]);

        Setting::updateOrCreate(
            ['key' => 'report_email'],
            ['value' => $validated['report_email']]
        );

        return redirect()->route('admin.settings.index')->with('success', 'Innstillinger oppdatert.');
    }
}
