<?php

namespace MetaFox\EMoney\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\EMoney\Contracts\PaymentInterface;
use MetaFox\EMoney\Contracts\SupportInterface;
use MetaFox\EMoney\Contracts\UserBalanceSupportInterface;
use MetaFox\EMoney\Models\WithdrawRequest;
use MetaFox\EMoney\Repositories\CurrencyConversionRateLogRepositoryInterface;
use MetaFox\EMoney\Repositories\CurrencyConverterRepositoryInterface;
use MetaFox\EMoney\Repositories\Eloquent\CurrencyConversionRateLogRepository;
use MetaFox\EMoney\Repositories\Eloquent\CurrencyConverterRepository;
use MetaFox\EMoney\Repositories\Eloquent\StatisticRepository;
use MetaFox\EMoney\Repositories\Eloquent\TransactionRepository;
use MetaFox\EMoney\Repositories\Eloquent\WithdrawMethodRepository;
use MetaFox\EMoney\Repositories\Eloquent\WithdrawRequestReasonRepository;
use MetaFox\EMoney\Repositories\Eloquent\WithdrawRequestRepository;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\EMoney\Repositories\TransactionRepositoryInterface;
use MetaFox\EMoney\Repositories\WithdrawMethodRepositoryInterface;
use MetaFox\EMoney\Repositories\WithdrawRequestReasonRepositoryInterface;
use MetaFox\EMoney\Repositories\WithdrawRequestRepositoryInterface;
use MetaFox\EMoney\Services\Contracts\ConversionRateServiceInterface;
use MetaFox\EMoney\Services\Contracts\UserBalanceServiceInterface;
use MetaFox\EMoney\Services\Contracts\WithdrawServiceInterface;
use MetaFox\EMoney\Services\ConversionRateService;
use MetaFox\EMoney\Services\UserBalanceService;
use MetaFox\EMoney\Services\WithdrawService;
use MetaFox\EMoney\Support\PaymentSupport;
use MetaFox\EMoney\Support\Support;
use MetaFox\EMoney\Support\UserBalanceSupport;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Providers/PackageServiceProvider.stub.
 */

/**
 * Class PackageServiceProvider.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        CurrencyConversionRateLogRepositoryInterface::class => CurrencyConversionRateLogRepository::class,
        CurrencyConverterRepositoryInterface::class         => CurrencyConverterRepository::class,
        StatisticRepositoryInterface::class                 => StatisticRepository::class,
        TransactionRepositoryInterface::class               => TransactionRepository::class,
        WithdrawMethodRepositoryInterface::class            => WithdrawMethodRepository::class,
        WithdrawRequestReasonRepositoryInterface::class     => WithdrawRequestReasonRepository::class,
        WithdrawRequestRepositoryInterface::class           => WithdrawRequestRepository::class,
        ConversionRateServiceInterface::class               => ConversionRateService::class,
        WithdrawServiceInterface::class                     => WithdrawService::class,
        SupportInterface::class                             => Support::class,
        PaymentInterface::class                             => PaymentSupport::class,
        'ewallet.conversion-rate'                           => ConversionRateService::class,
        'ewallet.transaction'                               => TransactionRepository::class,
        'ewallet.statistic'                                 => StatisticRepository::class,
        UserBalanceServiceInterface::class                  => UserBalanceService::class,
        UserBalanceSupportInterface::class                  => UserBalanceSupport::class,
    ];

    public function boot()
    {
        WithdrawRequest::observe([EloquentModelObserver::class]);
    }
}
