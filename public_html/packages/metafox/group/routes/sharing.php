<?php

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use MetaFox\User\Models\User as UserModels;
use MetaFox\User\Support\Facades\UserEntity;

Route::get('group/category/{id}/category/browse', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.group.browse_child_category',
        'group_category',
        $id,
        function ($data, $resource) use ($id) {
            $data->addBreadcrumb(__p('core::phrase.categories'), '/group/category/browse');
            /**
             * @var \MetaFox\Group\Models\Category $resource
             */
            $parent  = $resource?->parentCategory;
            $parents = [];

            while ($parent) {
                $parents[] = [
                    'label' => $parent->name,
                    'link'  => $parent->toSubCategoriesLink(),
                ];

                $parent = $parent->parentCategory;
            }

            if (count($parents)) {
                $parents = array_reverse($parents);
            }

            foreach ($parents as $parent) {
                $data->addBreadcrumb($parent['label'], $parent['link']);
            }

            $data->addBreadcrumb($resource?->name, null);
        }
    );
});

Route::get('group/add', function (Request $request) {
    return seo_sharing_view(
        'web',
        'group.group.create',
        null,
        null,
        function ($data, $resource) use ($request) {
            $queryParams = $request->get('queryParams') ?? [];
            $ownerId     = Arr::get($queryParams, 'owner_id') ?? 0;

            $owner = $package = null;
            try {
                $owner        = UserEntity::getById((int) $ownerId)->detail;
                $packageAlias = getAliasByEntityType($owner->entityType());
                $package      = app('core.packages')->getPackageByAlias($packageAlias);
            } catch (\Throwable $th) {
                //Silent
            }

            if (!$owner instanceof UserModels) {
                return;
            }

            if (!$package) {
                return;
            }

            $ownerLink = $owner->toLink();

            $data->addBreadcrumb($package->label, $package->internal_url);
            $data->addBreadcrumb($owner->toTitle(), $ownerLink);
            $data->addBreadcrumb(__p('group::phrase.label_menu_s'), sprintf('%s/%s', $ownerLink, 'group'));
        }
    );
});

Route::get('group/field/edit/{id}', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.group.edit_field',
        'group_custom_field',
        $id,
        function ($data, $resource) use ($id) {
            if (!$resource) {
                $resource = \MetaFox\Profile\Models\Field::query()->find($id);
            }

            $label = $resource->editingLabel;

            if (Str::match('/profile::phrase./', $label)) {
                $label = $resource->field_name;
            }

            $data->addBreadcrumb(__p('profile::phrase.manage_custom_fields'), '/group/field/browse');
            $data->addBreadcrumb($label, null);
        }
    );
});

Route::get('group/section/edit/{id}', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.group.edit_section',
        'group_custom_section',
        $id,
        function ($data, $resource) use ($id) {
            if (!$resource) {
                $resource = \MetaFox\Profile\Models\Section::query()->find($id);
            }

            $label = $resource->label;

            if (Str::match('/profile::phrase./', $label)) {
                $label = $resource->name;
            }

            $data->addBreadcrumb(__p('profile::phrase.manage_custom_sections'), '/group/section/browse');
            $data->addBreadcrumb($label, null);
        }
    );
});

Route::get('group/field/duplicate/{id}', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.group.duplicate_field',
        'group_custom_field',
        $id,
        function ($data, $resource) use ($id) {
            $data->addBreadcrumb(__p('profile::phrase.manage_custom_fields'), '/group/field/browse');
            $data->addBreadcrumb(__p('profile::phrase.duplicate_custom_field'), null);
        }
    );
});
