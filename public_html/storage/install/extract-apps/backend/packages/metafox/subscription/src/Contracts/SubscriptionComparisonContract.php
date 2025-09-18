<?php

namespace MetaFox\Subscription\Contracts;

use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;

interface SubscriptionComparisonContract
{
    /**
     * @param  User            $context
     * @return Collection|null
     */
    public function getComparisons(User $context): ?Collection;

    /**
     * @param  User $context
     * @return bool
     */
    public function hasComparisons(User $context): bool;
}
