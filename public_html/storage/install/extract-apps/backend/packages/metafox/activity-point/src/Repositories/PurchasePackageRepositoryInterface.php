<?php

namespace MetaFox\ActivityPoint\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use MetaFox\ActivityPoint\Models\PackagePurchase as Model;
use MetaFox\Platform\Contracts\User;

/**
 * Interface PurchasePackageRepositoryInterface.
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 */
interface PurchasePackageRepositoryInterface
{
    public function viewPurchasePackageAdminCP(User $context, array $attributes = []): Paginator;

    /**
     * @param User  $context
     * @param array $attributes
     * @return Builder
     */
    public function viewTransactions(User $context, array $attributes): Builder;

    /**
     * @param User  $context
     * @param int   $id
     * @param int   $gatewayId
     * @param array $extra
     * @return array
     */
    public function payInvoice(User $context, int $id, int $gatewayId, array $extra = []): array;
}
