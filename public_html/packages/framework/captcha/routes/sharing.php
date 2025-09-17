<?php

use Illuminate\Support\Facades\Route;

Route::get('captcha/type/edit/{name}', function ($name) {
    return seo_sharing_view(
        'admin',
        'admin.captcha.edit_type',
        null,
        null,
        function ($data) use ($name) {
            $data->addBreadcrumb(__p('captcha::admin.captcha_types'), '/captcha/type/browse');
            $data->addBreadcrumb($name, null);
        }
    );
});
