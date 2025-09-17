<?php

namespace MetaFox\InAppPurchase\Listeners;

use MetaFox\InAppPurchase\Http\Resources\v1\Product\ProductDetail;
use MetaFox\InAppPurchase\Models\Product;
use MetaFox\InAppPurchase\Repositories\ProductRepositoryInterface;
use MetaFox\InAppPurchase\Support\Facades\InAppPur;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

/**
 * Class GetProductListener.
 * @ignore
 * @codeCoverageIgnore
 */
class GetProductListener
{
    /**
     * @param                     $model
     * @return ProductDetail|null
     */
    public function handle($model, User $context)
    {
        if (!$model instanceof Entity) {
            return null;
        }
        $itemId   = $model->entityId();
        $itemType = $model->entityType();

        if (!InAppPur::getProductTypeByValue($itemType)) {
            return null;
        }
        $service = Payment::getManager()->getGatewayServiceByName('in-app-purchase');

        if (!$service->hasAccess($context, [
            'entity_type' => $model->entityType(),
            'entity_id'   => $model->entityId(),
        ])) {
            return null;
        }

        $product = $this->getProductRepository()->getProductByItem($itemId, $itemType);

        if (!$product instanceof Product) {
            return null;
        }

        return new ProductDetail($product);
    }

    public function getProductRepository(): ProductRepositoryInterface
    {
        return resolve(ProductRepositoryInterface::class);
    }
}
