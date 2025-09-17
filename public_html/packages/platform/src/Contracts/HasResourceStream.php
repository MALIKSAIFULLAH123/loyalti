<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Interface HasResourceStream.
 */
interface HasResourceStream
{
    /**
     * @return HasMany
     */
    public function privacyStreams(): HasMany;
}
