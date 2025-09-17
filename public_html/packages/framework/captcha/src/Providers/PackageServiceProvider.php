<?php

namespace MetaFox\Captcha\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Captcha\Support\CaptchaSupport;
use MetaFox\Captcha\Support\Contracts\CaptchaSupportContract;

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
        CaptchaSupportContract::class => CaptchaSupport::class,
    ];
}
