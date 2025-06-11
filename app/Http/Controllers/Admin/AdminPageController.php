<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class AdminPageController extends Controller
{
    /**
     * Display the project's README.md file.
     *
     * @return \Illuminate\View\View
     */
    public function showReadme()
    {
        $readmePath = base_path('README.md');
        $htmlContent = "<p>README.md file not found at project root.</p>";

        if (File::exists($readmePath)) {
            $markdownContent = File::get($readmePath);

            $converter = new GithubFlavoredMarkdownConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
            $htmlContent = $converter->convert($markdownContent)->getContent();
        }

        return view('admin.readme', ['content' => $htmlContent]);
    }
}
