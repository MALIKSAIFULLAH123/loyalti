<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\HealthCheck\Listeners;

use MetaFox\Core\Support\Facades\License;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Listeners/AdminSignedInListener.stub.
 */

/**
 * Class AdminSignedInListener.
 * @SuppressWarnings(PHPMD)
 * @ignore
 * @codeCoverageIgnore
 */
class AdminSignedInListener
{
    public function handle()
    {
        License::refresh();
    }
}
