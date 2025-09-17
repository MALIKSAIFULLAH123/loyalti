<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\User as ModelsUser;
use MetaFox\User\Support\Facades\UserEntity;

Route::get('photo/category/{id}/category/browse', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.photo.browse_child_category',
        'photo_category',
        $id,
        function ($data, $resource) use ($id) {
            $data->addBreadcrumb(__p('core::phrase.categories'), '/photo/category/browse');
            /**
             * @var \MetaFox\Photo\Models\Category $resource
             */
            $parent  = $resource?->parentCategory;
            $parents = [];

            while ($parent) {
                $parents[] = [
                    'label' => $parent->name,
                    'link'  => $parent->admin_browse_url,
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

Route::get('photo/add', function (Request $request) {
    return seo_sharing_view(
        'web',
        'photo.photo.create',
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
            $data->addBreadcrumb(__p('photo::phrase.label_menu_s'), sprintf('%s/%s', $ownerLink, 'photo'));
        }
    );
});

Route::get('photo/edit/{id}', function ($id) {
    return seo_sharing_view(
        'web',
        'photo.photo.edit',
        'photo',
        $id,
        function ($data, $resource) {
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
            $data->addBreadcrumb(__p('photo::phrase.label_menu_s'), sprintf('%s/%s', $ownerLink, 'photo'));
        }
    );
});

Route::get('photo/album/add', function (Request $request) {
    return seo_sharing_view(
        'web',
        'photo.photo_album.create',
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
            $data->addBreadcrumb(__p('photo::phrase.label_menu_s'), sprintf('%s/%s', $ownerLink, 'photo?stab=albums'));
        }
    );
});

Route::get('photo/album/edit/{id}', function ($id) {
    return seo_sharing_view(
        'web',
        'photo.photo_album.edit',
        'photo_album',
        $id,
        function ($data, $resource) {
            if (!$resource instanceof \MetaFox\Photo\Models\Album) {
                return;
            }

            $seoData = $resource->getAlbumDetailSeoData('web');
            foreach ($seoData as $key => $value) {
                $data->offsetSet($key, $value);
            }
        }
    );
});

Route::get('media/album/{albumId}/photo/{id}/{slug?}', function ($albumId, $id) {
    return seo_sharing_view(
        'web',
        'photo.photo_album.view_item_preview',
        'photo',
        $id,
        null,
    );
});
