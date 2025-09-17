<?php

namespace MetaFox\EMoney\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Http\JsonResponse;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Services\Contracts\WithdrawServiceInterface;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;

/**
 * stub: /packages/policies/model_policy.stub.
 */

/**
 * Class WithdrawRequestPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class WithdrawRequestPolicy
{
    public function create(User $user, string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE, ?float $amount = null): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        if (!$this->validateAmount($user, $currency, $amount)) {
            return false;
        }

        if (!$this->validateMethod($user, $currency)) {
            return false;
        }

        return true;
    }

    public function validateAmount(User $user, string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE, ?float $amount = null): bool
    {
        return resolve(WithdrawServiceInterface::class)->validateWithdrawRequest($user, $currency, $amount);
    }

    public function validateMethod(User $user, string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): bool
    {
        $availableMethods = resolve(WithdrawServiceInterface::class)->getAvailableMethodsForUser($user, $currency);

        if (!count($availableMethods)) {
            return false;
        }

        return true;
    }

    public function cancel(User $user, WithdrawRequest $request): bool
    {
        if (!$request->is_pending) {
            return false;
        }

        if ($user->entityId() == $request->userId()) {
            return true;
        }

        return false;
    }

    public function deny(User $user, WithdrawRequest $request): bool
    {
        if (!$request->is_pending) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        return false;
    }

    public function approve(User $user, WithdrawRequest $request): bool
    {
        if (null === $request->user) {
            return false;
        }

        if (!$request->is_pending) {
            return false;
        }

        if ($request->amount <= 0) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        return false;
    }

    public function payment(User $user, WithdrawRequest $request): bool
    {
        if (null === $request->user) {
            return false;
        }

        if (!$request->is_processing) {
            return false;
        }

        if ($request->amount <= 0) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        return false;
    }

    public function viewReason(User $user, WithdrawRequest $request): bool
    {
        if (!$request->is_denied) {
            return false;
        }

        if (null === $request->reason?->message) {
            return false;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($user->entityId() == $request->userId()) {
            return true;
        }

        return false;
    }

    public function view(User $user, WithdrawRequest $request): bool
    {
        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($user->entityId() == $request->userId()) {
            return true;
        }

        return false;
    }
}
