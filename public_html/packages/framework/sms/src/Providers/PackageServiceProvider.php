<?php

namespace MetaFox\Sms\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Sms\Contracts\ManagerInterface;
use MetaFox\Sms\Contracts\SmsSupportContracts;
use MetaFox\Sms\Support\SmsManager;
use MetaFox\Sms\Support\SmsSupport;

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
        'sms'                      => SmsSupportContracts::class,
        ManagerInterface::class    => SmsManager::class,
        SmsSupportContracts::class => SmsSupport::class,
    ];
}
