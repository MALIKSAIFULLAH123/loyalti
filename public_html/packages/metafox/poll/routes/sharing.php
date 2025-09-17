<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use MetaFox\User\Support\Facades\UserEntity;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\Content;
use MetaFox\User\Models\User as ModelsUser;

Route::get('poll/add', function (Request $request) {
    return seo_sharing_view(
        'web',
        'poll.poll.create',
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
            $data->addBreadcrumb(__p('poll::phrase.label_menu_s'), sprintf('%s/%s', $ownerLink, 'poll'));
        }
    );
});

Route::get('poll/edit/{id}', function ($id) {
    return seo_sharing_view(
        'web',
        'poll.poll.edit',
        'poll',
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
            $data->addBreadcrumb(__p('poll::phrase.label_menu_s'), sprintf('%s/%s', $ownerLink, 'poll'));
        }
    );
});
