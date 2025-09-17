<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\Section;

Route::get('profile/field/edit/{id}', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.profile.edit_field',
        'user_custom_field',
        $id,
        function ($data, $resource) use ($id) {
            if (!$resource) {
                $resource = Field::query()->find($id);
            }

            $label = $resource->editingLabel;

            if (Str::match('/profile::phrase./', $label)) {
                $label = $resource->field_name;
            }

            $data->addBreadcrumb(__p('profile::phrase.manage_custom_fields'), '/profile/field/browse');
            $data->addBreadcrumb($label, null);
        }
    );
});

Route::get('profile/section/edit/{id}', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.profile.edit_section',
        'user_custom_section',
        $id,
        function ($data, $resource) use ($id) {
            if (!$resource) {
                $resource = Section::query()->find($id);
            }

            $label = $resource->label;

            if (Str::match('/profile::phrase./', $label)) {
                $label = $resource->name;
            }

            $data->addBreadcrumb(__p('profile::phrase.manage_custom_sections'), '/profile/section/browse');
            $data->addBreadcrumb($label, null);
        }
    );
});

Route::get('profile/field/duplicate/{id}', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.profile.duplicate_field',
        'user_custom_field',
        $id,
        function ($data, $resource) use ($id) {
            $data->addBreadcrumb(__p('profile::phrase.manage_custom_fields'), '/profile/field/browse');
            $data->addBreadcrumb(__p('profile::phrase.duplicate_custom_field'), null);
        }
    );
});
