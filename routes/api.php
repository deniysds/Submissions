<?php

use Illuminate\Support\Facades\Route;
use Modules\Submissions\Http\Controllers\SubmissionsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('submissions', SubmissionsController::class)->names('submissions');
});
