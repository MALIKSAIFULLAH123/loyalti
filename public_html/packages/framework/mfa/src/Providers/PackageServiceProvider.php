<?php

namespace MetaFox\Mfa\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Mfa\Contracts\Mfa as ContractsMfa;
use MetaFox\Mfa\Contracts\ServiceManagerInterface;
use MetaFox\Mfa\Models\UserService;
use MetaFox\Mfa\Observers\UserServiceObserver;
use MetaFox\Mfa\Repositories\Eloquent\EnforceRequestRepository;
use MetaFox\Mfa\Repositories\Eloquent\ServiceRepository;
use MetaFox\Mfa\Repositories\Eloquent\UserAuthTokenRepository;
use MetaFox\Mfa\Repositories\Eloquent\UserServiceRepository;
use MetaFox\Mfa\Repositories\Eloquent\UserVerifyCodeRepository;
use MetaFox\Mfa\Repositories\EnforceRequestRepositoryInterface;
use MetaFox\Mfa\Repositories\ServiceRepositoryInterface;
use MetaFox\Mfa\Repositories\UserAuthTokenRepositoryInterface;
use MetaFox\Mfa\Repositories\UserServiceRepositoryInterface;
use MetaFox\Mfa\Repositories\UserVerifyCodeRepositoryInterface;
use MetaFox\Mfa\Support\Facades\Mfa;
use MetaFox\Mfa\Support\ServiceManager;
use Symfony\Component\Debug\ExceptionHandler;

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
        ServiceRepositoryInterface::class        => ServiceRepository::class,
        UserServiceRepositoryInterface::class    => UserServiceRepository::class,
        UserAuthTokenRepositoryInterface::class  => UserAuthTokenRepository::class,
        UserVerifyCodeRepositoryInterface::class => UserVerifyCodeRepository::class,
        EnforceRequestRepositoryInterface::class => EnforceRequestRepository::class,
        ServiceManagerInterface::class           => ServiceManager::class,
        ContractsMfa::class                      => Mfa::class,
    ];
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        UserService::observe([UserServiceObserver::class]);
    }
}
