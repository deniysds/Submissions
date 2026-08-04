<?php

use Illuminate\Support\Facades\Route;
use Modules\Submissions\Http\Controllers\SubmissionsController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Alur wizard step-by-step submission
    Route::get('submissions/create/step-1', [SubmissionsController::class, 'create'])->name('submissions.create.step1');
    Route::post('submissions/store/step-1', [SubmissionsController::class, 'storeStep1'])->name('submissions.store.step1');

    Route::get('submissions/{id}/create/step-2', [SubmissionsController::class, 'createStep2'])->name('submissions.create.step2');
    Route::post('submissions/{id}/store/step-2', [SubmissionsController::class, 'storeStep2'])->name('submissions.store.step2');
    Route::delete('submissions/{id}/delete-file/{fileId}', [SubmissionsController::class, 'deleteFileStep2'])->name('submissions.delete_file.step2');

    Route::get('submissions/{id}/create/step-3', [SubmissionsController::class, 'createStep3'])->name('submissions.create.step3');
    Route::post('submissions/{id}/store/step-3', [SubmissionsController::class, 'storeStep3'])->name('submissions.store.step3');
    Route::post('submissions/{id}/add-author', [SubmissionsController::class, 'addAuthorStep3'])->name('submissions.add_author.step3');
    Route::delete('submissions/{id}/delete-author/{authorId}', [SubmissionsController::class, 'deleteAuthorStep3'])->name('submissions.delete_author.step3');

    // Route bawaan resource
    Route::resource('submissions', SubmissionsController::class)->names('submissions')->except(['create', 'store']);
});
