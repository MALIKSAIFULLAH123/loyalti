<?php

namespace MetaFox\EMoney\Contracts;

use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Contracts\User;

interface WithdrawMethodInterface
{
    /**
     * @param  User   $user
     * @param  string $currency
     * @return bool
     */
    public function hasAccess(User $user, string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE, ?float $price = null): bool;

    /**
     * @param  User   $user
     * @param  string $currency
     * @return bool
     */
    public function waitForConfirmation(WithdrawRequest $request): bool;

    /**
     * @param  User $user
     * @return bool
     */
    public function validateGateway(User $user, string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): bool;

    /**
     * @param  User            $payee
     * @param  WithdrawRequest $request
     * @param  array           $params
     * @return array|null
     */
    public function placeOrder(User $payee, WithdrawRequest $request, array $params = []): ?array;
}
