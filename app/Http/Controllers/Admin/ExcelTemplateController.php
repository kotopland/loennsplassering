<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExcelTemplateController extends Controller
{
    private string $templateDirectory = 'public';
    private array $allowedTemplateFiles = [
        [
            'filename' => '14lonnsskjema.xlsx',
            'help' => 'Dette er originalskjemaet som vi gir arbeidsgivere/ansatte og som gjøres tilgjengelig på frikirkens sine hjemmesider. Dersom skjemaet endrer seg på rader og plassering av informasjon, må webappen oppdateres av utvikler.'
        ],
        [
            'filename' => '14lonnsskjema-expanded.xlsx',
            'help' => 'Ofte er deet behov for flere linjer i excel filen på ansiennitet. Utdanning starter fra celle B15 som er den samme som originalskjemaet. Derimot starter Ansiennitetsopplysninger fra B39! Det er viktig å oppdatere formlene der du har opprettet nye rader. Pass også på at ark2 er oppdatert.'
        ],
        [
            'filename' => '14lonnsskjema-extraexpanded.xlsx',
            'help' => 'Dette skjemaet er når listen over ansiennitet blir enda lengre enn skjemaet over. Utdanning starter fra celle B15 som er den samme som originalskjemaet. Derimot starter Ansiennitetsopplysninger fra B55! Det er viktig å oppdatere formlene der du har opprettet nye rader. Pass også på at ark2 er oppdatert.'
        ],
    ];

    public function __construct()
    {
        // Ensure the template directory exists
        if (!Storage::exists($this->templateDirectory)) {
            Storage::makeDirectory($this->templateDirectory);
        }
    }

    /**
     * Helper method to find a template configuration by its filename.
     *
     * @param string $templateName
     * @return array|null
     */
    private function findTemplateConfig(string $templateName): ?array
    {
        foreach ($this->allowedTemplateFiles as $fileConfig) {
            if ($fileConfig['filename'] === $templateName) {
                return $fileConfig;
            }
        }
        return null;
    }

    /**
     * Display a listing of the excel templates and forms to manage them.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $templates = [];
        foreach ($this->allowedTemplateFiles as $file) {
            $templates[] = [
                'name' => $file['filename'],
                'help' => $file['help'],
                'exists' => Storage::disk('local')->exists($this->templateDirectory . '/' . $file['filename']),
            ];
        }

        return view('admin.excel-templates.index', compact('templates'));
    }

    /**
     * Download the specified template file.
     *
     * @param  string  $templateName
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function download(string $templateName)
    {
        // Validate if the templateName is one of the allowed filenames
        if (!$this->findTemplateConfig($templateName)) {
            return redirect()->route('admin.excel-templates.index')->with('error', 'Invalid template file specified.');
        }

        $filePath = $this->templateDirectory . '/' . $templateName;

        if (!Storage::disk('local')->exists($filePath)) {
            return redirect()->route('admin.excel-templates.index')->with('error', 'Template file not found: ' . $templateName);
        }

        return Storage::disk('local')->download($filePath, $templateName);
    }

    /**
     * Upload and replace the specified template file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $templateName
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request, string $templateName)
    {
        // Validate if the templateName is one of the allowed filenames
        if (!$this->findTemplateConfig($templateName)) {
            return redirect()->route('admin.excel-templates.index')->with('error', 'Invalid template file specified for upload.');
        }

        $validator = Validator::make($request->all(), [
            'template_file' => ['required', 'file', 'mimes:xlsx', 'max:5120'], // 5MB limit
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.excel-templates.index')
                ->withErrors($validator, 'upload_' . str_replace('.', '_', $templateName)) // Custom error bag
                ->withInput();
        }

        $request->file('template_file')->storeAs($this->templateDirectory, $templateName, 'local');

        return redirect()->route('admin.excel-templates.index')->with('success', 'Template "' . $templateName . '" uploaded successfully.');
    }
}
