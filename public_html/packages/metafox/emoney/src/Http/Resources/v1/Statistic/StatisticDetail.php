<?php

namespace MetaFox\EMoney\Http\Resources\v1\Statistic;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Models\Statistic as Model;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\EMoney\Services\Contracts\ConversionRateServiceInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class StatisticDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class StatisticDetail extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $user = $this->resource->user;

        if (null === $user) {
            return [];
        }

        $userCurrency = app('currency')->getUserCurrencyId($user);

        $data = [
            'id'            => $this->resource->entityId(),
            'module_name'   => Emoney::getAppAlias(),
            'resource_name' => $this->getResourceName(),
            'balances'      => $this->statisticRepository()->getUserBalances($user),
            'user_balance'  => $this->getAmountFormat(...$this->getUserBalanceAmount($userCurrency)),
        ];

        return $this->getDataByVersion($user, $data);
    }

    private function getResourceName(): string
    {
        if (Emoney::isUsingNewAlias()) {
            return $this->resource->entityType();
        }

        return 'emoney_statistic';
    }

    protected function getPurchasedAmount(string $userCurrency): array
    {
        $purchased = $this->getConversedAmount($this->resource->currency, $this->resource->total_purchased, $userCurrency);

        if (null === $purchased) {
            return [$this->resource->currency, $this->resource->total_purchased];
        }

        return [$userCurrency, $purchased];
    }

    protected function getWithdrawnAmount(string $userCurrency): array
    {
        $withdrawn = $this->getConversedAmount($this->resource->currency, $this->resource->total_withdrawn, $userCurrency);

        if (null === $withdrawn) {
            return [$this->resource->currency, $this->resource->total_withdrawn];
        }

        return [$userCurrency, $withdrawn];
    }

    protected function getEarnedAmount(string $userCurrency): array
    {
        $balances = $this->statisticRepository()->getUserAmounts($this->resource->user);
        $earned   = 0;
        foreach ($balances as $balance) {
            $earned = $earned + $this->getConversedAmount($balance['currency'], $balance['total_earned'], $userCurrency);
        }

        if (null === $earned) {
            return [$this->resource->currency, $this->resource->total_earned];
        }

        return [$userCurrency, $earned];
    }

    protected function getUserBalanceAmount(string $userCurrency): array
    {
        $balances      = $this->statisticRepository()->getUserAmounts($this->resource->user);
        $balanceAmount = 0;

        foreach ($balances as $balance) {
            $balanceAmount = $balanceAmount + $this->getConversedAmount($balance['currency'], $balance['total_balance'], $userCurrency);
        }

        if (null === $balanceAmount) {
            return [$this->resource->currency, $this->resource->total_balance];
        }

        return [$userCurrency, $balanceAmount];
    }

    protected function getConversedAmount(string $baseCurrency, float $baseAmount, string $targetCurrency): ?float
    {
        return resolve(ConversionRateServiceInterface::class)->getConversedAmount($baseCurrency, $baseAmount, $targetCurrency);
    }

    protected function getAmountFormat(string $currency, float $amount): ?string
    {
        return app('currency')->getPriceFormatByCurrencyId($currency, $amount);
    }

    protected function statisticRepository(): StatisticRepositoryInterface
    {
        return resolve(StatisticRepositoryInterface::class);
    }

    protected function getDataByVersion(User $user, array $data): array
    {
        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.10', '<')) {
            return $this->getDataOldVersion($user, $data);
        }

        $repository = $this->statisticRepository();

        $results    = [
            'purchased'           => $repository->getUserBalancesByValue($user, 'total_purchased'),
            'withdraw'            => $repository->getUserBalancesByValue($user, 'total_withdrawn'),
            'earned'              => $repository->getUserBalancesByValue($user, 'total_earned'),
            'pending_withdraw'    => $repository->getUserBalancesByValue($user, 'total_pending'),
            'pending_transaction' => $repository->getUserBalancesByValue($user, 'total_pending_transaction'),
            'balance_description' => __p('ewallet::phrase.statistic_balance_description'),
            'sent_by_admin'       => $repository->getUserBalancesByValue($user, 'total_sent'),
            'reduced_by_admin'       => $repository->getUserBalancesByValue($user, 'total_reduced'),
            'exchange_rates'      => $this->getTargetExchangeRates($user),
        ];

        return array_merge($data, $results);
    }

    protected function getTargetExchangeRates(User $user): array
    {
        $userCurrency = app('currency')->getUserCurrencyId($user);
        $earned       = $this->statisticRepository()->getUserBalancesByValue($user, 'total_earned');
        $target       = [];
        foreach ($earned as $value) {
            if ($value['label'] == $userCurrency) {
                continue;
            }

            $price = app('ewallet.conversion-rate')->getExchangeRate($value['label'], $userCurrency);

            if (!$price) {
                continue;
            }

            $target[] = [
                'price'           => $price,
                'base_currency'   => $value['label'],
                'target_currency' => $userCurrency,
            ];
        }

        return $target;
    }

    protected function getDataOldVersion(User $user, array $data): array
    {
        $userCurrency    = app('currency')->getUserCurrencyId($user);
        $balanceCurrency = $this->resource->currency;

        $conversionRate = null;

        if ($userCurrency != $balanceCurrency) {
            $conversionRate = app('ewallet.conversion-rate')->getExchangeRate($userCurrency, $balanceCurrency);
        }

        $results = [
            'purchased'           => $this->getAmountFormat(...$this->getPurchasedAmount($userCurrency)),
            'withdraw'            => $this->getAmountFormat(...$this->getWithdrawnAmount($userCurrency)),
            'earned'              => $this->getAmountFormat(...$this->getEarnedAmount($userCurrency)),
            'pending_withdraw'    => $this->getAmountFormat($this->resource->currency, $this->resource->total_pending),
            'pending_transaction' => $this->getAmountFormat($this->resource->currency, $this->resource->total_pending_transaction),
            'balance_description' => __p('ewallet::phrase.balance_description', [
                'hasConversionRate' => is_numeric($conversionRate) ? 1 : 0,
                'baseCurrency'      => $userCurrency,
                'targetPrice'       => $conversionRate,
                'targetCurrency'    => $balanceCurrency,
            ]),
        ];

        return array_merge($data, $results);
    }
}
