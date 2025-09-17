<?php

namespace MetaFox\EMoney\Providers\Withdraw;

use MetaFox\EMoney\Contracts\WithdrawMethodInterface;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Services\Contracts\WithdrawServiceInterface;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Contracts\User;

abstract class AbstractWithdrawMethod implements WithdrawMethodInterface
{
    public function __construct(protected WithdrawServiceInterface $service)
    {
    }

    public function hasAccess(User $user, string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE, ?float $price = null): bool
    {
        if (!$this->service->validateWithdrawRequest($user, $currency)) {
            return false;
        }

        if (!$this->validateGateway($user)) {
            return false;
        }

        return true;
    }

    public function waitForConfirmation(WithdrawRequest $request): bool
    {
        return false;
    }
}
