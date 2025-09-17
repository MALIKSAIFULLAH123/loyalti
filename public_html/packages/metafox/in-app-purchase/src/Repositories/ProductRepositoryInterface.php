<?php

namespace MetaFox\InAppPurchase\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\InAppPurchase\Models\Product;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User as ContractUser;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Product.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ProductRepositoryInterface
{
    public function initProducts(): void;

    /**
     * @param  int     $id
     * @param  array   $attributes
     * @return Product
     */
    public function updateProduct(int $id, array $attributes): Product;

    /**
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     */
    public function viewProducts(array $attributes): Paginator;

    /**
     * @param  Entity  $model
     * @return Product
     */
    public function addProduct(Entity $model): Product;

    /**
     * @param  int    $itemId
     * @param  string $itemType
     * @return int
     */
    public function deleteProduct(int $itemId, string $itemType): int;

    /**
     * @param  int          $itemId
     * @param  string       $itemType
     * @param  Entity       $model
     * @return Product|null
     */
    public function updateProductByItem(int $itemId, string $itemType, Entity $model): ?Product;

    /**
     * @param  int          $itemId
     * @param  string       $itemType
     * @return Product|null
     */
    public function getProductByItem(int $itemId, string $itemType): ?Product;

    /**
     * @param  string       $id
     * @param  string       $platform
     * @return Product|null
     */
    public function getProductByStoreId(string $id, string $platform): ?Product;
}
