<?php

namespace MetaFox\ChatPlus\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\ChatPlus\Repositories\ChatServerInterface;
use MetaFox\ChatPlus\Repositories\Eloquent\ChatServer;
use MetaFox\ChatPlus\Repositories\Eloquent\JobRepository;
use MetaFox\ChatPlus\Repositories\JobRepositoryInterface;

/**
 * Class PackageServiceProvider.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        ChatServerInterface::class    => ChatServer::class,
        JobRepositoryInterface::class => JobRepository::class,
    ];
}
