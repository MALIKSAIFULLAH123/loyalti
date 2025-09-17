<?php

namespace MetaFox\Subscription\Contracts;

use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Models\SubscriptionCancelReason as Model;

interface SubscriptionCancelReasonContract
{
    /**
     * @param  User  $context
     * @return array
     */
    public function getActiveOptions(User $context): array;

    /**
     * @param  User $context
     * @return bool
     */
    public function hasActiveReasons(User $context): bool;

    /**
     * @return Model|null
     */
    public function getDefaultReason(): ?Model;

    /**
     * @return void
     */
    public function clearCaches(): void;
}
