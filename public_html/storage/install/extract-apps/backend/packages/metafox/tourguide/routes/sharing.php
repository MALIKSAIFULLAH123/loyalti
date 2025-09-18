<?php

use Illuminate\Support\Facades\Route;

Route::get('tourguide/tour-guide/{id}/step/browse', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.tourguide.tour_guide_detail',
        'tour_guide',
        $id,
        function ($data, $resource) use ($id) {
            $data->addBreadcrumb(__p('tourguide::phrase.tour_guide'), '/tourguide/tour-guide/browse');
            $data->addBreadcrumb($resource?->name, null);
        }
    );
});
