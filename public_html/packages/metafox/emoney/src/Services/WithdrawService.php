<?php

namespace MetaFox\EMoney\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\EMoney\Contracts\WithdrawMethodInterface;
use MetaFox\EMoney\Facades\Emoney;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\EMoney\Repositories\WithdrawMethodRepositoryInterface;
use MetaFox\EMoney\Services\Contracts\WithdrawServiceInterface;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Contracts\User;

class WithdrawService implements WithdrawServiceInterface
{
    private Collection $methods;

    public function __construct(private WithdrawMethodRepositoryInterface $repository)
    {
        $this->initialize();
    }

    private function initialize(): void
    {
        $methods = $this->repository->viewMethods();

        $this->methods = $methods->keyBy('service');
    }

    public function getActiveMethods(): Collection
    {
        return $this->methods->filter(function ($method) {
            return $method->is_active;
        });
    }

    public function getAvailableMethodsForUser(User $user, string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE): array
    {
        return $this->getActiveMethods()->filter(function ($method) use ($user, $currency) {
            return $this->getServiceProvider($method->service)->hasAccess($user, $currency);
        })
            ->map(function ($method) {
                return [
                    'label' => $method->title,
                    'value' => $method->service,
                ];
            })
            ->toArray();
    }

    public function getServiceProvider(string $service): WithdrawMethodInterface
    {
        $class = Arr::get($this->methods, sprintf('%s.service_class', $service));

        if (null === $class || !class_exists($class)) {
            throw new \RuntimeException('Withdraw Service Class not found.');
        }

        return resolve($class);
    }

    public function validateWithdrawRequest(User $user, string $currency = Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE, ?float $price = null): bool
    {
        $min = Emoney::getMinimumWithdrawalAmount($currency);

        if (!is_numeric($min) || $min < 0) {
            return false;
        }

        if (is_numeric($price) && $price <= 0) {
            return false;
        }

        $userBalance = resolve(StatisticRepositoryInterface::class)->getUserBalance($user, $currency);

        if ($userBalance <= 0) {
            return false;
        }

        if (is_numeric($price)) {
            if ($price > $userBalance) {
                return false;
            }

            if ($price < $min) {
                return false;
            }
        }

        if ($min == 0) {
            return true;
        }

        return $userBalance >= $min;
    }

    public function processRequest(WithdrawRequest $request): array
    {
        $provider = $this->getServiceProvider($request->withdraw_service);

        if (!$provider instanceof WithdrawMethodInterface) {
            throw new \RuntimeException('Provider does not support this feature.');
        }

        $payee = $request->user;

        if (null === $payee) {
            throw new \RuntimeException('Payee not found.');
        }

        if (!$provider->hasAccess($payee, $request->currency, $request->amount)) {
            throw new AuthorizationException();
        }

        $wait = $provider->waitForConfirmation($request);

        $status = match ($wait) {
            true    => Support::WITHDRAW_STATUS_WAITING_CONFIRMATION,
            default => Support::WITHDRAW_STATUS_PROCESSING,
        };

        $request->update(['status' => $status]);

        if ($wait) {
            return ['is_waiting' => true];
        }

        return $provider->placeOrder($payee, $request);
    }

    public function availableCurrencies(): array
    {
        return [Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE];
    }
}
