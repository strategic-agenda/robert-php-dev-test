<?php

use Api\Http\Controllers\Api\TranslationUnitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('v1')->group(function () {
    Route::apiResource('translation-units', TranslationUnitController::class);
    Route::get('translation-units/{id}/history', [TranslationUnitController::class, 'history']);
}); 