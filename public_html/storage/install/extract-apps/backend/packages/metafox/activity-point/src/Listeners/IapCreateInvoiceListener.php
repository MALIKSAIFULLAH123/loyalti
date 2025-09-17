<?php

namespace MetaFox\ActivityPoint\Listeners;

use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\PointPackage;
use MetaFox\ActivityPoint\Repositories\PointPackageRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class IapCreateInvoiceListener
{
    public function handle(string $itemType, User $context, array $params): ?array
    {
        if ($itemType != PointPackage::ENTITY_TYPE) {
            return null;
        }
        $id = Arr::get($params, 'id');

        if (!$id) {
            return null;
        }

        return $this->pointPackageRepository()->purchasePackage($context, $id, $params);
    }

    public function pointPackageRepository(): PointPackageRepositoryInterface
    {
        return resolve(PointPackageRepositoryInterface::class);
    }
}
