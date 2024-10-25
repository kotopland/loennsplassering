<?php

use App\Http\Controllers\EmployeeCVController;

Route::view('/', 'empty');
Route::get('/salary', [EmployeeCVController::class, 'index'])->name('welcome');
Route::post('/salary/upload-excel', [EmployeeCVController::class, 'loadExcel'])->name('loadExcel');
Route::get('/open-application/{application?}', [EmployeeCVController::class, 'openApplication'])->name('open-application');
Route::post('/send-email', [EmployeeCVController::class, 'sendEmailLink'])->name('send-application-link-to-email');
Route::view('/salary/steps', 'steps')->name('steps');
Route::get('/salary/enter-employment-information/{application?}', [EmployeeCVController::class, 'enterEmploymentInformation'])->name('enter-employment-information');
Route::post('/salary/post-employment-information', [EmployeeCVController::class, 'postEmploymentInformation'])->name('post-employment-information');
Route::get('/salary/enter-education-information/{application?}', [EmployeeCVController::class, 'enterEducationInformation'])->name('enter-education-information');
Route::post('/salary/post-education-information', [EmployeeCVController::class, 'postEducationInformation'])->name('post-education-information');
Route::post('/salary/update-single-education-information', [EmployeeCVController::class, 'updateSingleEducationInformation'])->name('update-single-education-information');
Route::get('/salary/enter-experience-information/{application?}', [EmployeeCVController::class, 'enterExperienceInformation'])->name('enter-experience-information');
Route::post('/salary/post-experience-information', [EmployeeCVController::class, 'postExperienceInformation'])->name('post-experience-information');
Route::post('/salary/update-single-experience-information', [EmployeeCVController::class, 'updateSingleExperienceInformation'])->name('update-single-experience-information');
Route::get('/salary/preview-and-estimated-salary/{application?}', [EmployeeCVController::class, 'previewAndEstimatedSalary'])->name('preview-and-estimated-salary');
Route::get('/salary/export-as-xls', [EmployeeCVController::class, 'exportAsXls'])->name('export-as-xls');

Route::get('/salary/destroy-education-information', [EmployeeCVController::class, 'destroyEducationInformation'])->name('destroy-education-information');
Route::get('/salary/destroy-experience-information', [EmployeeCVController::class, 'destroyWorkExperienceInformation'])->name('destroy-experience-information');

Route::post('/salary/upload', [EmployeeCVController::class, 'upload'])->name('salary.upload');
Route::post('/salary/upload', [EmployeeCVController::class, 'store'])->name('salary.calculate');
