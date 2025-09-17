<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\Platform\Contracts\User;

Route::get('forum/forum/{id}/forum/browse', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.forum.browse_child_forum',
        'forum',
        $id,
        function ($data, $resource) use ($id) {
            $data->addBreadcrumb(__p('forum::phrase.browse_forum'), '/forum/forum/browse');

            $parent  = $resource?->parentForums;
            $parents = [];

            while ($parent) {
                $parents[] = [
                    'label' => $parent->title,
                    'link'  => $parent->toSubForumLink(),
                ];

                $parent = $parent->parentForums;
            }

            if (count($parents)) {
                $parents = array_reverse($parents);
            }

            foreach ($parents as $parent) {
                $data->addBreadcrumb($parent['label'], $parent['link']);
            }

            $label  = $resource->toTitle();

            $data->addBreadcrumb($label, null);
        }
    );
});

Route::get('forum/thread/add', function (Request $request) {
    return seo_sharing_view('web', 'forum.forum_thread.create', null, null, function ($sharingData) use ($request) {
        $params = $request->get('queryParams');

        if (!is_array($params)) {
            return;
        }

        $ownerId = Arr::get($params, 'owner_id');

        if (!$ownerId) {
            return;
        }

        $owner = UserEntity::getById($ownerId)?->detail;

        if (!$owner instanceof User) {
            return;
        }

        try {
            $packageAlias = getAliasByEntityType($owner->entityType());

            $package      = app('core.packages')->getPackageByAlias($packageAlias);

            if (null === $package) {
                return;
            }

            if (!$package->is_installed) {
                return;
            }

            if (!$package->is_active) {
                return;
            }

            /*
             * @var SeoMetaData $sharingData
             */
            $sharingData->addBreadcrumb($package->label, $package->internal_url);
            $sharingData->addBreadcrumb($owner->toTitle(), $owner->toLink());
            $sharingData->addBreadcrumb(__p('forum::phrase.forums'), sprintf('%s/%s', $owner->toLink(), 'forum'));
        } catch (\Throwable $exception) {
            return;
        }
    });
});

Route::get('forum/thread/edit/{id}', function ($id) {
    return seo_sharing_view('web', 'forum.forum_thread.edit', 'forum_thread', $id, function ($sharingData, $resource) {
        $owner = $resource->owner;

        if (!$owner instanceof User) {
            return;
        }

        if ($resource->ownerId() == $resource->userId()) {
            return;
        }

        try {
            $packageAlias = getAliasByEntityType($owner->entityType());

            $package      = app('core.packages')->getPackageByAlias($packageAlias);

            if (null === $package) {
                return;
            }

            if (!$package->is_installed) {
                return;
            }

            if (!$package->is_active) {
                return;
            }

            /*
             * @var SeoMetaData $sharingData
             */
            $sharingData->addBreadcrumb($package->label, $package->internal_url);
            $sharingData->addBreadcrumb($owner->toTitle(), $owner->toLink());
            $sharingData->addBreadcrumb(__p('forum::phrase.forums'), sprintf('%s/%s', $owner->toLink(), 'forum'));
        } catch (\Throwable $exception) {
            return;
        }
    });
});
