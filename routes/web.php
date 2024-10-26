<?php

use App\Http\Controllers\EmployeeCVController;

Route::get('/', function () {
    return redirect()->route('welcome');
});
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
Route::get('/lonnsberegner/enter-experience-information/{application?}', [EmployeeCVController::class, 'enterExperienceInformation'])->name('enter-experience-information');
Route::post('/lonnsberegner/post-experience-information', [EmployeeCVController::class, 'postExperienceInformation'])->name('post-experience-information');
Route::post('/lonnsberegner/update-single-experience-information', [EmployeeCVController::class, 'updateSingleExperienceInformation'])->name('update-single-experience-information');
Route::get('/lonnsberegner/preview-and-estimated-lonnsberegner/{application?}', [EmployeeCVController::class, 'previewAndEstimatedSalary'])->name('preview-and-estimated-salary');
Route::get('/lonnsberegner/export-as-xls', [EmployeeCVController::class, 'exportAsXls'])->name('export-as-xls');

Route::get('/lonnsberegner/destroy-education-information', [EmployeeCVController::class, 'destroyEducationInformation'])->name('destroy-education-information');
Route::get('/lonnsberegner/destroy-experience-information', [EmployeeCVController::class, 'destroyWorkExperienceInformation'])->name('destroy-experience-information');

Route::post('/lonnsberegner/upload', [EmployeeCVController::class, 'upload'])->name('lonnsberegner.upload');
Route::post('/lonnsberegner/upload', [EmployeeCVController::class, 'store'])->name('lonnsberegner.calculate');
