<?php

namespace MetaFox\Activity\Contracts;

use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Contracts\User;

/**
 * Interface ActivityPinManager.
 */
interface ActivityPinManager
{
    public function getCacheName(int $userId): string;

    public function clearCache(int $userId): void;
}
