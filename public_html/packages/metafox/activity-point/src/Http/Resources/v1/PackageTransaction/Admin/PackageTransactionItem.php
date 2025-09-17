<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PackageTransaction\Admin;

use Illuminate\Http\Request;
use MetaFox\ActivityPoint\Http\Resources\v1\PackageTransaction\PackageTransactionItem as PackageTransactionItemUser;
use MetaFox\ActivityPoint\Models\PackagePurchase as Model;


/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PackageTransactionItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PackageTransactionItem extends PackageTransactionItemUser
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
