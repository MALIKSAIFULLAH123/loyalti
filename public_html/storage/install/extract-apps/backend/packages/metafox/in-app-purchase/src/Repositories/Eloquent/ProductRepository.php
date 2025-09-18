<?php

namespace MetaFox\InAppPurchase\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use MetaFox\InAppPurchase\Models\Product;
use MetaFox\InAppPurchase\Repositories\ProductRepositoryInterface;
use MetaFox\InAppPurchase\Support\Constants;
use MetaFox\InAppPurchase\Support\Facades\InAppPur;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTitle;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class ProductRepository.
 * @method Product getModel()
 */
class ProductRepository extends AbstractRepository implements ProductRepositoryInterface
{
    public function model()
    {
        return Product::class;
    }

    public function initProducts(): void
    {
        $allProductTypes = InAppPur::getProductTypes(false);

        foreach ($allProductTypes as $productType) {
            $modelClass = Relation::getMorphedModel($productType['value']);
            if (!$modelClass) {
                continue;
            }
            $model = resolve($modelClass);
            if (!$model instanceof Model) {
                continue;
            }
            $products = $model->newModelQuery()->get()->collect();
            $products->each(function ($product) {
                if (!$product instanceof Entity) {
                    return;
                }
                $price  = $product->price ?? [];
                $update = [
                    'item_id'      => $product->entityId(),
                    'item_type'    => $product->entityType(),
                    'price'        => is_array($price) ? json_encode($price) : $price,
                    'is_recurring' => $product->is_recurring ?? 0,
                    'title'        => $product instanceof HasTitle ? $product->toTitle() : $product->entityType() . '#' . $product->entityId(),
                ];
                Product::query()->upsert($update, ['item_id', 'item_type'], ['title', 'price']);
            });
        }
    }

    public function updateProduct(int $id, array $attributes): Product
    {
        $product = $this->find($id);

        if (!$product instanceof Product) {
            throw new ModelNotFoundException();
        }
        $product->fill($attributes);

        $product->save();

        $product->refresh();

        return $product;
    }

    public function viewProducts(array $attributes): Paginator
    {
        $query = $this->buildQueryViewProduct($attributes);

        return $query->orderBy('created_at', 'DESC')->paginate($attributes['limit'] ?? 100);
    }

    private function buildQueryViewProduct(array $attributes): ?Builder
    {
        $search = Arr::get($attributes, 'q');
        $type   = Arr::get($attributes, 'item_type');

        $query = $this->getModel()->newModelQuery();

        if ($search) {
            $searchScope = new SearchScope($search, ['title']);
            $query       = $query->addScope($searchScope);
        }

        match ($type) {
            null    => $query->whereIn('iap_products.item_type', array_column(InAppPur::getProductTypes(), 'value')),
            default => $query->where('iap_products.item_type', $type)
        };

        return $query;
    }

    public function addProduct(Entity $model): Product
    {
        $price   = $model->price ?? [];
        $product = new Product([
            'title'     => $model instanceof HasTitle ? $model->toTitle() : $model->entityType() . '#' . $model->entityId(),
            'item_id'   => $model->entityId(),
            'price'     => is_array($price) ? json_encode($price) : $price,
            'item_type' => $model->entityType(),
        ]);

        $product->save();

        return $product;
    }

    public function deleteProduct(int $itemId, string $itemType): int
    {
        return $this->getModel()->newModelQuery()
            ->where([
                'item_id'   => $itemId,
                'item_type' => $itemType,
            ])->delete();
    }

    public function updateProductByItem(int $itemId, string $itemType, Entity $model): ?Product
    {
        $product = $this->getModel()->newModelQuery()
            ->where([
                'item_id'   => $itemId,
                'item_type' => $itemType,
            ])->first();
        if (!$product instanceof Product) {
            return null;
        }
        $price = $model->price ?? [];
        $product->fill([
            'price'        => is_array($price) ? json_encode($price) : $price,
            'is_recurring' => $model->is_recurring ?? 0,
            'title'        => $model instanceof HasTitle ? $model->toTitle() : $model->entityType() . '#' . $model->entityId(),
        ]);

        $product->save();
        $product->refresh();

        return $product;
    }

    public function getProductByItem(int $itemId, string $itemType): ?Product
    {
        return $this->getModel()->newModelQuery()
            ->where([
                'item_id'   => $itemId,
                'item_type' => $itemType,
            ])->first();
    }

    public function getProductByStoreId(string $id, string $platform): ?Product
    {
        $key = match ($platform) {
            Constants::IOS     => 'ios_product_id',
            Constants::ANDROID => 'android_product_id'
        };

        return $this->getModel()->newModelQuery()
            ->where([
                $key => $id,
            ])->first();
    }
}
