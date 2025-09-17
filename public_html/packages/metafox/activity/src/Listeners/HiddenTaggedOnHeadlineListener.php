<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Models\Type;

class HiddenTaggedOnHeadlineListener
{
    public function handle(string $feedType): bool
    {
        return resolve(TypeManager::class)->hasFeature($feedType, Type::PREVENT_DISPLAY_TAG_ON_HEADLINE);
    }
}
