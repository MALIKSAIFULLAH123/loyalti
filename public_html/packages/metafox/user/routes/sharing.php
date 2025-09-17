<?php

use Illuminate\Support\Facades\Route;

Route::get('user/{id}', function ($id) {
    return seo_sharing_view('web', 'user.user.landing', 'user', $id);
});

Route::get('user', function () {
    return seo_sharing_view('web', 'user.user.landing');
});

Route::get('user/user/edit/{id}', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.user.edit_user',
        'user',
        $id,
        function ($data, $user) use ($id) {
            $label = $user?->full_name;

            if (!$label) {
                $label = $user?->user_name ?? 'User #' . $id;
            }

            $data->addBreadcrumb(__p('user::admin.manage_members'), '/user/user/browse');
            $data->addBreadcrumb($label, null);
        }
    );
});

Route::get('user/inactive-process/create', function () {
    return seo_sharing_view(
        'admin',
        'admin.user.create_inactive_process',
        null,
        null,
        function ($data) {
            $data->addBreadcrumb(__p('user::phrase.inactive_user'), '/user/inactive/browse');
            $data->addBreadcrumb(__p('user::phrase.mail_inactive_members'), null);
        }
    );
});

Route::get('user/inactive-process/browse', function () {
    return seo_sharing_view(
        'admin',
        'admin.user.browse_inactive_process',
        null,
        null,
        function ($data) {
            $data->addBreadcrumb(__p('user::phrase.inactive_user'), '/user/inactive/browse');
            $data->addBreadcrumb(__p('user::phrase.manage_mailing_processes'), null);
        }
    );
});
