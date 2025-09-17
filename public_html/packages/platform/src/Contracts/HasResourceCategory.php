<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Interface HasResourceCategory.
 * @package MetaFox\Platform\Contracts
 */
interface HasResourceCategory
{
    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany;
}
