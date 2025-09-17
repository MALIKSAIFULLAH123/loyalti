<?php

namespace MetaFox\ChatPlus\Listeners;

use MetaFox\ChatPlus\Checks\CheckNewChatplusServerVersion;

class HealthExtraCheckerListener
{
    public function handle(): array
    {
        return [
            'handler' => CheckNewChatplusServerVersion::class,
        ];
    }
}
