<?php

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\Marketplace\Models\Invoice;

Route::get('marketplace/category/{id}/category/browse', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.marketplace.browse_child_category',
        'marketplace_category',
        $id,
        function ($data, $resource) use ($id) {
            $data->addBreadcrumb(__p('core::phrase.categories'), '/marketplace/category/browse');
            /**
             * @var \MetaFox\Marketplace\Models\Category $resource
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

Route::get('marketplace/add', function (Request $request) {
    return seo_sharing_view('web', 'marketplace.marketplace.create', null, null, function ($sharingData) use ($request) {
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

            $package = app('core.packages')->getPackageByAlias($packageAlias);

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
            $sharingData->addBreadcrumb(__p('marketplace::phrase.marketplace'), sprintf('%s/%s', $owner->toLink(), 'marketplace'));
        } catch (\Throwable $exception) {
            return;
        }
    });
});

Route::get('marketplace/edit/{id}', function ($id) {
    return seo_sharing_view('web', 'marketplace.marketplace.edit', 'marketplace', $id, function ($sharingData, $resource) {
        $owner = $resource->owner;

        if (!$owner instanceof User) {
            return;
        }

        if ($resource->ownerId() == $resource->userId()) {
            return;
        }

        try {
            $packageAlias = getAliasByEntityType($owner->entityType());

            $package = app('core.packages')->getPackageByAlias($packageAlias);

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
            $sharingData->addBreadcrumb(__p('marketplace::phrase.marketplace'), sprintf('%s/%s', $owner->toLink(), 'marketplace'));
        } catch (\Throwable $exception) {
            return;
        }
    });
});

Route::get('marketplace/invoice/detail/{id}', function ($id) {
    return seo_sharing_view(
        'admin',
        'admin.marketplace.marketplace_invoice_detail',
        'marketplace_invoice',
        $id,
        function ($data, $resource) use ($id) {
            $data->addBreadcrumb(__p('marketplace::phrase.invoices'), '/marketplace/invoice/browse');

            if ($resource instanceof Invoice && null === $resource->listing) {
                $resource->load(['listing' => fn ($item) => $item->withTrashed()]);
            }

            $data->addBreadcrumb($resource?->listing?->title, null);
        }
    );
});
