<?php

namespace MetaFox\GettingStarted\Support\Traits;

use MetaFox\GettingStarted\Repositories\UserFirstLoginRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;

trait GettingStartedTrait
{
    public function isFirstLogin(User $context): bool
    {
        if (empty($context->entityId()) || empty($context->entityType())) {
            return false;
        }

        $resolution = MetaFox::getResolution();
        $isMobile   = $resolution == MetaFoxConstant::RESOLUTION_MOBILE;

        $isFirstLogin = resolve(UserFirstLoginRepositoryInterface::class)->isFirstLogin($context, $resolution);

        $pendingSubscriptions = app('events')->dispatch('subscription.invoice.has_pending', [$context, $isMobile], true);

        if ($pendingSubscriptions != null) {
            $isFirstLogin = false;
        }

        return $isFirstLogin;
    }

    public function markFirstLogin(User $context): void
    {
        $resolution = MetaFox::getResolution();

        resolve(UserFirstLoginRepositoryInterface::class)->markFirstLogin($context, $resolution);
    }
}
