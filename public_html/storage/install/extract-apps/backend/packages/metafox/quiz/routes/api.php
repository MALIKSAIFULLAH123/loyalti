<?php

namespace MetaFox\Quiz\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Quiz's Forms
Route::controller(QuizController::class)
    ->prefix('quiz')
    ->group(function () {
        Route::get('search-form', 'searchForm');
        Route::patch('approve/{id}', 'approve');
        Route::patch('feature/{id}', 'feature');
        Route::patch('sponsor/{id}', 'sponsor');
        Route::patch('sponsor-in-feed/{id}', 'sponsorInFeed');
    });

Route::controller(QuestionController::class)->group(function () {
    Route::get('quiz-question/view-plays', 'viewPlays');
});

//Quiz Result API endpoints
Route::controller(ResultController::class)->group(function () {
    Route::get('quiz-result/view-individual-play', 'viewIndividualPlay');
});

Route::resource('quiz', QuizController::class);
Route::resource('quiz-result', ResultController::class)->only(['index', 'store']);
