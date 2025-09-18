<?php

namespace MetaFox\Subscription\Repositories;

use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Models\SubscriptionPendingRegistrationUser;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface SubscriptionPendingRegistrationUser.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface SubscriptionPendingRegistrationUserRepositoryInterface
{
    /**
     * @param  User                                $context
     * @param  int                                 $invoiceId
     * @return SubscriptionPendingRegistrationUser
     */
    public function createPendingRegistrationUser(User $context, int $invoiceId): SubscriptionPendingRegistrationUser;

    /**
     * @param  User     $context
     * @param  int|null $invoiceId
     * @return bool
     */
    public function deletePendingRegistrationUser(User $context, ?int $invoiceId = null): bool;

    /**
     * @param  User                                     $context
     * @param  ?int                                     $invoiceId
     * @return SubscriptionPendingRegistrationUser|null
     */
    public function getPendingRegistrationUser(User $context, ?int $invoiceId = null): ?SubscriptionPendingRegistrationUser;
}
