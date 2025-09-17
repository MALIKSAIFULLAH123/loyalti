<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\PackageManager;

/**
 * @property Content $resource
 * @property string  $text
 * @property string  $text_parsed
 * @mixin Model
 */
interface ResourceText extends Entity
{
    public function resource(): BelongsTo;
}
