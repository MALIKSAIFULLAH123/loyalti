<?php

use Illuminate\Support\Facades\Route;
use MetaFox\Localize\Models\CountryChild;

Route::get('localize/country/{id}/state/browse', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.localize.browse_state',
        'country',
        $id,
        function ($data, $country) {
            $data->addBreadcrumb('Countries', '/localize/country/browse');
            $data->addBreadcrumb($country?->name, null);
        }
    );
});

Route::get('localize/country/{country}/state/{country_child}/city/browse', function ($country, $state) {
    return seo_sharing_view(
        'admin',
        'admin.localize.browse_city',
        'country',
        $country,
        function ($data, $country) use ($state) {
            $data->addBreadcrumb('Countries', '/localize/country/browse');
            $data->addBreadcrumb($country?->name, "localize/country/{$country?->id}/state/browse");
            $stateObj = CountryChild::query()->find($state);
            $data->addBreadcrumb($stateObj?->name, null);
        }
    );
});
