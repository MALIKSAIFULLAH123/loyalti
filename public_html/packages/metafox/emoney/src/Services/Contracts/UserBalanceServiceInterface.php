<?php
namespace MetaFox\EMoney\Services\Contracts;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Platform\Contracts\User;
use Illuminate\Support\Collection;

interface UserBalanceServiceInterface
{
    /**
     * @param array $attributes
     * @return Paginator
     */
    public function manageUserBalances(array $attributes = []): Paginator;

    /**
     * @param Collection $collection
     * @return void
     */
    public function preloadUserBalances(Collection $collection): void;

    /**
     * @param User $user
     * @return array
     */
    public function getUserBalances(User $user): array;

    /**
     * @param User   $context
     * @param User   $user
     * @param string $currency
     * @param float  $amount
     * @return bool
     */
    public function sendAmountToSpecificUserBalanceByCurrency(User $context, User $user, string $currency, float $amount): bool;

    /**
     * @param User   $context
     * @param User   $user
     * @param string $currency
     * @param float  $amount
     * @return bool
     */
    public function reduceAmountToSpecificUserBalanceByCurrency(User $context, User $user, string $currency, float $amount): bool;

    /**
     * @param User  $context
     * @param User  $user
     * @param array $attributes
     * @return Paginator
     */
    public function manageAdjustmentHistories(User $context, User $user, array $attributes = []): Paginator;
}
