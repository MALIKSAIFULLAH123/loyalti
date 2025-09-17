<?php

namespace MetaFox\Localize\Http\Controllers\Api;

use Illuminate\Support\Facades\Route;

Route::controller(PhraseAdminController::class)
    ->as('phrase.')
    ->group(function () {
        Route::get('phrase/suggest', 'suggest')->name('suggest');
        Route::delete('phrase', 'batchDelete')->name('deleteAll');
        Route::get('localize/phrase/missing', 'missing')->name('missing');
        Route::post('phrase/translate', 'translate')->name('translate');
        Route::post('phrase/import', 'import')->name('import');
    });

Route::controller(CountryChildAdminController::class)
    ->as('localize.')
    ->prefix('localize')
    ->group(function () {
        Route::prefix('country')
            ->as('country.')
            ->group(function () {
                Route::resource('child', CountryChildAdminController::class);
                Route::resource('city', CountryCityAdminController::class);
            });

        Route::prefix('language')
            ->as('language.')
            ->controller(LanguageAdminController::class)
            ->group(function () {
                Route::get('language/{id}/export-phrases', 'exportPhrases')->name('exportPhrases');
                Route::get('language/{id}/upload-csv', 'uploadCSV')->name('uploadCSV');
                Route::post('language/{id}/upload-csv', 'uploadCSVFile')->name('uploadCSVFile');
                Route::patch('language/{id}/phrases/missing', 'missingPhrases')->name('phrase.missing');
            });

        Route::prefix('phrase')
            ->controller(PhraseAdminController::class)
            ->as('phrase.')
            ->group(function () {
                Route::post('import', 'import')->name('import');
                Route::get('missing', 'missing')->name('missing');
            });

        Route::resource('currency', CurrencyAdminController::class);
        Route::resource('country', CountryAdminController::class);
        Route::resource('language', LanguageAdminController::class);
        Route::resource('phrase', PhraseAdminController::class);
    });

Route::resource('phrase', PhraseAdminController::class);
