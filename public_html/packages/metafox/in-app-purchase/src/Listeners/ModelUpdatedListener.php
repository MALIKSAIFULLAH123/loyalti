<?php

namespace MetaFox\InAppPurchase\Listeners;

use MetaFox\InAppPurchase\Repositories\ProductRepositoryInterface;
use MetaFox\InAppPurchase\Support\Facades\InAppPur;
use MetaFox\Platform\Contracts\Entity;

/**
 * Class ModelUpdatedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class ModelUpdatedListener
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

        $this->getProductRepository()->updateProductByItem($model->entityId(), $model->entityType(), $model);
    }

    public function getProductRepository(): ProductRepositoryInterface
    {
        return resolve(ProductRepositoryInterface::class);
    }
}
