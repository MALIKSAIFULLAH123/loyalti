<?php

use Illuminate\Support\Facades\Route;

Route::get('layout/theme/{theme}/variant/browse', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.layout.browse_variant',
        'layout_theme',
        $id,
        function ($data, $theme) use ($id) {
            $label = $theme?->title;

            if (!$label) {
                $label = 'Theme #' . $id;
            }
            $data->addBreadcrumb($label, null);
        }
    );
});
