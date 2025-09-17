<?php

namespace MetaFox\EMoney\Http\Resources\v1\UserBalance\Admin;

use MetaFox\EMoney\Services\Contracts\UserBalanceServiceInterface;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Http\Resources\v1\User\Admin\UserItem;
use MetaFox\User\Models\User as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class UserBalanceItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class UserBalanceItem extends UserItem
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'ewallet_balance' => resolve(UserBalanceServiceInterface::class)->getUserBalances($this->resource),
        ]);
    }
}
