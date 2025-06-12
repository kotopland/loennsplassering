<?php

use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\ExcelTemplateController;
use App\Http\Controllers\Admin\SalaryLadderController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmployeeCVController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'sendLoginLink'])->name('login');
Route::get('/login/{token}', [LoginController::class, 'processLoginLink'])->name('login.process');

Route::get('/login', function () {
    return view('auth-login.index');
});
Route::post('/logout', function () {
    Auth::logout();

    return redirect('/')->with('success', 'Logged out successfully!');
})->name('logout');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::view('/', 'admin.index')->name('admin.index');
        Route::resource('positions', PositionController::class);
        Route::resource('salary-ladders', SalaryLadderController::class);
        Route::resource('employee-cv', \App\Http\Controllers\Admin\EmployeeCVController::class)->only(['index', 'destroy']);
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::get('/readme', [AdminPageController::class, 'showReadme'])->name('readme.show');

        Route::prefix('excel-templates')->name('excel-templates.')->controller(ExcelTemplateController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/download/{templateName}', 'download')->name('download')->where('templateName', '[a-zA-Z0-9_.-]+\.xlsx');
            Route::post('/upload/{templateName}', 'upload')->name('upload')->where('templateName', '[a-zA-Z0-9_.-]+\.xlsx');
        });
    });
});

Route::get('/', function () {
    return redirect()->route('welcome');
});
Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::get('/lonnsberegner', [EmployeeCVController::class, 'index'])->name('welcome');
Route::post('/lonnsberegner/upload-excel', [EmployeeCVController::class, 'loadExcel'])->name('loadExcel');
Route::get('/open-application/{application?}', [EmployeeCVController::class, 'openApplication'])->name('open-application');
Route::post('/send-email', [EmployeeCVController::class, 'sendEmailLink'])->name('send-application-link-to-email');
Route::view('/lonnsberegner/steps', 'steps')->name('steps');
Route::get('/lonnsberegner/enter-employment-information/{application?}', [EmployeeCVController::class, 'enterEmploymentInformation'])->name('enter-employment-information');
Route::post('/lonnsberegner/post-employment-information', [EmployeeCVController::class, 'postEmploymentInformation'])->name('post-employment-information');
Route::get('/lonnsberegner/enter-education-information/{application?}', [EmployeeCVController::class, 'enterEducationInformation'])->name('enter-education-information');
Route::post('/lonnsberegner/post-education-information', [EmployeeCVController::class, 'postEducationInformation'])->name('post-education-information');
Route::post('/lonnsberegner/update-single-education-information', [EmployeeCVController::class, 'updateSingleEducationInformation'])->name('update-single-education-information');
Route::get('/lonnsberegner/update-relevance-on-education-information', [EmployeeCVController::class, 'updateRelevanceOnEducationInformation'])->name('update-relevance-on-education-information');
Route::get('/lonnsberegner/enter-experience-information/{application?}', [EmployeeCVController::class, 'enterExperienceInformation'])->name('enter-experience-information');
Route::post('/lonnsberegner/post-experience-information', [EmployeeCVController::class, 'postExperienceInformation'])->name('post-experience-information');
Route::post('/lonnsberegner/update-single-experience-information', [EmployeeCVController::class, 'updateSingleExperienceInformation'])->name('update-single-experience-information');
Route::get('/lonnsberegner/update-relevance-on-experience-information', [EmployeeCVController::class, 'updateRelevanceOnExperienceInformation'])->name('update-relevance-on-experience-information');
Route::get('/lonnsberegner/enter-enter-courses-and-activities/{application?}', [EmployeeCVController::class, 'enterCoursesAndActivityInformation'])->name('enter-courses-and-activity-information');
Route::get('/lonnsberegner/preview-and-estimated-lonnsberegner/{application?}', [EmployeeCVController::class, 'previewAndEstimatedSalary'])->name('preview-and-estimated-salary');
Route::get('/lonnsberegner/export-as-xls', [EmployeeCVController::class, 'exportAsXls'])->name('export-as-xls');

Route::get('/lonnsberegner/destroy-education-information', [EmployeeCVController::class, 'destroyEducationInformation'])->name('destroy-education-information');
Route::get('/lonnsberegner/destroy-experience-information', [EmployeeCVController::class, 'destroyWorkExperienceInformation'])->name('destroy-experience-information');

Route::post('/lonnsberegner/upload', [EmployeeCVController::class, 'upload'])->name('lonnsberegner.upload');
Route::post('/lonnsberegner/upload', [EmployeeCVController::class, 'store'])->name('lonnsberegner.calculate');

Route::get('/logg-ut', [EmployeeCVController::class, 'signout'])->name('signout');

Route::get('test-email', function () {
    return new \App\Mail\SimpleEmail('test', 'test');
});
