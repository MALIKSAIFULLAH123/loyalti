<?php

namespace MetaFox\EMoney\Services\Contracts;

use Illuminate\Support\Collection;
use MetaFox\EMoney\Contracts\WithdrawMethodInterface;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Contracts\User;

interface WithdrawServiceInterface
{
    /**
     * @return Collection
     */
    public function getActiveMethods(): Collection;

    /**
     * @param  User   $user
     * @param  string $currency
     * @return array
     */
    public function getAvailableMethodsForUser(User $user, string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): array;

    /**
     * @param  string                  $service
     * @return WithdrawMethodInterface
     */
    public function getServiceProvider(string $service): WithdrawMethodInterface;

    /**
     * @param  User       $user
     * @param  string     $currency
     * @param  float|null $price
     * @return bool
     */
    public function validateWithdrawRequest(User $user, string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE, ?float $price = null): bool;

    /**
     * @param  WithdrawRequest $request
     * @return array
     */
    public function processRequest(WithdrawRequest $request): array;

    /**
     * @return array
     */
    public function availableCurrencies(): array;
}
