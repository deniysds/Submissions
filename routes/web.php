<?php

use Illuminate\Support\Facades\Route;
use Modules\Submissions\Http\Controllers\SubmissionsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('submissions', SubmissionsController::class)->names('submissions');
});
