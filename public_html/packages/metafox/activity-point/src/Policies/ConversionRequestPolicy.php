<?php

namespace MetaFox\ActivityPoint\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use MetaFox\ActivityPoint\Models\ConversionRequest;
use MetaFox\ActivityPoint\Support\Facade\PointConversion;
use MetaFox\ActivityPoint\Support\PointConversion as Support;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;

/**
 * stub: /packages/policies/model_policy.stub
 */

/**
 * Class ConversionRequestPolicy.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ConversionRequestPolicy implements ResourcePolicyInterface
{
    use HandlesAuthorization;

    protected string $type = 'activitypoint_conversion_request';

    public function viewAny(User $user, ?User $owner = null): bool
    {
        return $this->viewOwner($user, $owner);
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        if (null === $owner) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($user->entityId() === $owner->entityId()) {
            return true;
        }

        return false;
    }

    public function view(User $user, Entity $resource): bool
    {
        return $this->viewOwner($user, $resource->user);
    }

    public function enoughPointsForCreating(User $user, int $points): bool
    {
        if ($points <= 0) {
            return false;
        }

        $currentPoints = PointConversion::getAvailableUserPoints($user);

        if ($currentPoints <= 0) {
            return false;
        }

        if ($currentPoints < $points) {
            return false;
        }

        return true;
    }

    public function conversionRateForCreate(string $currency = Support::DEFAULT_CONVERSION_RATE_CURRENCY_TO_MONEY): bool
    {
        $rate = (float) Settings::get('activitypoint.conversion_rate.' . $currency, 0);

        if ($rate <= 0) {
            return false;
        }

        return true;
    }

    public function createForm(User $user): bool
    {
        $currency = app('currency')->getDefaultCurrencyId();
        if (!$this->conversionRateForCreate($currency)) {
            return false;
        }

        $currentPoints      = PointConversion::getAvailableUserPoints($user);
        $min                = PointConversion::getMinPointsCanCreate($user);
        $restPointsPerDay   = PointConversion::getRestPointsPerDay($user);
        $restPointsPerMonth = PointConversion::getRestPointsPerMonth($user);

        if ($currentPoints <= 0) {
            return false;
        }

        if ($currentPoints < $min) {
            return false;
        }

        if (null === $restPointsPerDay && null === $restPointsPerMonth) {
            return true;
        }

        if (0 === $restPointsPerDay) {
            return false;
        }

        if (0 === $restPointsPerMonth) {
            return false;
        }

        return true;
    }

    public function createConversionRequest(User $user, int $points): bool
    {
        if (!$user->hasPermissionTo('activitypoint_conversion_request.create')) {
            return false;
        }

        $currency = app('currency')->getDefaultCurrencyId();
        if (!$this->conversionRateForCreate($currency)) {
            return false;
        }

        if ($points <= 0) {
            return false;
        }

        $currentPoints = PointConversion::getAvailableUserPoints($user);
        $min           = PointConversion::getMinPointsCanCreate($user);
        $max           = PointConversion::getMaxPointsCanCreate($user);

        if ($points < $min) {
            return false;
        }

        if ($points > $max) {
            return false;
        }

        if ($points > $currentPoints) {
            return false;
        }

        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        return false;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        return false;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        return false;
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        return false;
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        return false;
    }

    public function approveConversionRequest(User $user, ConversionRequest $transaction): bool
    {
        if (!$transaction->is_pending) {
            return false;
        }

        if (!$transaction->user instanceof User || $transaction->user->isDeleted()) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        return false;
    }

    public function denyConversionRequest(User $user, ConversionRequest $transaction): bool
    {
        if (!$transaction->is_pending) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        return false;
    }

    public function cancelConversionRequest(User $user, ConversionRequest $transaction): bool
    {
        if (!$transaction->is_pending) {
            return false;
        }

        if ($user->entityId() == $transaction->userId()) {
            return true;
        }

        return false;
    }

    public function viewDeniedReason(User $user, ConversionRequest $transaction): bool
    {
        if (!$transaction->is_denied) {
            return false;
        }

        if (null === $transaction->denied_reason) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($user->entityId() == $transaction->userId()) {
            return true;
        }

        return false;
    }
}
