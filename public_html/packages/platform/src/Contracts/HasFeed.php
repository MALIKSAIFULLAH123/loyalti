<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property Content $activity_feed
 */
interface HasFeed
{
    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @return MorphOne|null
     */
    public function activity_feed(): ?MorphOne;
}
