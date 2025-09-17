<?php

use Illuminate\Support\Facades\Route;

Route::get('storage/option/edit/{driver}/{name}', function ($name) {
    return seo_sharing_view(
        'admin',
        'admin.storage.edit_config',
        null,
        null,
        function ($data) use ($name) {
            $data->addBreadcrumb(__p('storage::phrase.configurations'), '/storage/option/browse');
            $data->addBreadcrumb($name, null);
        }
    );
});
