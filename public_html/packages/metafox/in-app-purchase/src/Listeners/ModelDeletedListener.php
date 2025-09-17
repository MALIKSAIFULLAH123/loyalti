<?php

namespace MetaFox\InAppPurchase\Listeners;

use MetaFox\InAppPurchase\Repositories\ProductRepositoryInterface;
use MetaFox\InAppPurchase\Support\Facades\InAppPur;
use MetaFox\Platform\Contracts\Entity;

/**
 * Class ModelDeletedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class ModelDeletedListener
{
    /**
     * @param       $model
     * @return void
     */
    public function handle($model)
    {
        if (!$model instanceof Entity) {
            return;
        }
        $entityType = $model->entityType();

        if (!InAppPur::getProductTypeByValue($entityType)) {
            return;
        }
        $this->getProductRepository()->deleteProduct($model->entityId(), $model->entityType());
    }

    public function getProductRepository(): ProductRepositoryInterface
    {
        return resolve(ProductRepositoryInterface::class);
    }
}
