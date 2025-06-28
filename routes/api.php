<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TranslationUnitController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are within the "api" middleware group (prefix `/api`).
|
*/

// CRUD endpoints for translation units
Route::apiResource('translation-units', TranslationUnitController::class);

// (Optional) Fetch version history
Route::get('translation-units/{translation_unit}/versions', [TranslationUnitController::class, 'versions']);
