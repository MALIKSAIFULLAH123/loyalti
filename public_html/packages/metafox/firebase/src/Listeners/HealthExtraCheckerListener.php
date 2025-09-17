<?php

namespace MetaFox\Firebase\Listeners;

use MetaFox\Firebase\Checks\CheckNewFirebaseConfiguration;

class HealthExtraCheckerListener
{
    public function handle(): array
    {
        return [
            'handler' => CheckNewFirebaseConfiguration::class,
        ];
    }
}
