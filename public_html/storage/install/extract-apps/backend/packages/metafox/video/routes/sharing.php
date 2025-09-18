<?php

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\User as ModelsUser;
use MetaFox\User\Support\Facades\UserEntity;

Route::get('video/category/{id}/category/browse', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.video.browse_child_category',
        'video_category',
        $id,
        function ($data, $resource) use ($id) {
            $data->addBreadcrumb(__p('core::phrase.categories'), '/video/category/browse');
            /**
             * @var \MetaFox\Video\Models\Category $resource
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

Route::get('video/share', function (Request $request) {
    return seo_sharing_view(
        'web',
        'video.video.create',
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

            if (!$owner instanceof User) {
                return;
            }

            if (!$package) {
                return;
            }

            $ownerLink = $owner->toLink();

            $data->addBreadcrumb($package->label, $package->internal_url);
            $data->addBreadcrumb($owner->toTitle(), $ownerLink);
            $data->addBreadcrumb(__p('video::phrase.label_menu_s'), sprintf('%s/%s', $ownerLink, 'video'));
        }
    );
});

Route::get('video/edit/{id}', function ($id) {
    return seo_sharing_view(
        'web',
        'video.video.edit',
        'video',
        $id,
        function ($data, $resource) use ($id) {
            if (!$resource instanceof Content) {
                return;
            }

            $owner = $resource->owner;

            if (!$owner instanceof User) {
                return;
            }

            if ($owner instanceof ModelsUser) {
                return;
            }

            $packageAlias = getAliasByEntityType($owner->entityType());
            $package      = app('core.packages')->getPackageByAlias($packageAlias);

            if (!$package) {
                return;
            }
            $ownerLink = $owner->toLink();

            $data->addBreadcrumb($package->label, $package->internal_url);
            $data->addBreadcrumb($owner->toTitle(), $ownerLink);
            $data->addBreadcrumb(__p('video::phrase.label_menu_s'), sprintf('%s/%s', $ownerLink, 'video'));
        }
    );
});

Route::get('media/album/{albumId}/video/{id}/{slug?}', function ($albumId, $id) {
    return seo_sharing_view(
        'web',
        'video.video_album.view_item_preview',
        'video',
        $id,
        null,
    );
});
