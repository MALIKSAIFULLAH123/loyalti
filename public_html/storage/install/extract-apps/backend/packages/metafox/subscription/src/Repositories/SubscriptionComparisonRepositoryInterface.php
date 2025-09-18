<?php

namespace MetaFox\Subscription\Repositories;

use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Models\SubscriptionComparison;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface SubscriptionComparison.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface SubscriptionComparisonRepositoryInterface
{
    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return SubscriptionComparison
     */
    public function createComparison(User $context, array $attributes): SubscriptionComparison;

    /**
     * @param User  $context
     * @param int   $id
     * @param array $attributes
     *
     * @return SubscriptionComparison
     */
    public function updateComparison(User $context, int $id, array $attributes): SubscriptionComparison;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return bool
     */
    public function deleteComparison(User $context, int $id): bool;

    /**
     * @param array $cacheIds
     *
     * @return void
     */
    public function clearCaches(array $cacheIds = []): void;

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return Collection
     */
    public function viewComparisons(User $context, array $attributes = []): Collection;

    /**
     * @param User $context
     *
     * @return bool
     */
    public function hasComparisons(User $context): bool;

    /**
     * @param User  $context
     * @param array $orders
     */
    public function ordering(User $context, array $orders): void;
}
