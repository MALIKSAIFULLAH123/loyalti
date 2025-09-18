<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\User as ModelsUser;
use MetaFox\User\Support\Facades\UserEntity;

Route::get('music/add', function (Request $request) {
    return seo_sharing_view(
        'web',
        'music.music_song.create',
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
            $data->addBreadcrumb(__p('music::phrase.app_name'), sprintf('%s/%s', $ownerLink, 'music'));
        }
    );
});

Route::get('music/song/edit/{id}', function ($id) {
    return seo_sharing_view(
        'web',
        'music.music_song.edit',
        'music_song',
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
            $data->addBreadcrumb(__p('music::phrase.label_menu_s'), sprintf('%s/%s', $ownerLink, 'music'));
        }
    );
});

Route::get('music/album/edit/{id}', function ($id) {
    return seo_sharing_view(
        'web',
        'music.music_album.edit',
        'music_album',
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
            $data->addBreadcrumb(__p('music::phrase.label_menu_s'), sprintf('%s/%s', $ownerLink, 'music'));
        }
    );
});

Route::get('music/genre/{id}/genre/browse', function ($id) {
    return seo_sharing_view('admin', 'admin.music.browse_child_genre', 'music_genre', $id, function ($sharedData, $resource) {
        $sharedData->addBreadcrumb(__p('core::phrase.genres'), '/music/genre/browse');

        $parents = [];

        /**
         * @var \MetaFox\Music\Models\Genre $resource
         */
        $parent = $resource->parentCategory;

        while ($parent) {
            $parents[] = [
                'label' => $parent->name,
                'link'  => sprintf('music/genre/%s/genre/browse', $parent->entityId()),
            ];

            $parent = $parent->parentCategory;
        }

        if (count($parents)) {
            $parents = array_reverse($parents);
        }

        foreach ($parents as $parent) {
            $sharedData->addBreadcrumb($parent['label'], $parent['link']);
        }

        $sharedData->addBreadcrumb($resource->name, null);
    });
});
