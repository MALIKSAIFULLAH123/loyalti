<?php
namespace MetaFox\Featured\Traits\Item;

use MetaFox\Featured\Models\Item;
use MetaFox\Featured\Policies\ItemPolicy;

/**
 * @property Item $resource
 */
trait ExtraTrait
{
    public function getExtra(): array
    {
        /**
         * @var ItemPolicy $policy
         */
        $policy = resolve(ItemPolicy::class);

        $context = user();

        return [
            'can_payment' => $policy->payment($context, $this->resource),
            'can_cancel'  => $policy->cancel($context, $this->resource),
            'can_delete'  => $policy->delete($context, $this->resource),
        ];
    }
}
