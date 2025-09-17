<?php

namespace MetaFox\Advertise\Policies\Handlers;

use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanPurchaseSponsor extends BaseSponsorHandler implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if (!$this->validateCreatePermission($user)) {
            return false;
        }

        if (!$this->validateResourceStatus($resource)) {
            return false;
        }

        if (!$this->validatePermissionOnResource($user, $resource)) {
            return false;
        }

        if (!$user->hasPermissionTo(sprintf('%s.%s', $entityType, 'sponsor'))) {
            return false;
        }

        if ($user->hasPermissionTo(sprintf('%s.%s', $entityType, 'sponsor_free'))) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        if (!$this->validatePrice($user, $resource)) {
            return false;
        }

        return $this->validateValue($resource, 1);
    }

    protected function validatePrice(User $user, Content $resource): bool
    {
        $currencyId = app('currency')->getUserCurrencyId($user);

        if (!$currencyId) {
            return false;
        }

        $price = resolve(SponsorSettingServiceInterface::class)->getPriceForPayment($user, $resource, $currencyId);

        if (null === $price) {
            return false;
        }

        return true;
    }
}
