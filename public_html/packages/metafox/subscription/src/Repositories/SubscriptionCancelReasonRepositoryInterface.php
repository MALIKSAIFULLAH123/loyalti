<?php

namespace MetaFox\Subscription\Repositories;

use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Models\SubscriptionCancelReason;
use MetaFox\Subscription\Models\SubscriptionUserCancelReason;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface SubscriptionCancelReason.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface SubscriptionCancelReasonRepositoryInterface
{
    /**
     * @param  User                     $context
     * @param  array                    $attributes
     * @return SubscriptionCancelReason
     */
    public function createReason(User $context, array $attributes): SubscriptionCancelReason;

    /**
     * @param  User                     $context
     * @param  int                      $id
     * @param  array                    $attributes
     * @return SubscriptionCancelReason
     */
    public function updateReason(User $context, int $id, array $attributes): SubscriptionCancelReason;

    /**
     * @param  User  $context
     * @param  int   $id
     * @param  array $attributes
     * @return bool
     */
    public function deleteReason(User $context, int $id, array $attributes): bool;

    /**
     * @param  User $context
     * @param  int  $id
     * @param  bool $isActive
     * @return bool
     */
    public function activeReason(User $context, int $id, bool $isActive): bool;

    /**
     * @param  User  $context
     * @return array
     */
    public function getCustomReasonOptions(User $context): array;

    /**
     * @return void
     */
    public function clearCaches(): void;

    /**
     * @param  User            $context
     * @return Collection|null
     */
    public function viewActiveReasons(User $context): ?Collection;

    /**
     * @param  User            $context
     * @param  array           $attributes
     * @return Collection|null
     */
    public function viewReasons(User $context, array $attributes): ?Collection;

    /**
     * @param  User                              $context
     * @param  int                               $invoiceId
     * @param  int|null                          $reasonId
     * @return SubscriptionUserCancelReason|null
     */
    public function createUserCancelReason(User $context, int $invoiceId, ?int $reasonId = null): ?SubscriptionUserCancelReason;

    /**
     * @param  int                               $invoiceId
     * @param  int|null                          $reasonId
     * @return SubscriptionUserCancelReason|null
     */
    public function createCancelReason(int $invoiceId, ?int $reasonId = null): ?SubscriptionUserCancelReason;

    /**
     * @return SubscriptionCancelReason
     */
    public function getDefaultReason(): ?SubscriptionCancelReason;

    /**
     * @param  User  $context
     * @param  array $ids
     * @return bool
     */
    public function order(User $context, array $ids): bool;

    /**
     * @return array
     */
    public function getReasonOptions(): array;
}
