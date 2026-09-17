<?php

use Illuminate\Support\Facades\Route;
use Modules\Submissions\Http\Controllers\EditorSubmissionsController;
use Modules\Submissions\Http\Controllers\ReviewerWorkspaceController;
use Modules\Submissions\Http\Controllers\SubmissionsController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Alur wizard step-by-step submission (Penulis / Author)
    Route::get('submissions/create/step-1', [SubmissionsController::class, 'create'])->name('submissions.create.step1');
    Route::post('submissions/store/step-1', [SubmissionsController::class, 'storeStep1'])->name('submissions.store.step1');

    Route::get('submissions/{id}/create/step-2', [SubmissionsController::class, 'createStep2'])->name('submissions.create.step2');
    Route::post('submissions/{id}/store/step-2', [SubmissionsController::class, 'storeStep2'])->name('submissions.store.step2');
    Route::delete('submissions/{id}/delete-file/{fileId}', [SubmissionsController::class, 'deleteFileStep2'])->name('submissions.delete_file.step2');

    Route::get('submissions/{id}/create/step-3', [SubmissionsController::class, 'createStep3'])->name('submissions.create.step3');
    Route::post('submissions/{id}/store/step-3', [SubmissionsController::class, 'storeStep3'])->name('submissions.store.step3');
    Route::post('submissions/{id}/add-author', [SubmissionsController::class, 'addAuthorStep3'])->name('submissions.add_author.step3');
    Route::delete('submissions/{id}/delete-author/{authorId}', [SubmissionsController::class, 'deleteAuthorStep3'])->name('submissions.delete_author.step3');

    Route::get('submissions/{id}/create/step-4', [SubmissionsController::class, 'createStep4'])->name('submissions.create.step4');
    Route::post('submissions/{id}/finish', [SubmissionsController::class, 'finishStep4'])->name('submissions.finish.step4');

    // Sertifikat Resmi DOI & Ringkasan Publikasi (Author & Editor)
    Route::get('submissions/{id}/doi-certificate', [SubmissionsController::class, 'doiCertificate'])->name('submissions.doi_certificate');

    // Manajemen Penelaahan Dewan Redaksi (Editor / Review Workflow)
    Route::prefix('editor/submissions')->name('editor.submissions.')->group(function () {
        Route::get('/', [EditorSubmissionsController::class, 'index'])->name('index');
        Route::get('/export/csv', [EditorSubmissionsController::class, 'exportCsv'])->name('export');
        Route::get('/{id}', [EditorSubmissionsController::class, 'show'])->name('show');
        Route::get('/{id}/doi-certificate', [EditorSubmissionsController::class, 'doiCertificate'])->name('doi_certificate');
        Route::post('/{id}/decision', [EditorSubmissionsController::class, 'makeDecision'])->name('decision');
        Route::post('/{id}/assign-editor', [EditorSubmissionsController::class, 'assignEditor'])->name('assign_editor');
        Route::post('/{id}/assign-reviewer', [EditorSubmissionsController::class, 'assignReviewer'])->name('assign_reviewer');
    });

    // Ruang Kerja Mitra Bestari (Reviewer Workspace)
    Route::prefix('reviewer/assignments')->name('reviewer.assignments.')->group(function () {
        Route::get('/', [ReviewerWorkspaceController::class, 'index'])->name('index');
        Route::get('/{id}', [ReviewerWorkspaceController::class, 'show'])->name('show');
        Route::post('/{id}/respond', [ReviewerWorkspaceController::class, 'respond'])->name('respond');
        Route::post('/{id}/submit', [ReviewerWorkspaceController::class, 'submitReview'])->name('submit');
        Route::get('/{id}/download/{fileId}', [ReviewerWorkspaceController::class, 'downloadFile'])->name('download');
    });

    // Route bawaan resource
    Route::resource('submissions', SubmissionsController::class)->names('submissions')->except(['create', 'store']);
});
