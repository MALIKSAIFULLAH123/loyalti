<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Support\TypeManager;

class HasActivityFeature
{
    public function hasFeature(string $type, string $feature): bool
    {
        return resolve(TypeManager::class)->hasFeature($type, $feature);
    }
}
