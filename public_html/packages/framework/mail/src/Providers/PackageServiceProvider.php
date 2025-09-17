<?php

namespace MetaFox\Mail\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Mail\Contracts\MailSupportContracts;
use MetaFox\Mail\Support\MailSupport;

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
    /**
     * @var string[]
     */
    public array $singletons = [
        'mail'                      => MailSupportContracts::class,
        MailSupportContracts::class => MailSupport::class,
    ];
}
