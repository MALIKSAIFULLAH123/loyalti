<?php

namespace MetaFox\Subscription\Support;

use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Contracts\SubscriptionComparisonContract;
use MetaFox\Subscription\Repositories\SubscriptionComparisonRepositoryInterface;

class SubscriptionComparison implements SubscriptionComparisonContract
{
    /**
     * @var SubscriptionComparisonRepositoryInterface
     */
    protected $repository;

    public function __construct(SubscriptionComparisonRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getComparisons(User $context): ?Collection
    {
        return $this->repository->viewComparisons($context, [
            'view' => Helper::VIEW_ADMINCP,
        ]);
    }

    public function hasComparisons(User $context): bool
    {
        return $this->repository->hasComparisons($context);
    }
}
