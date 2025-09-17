<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property Collection $hashtags
 * @property string[]   $tags
 * @package MetaFox\Platform\Contracts
 */
interface HasHashTag extends Entity
{
    public function tagData(): BelongsToMany;
}
