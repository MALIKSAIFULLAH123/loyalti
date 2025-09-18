<?php

namespace MetaFox\Music\Support\Browse\Traits\Genre;

use MetaFox\Music\Models\Genre;
use MetaFox\Platform\ResourcePermission;

/**
 * @property Genre $resource
 */
trait ExtraTrait
{
    public function getExtra(): array
    {
        return [
            ResourcePermission::CAN_DELETE => !$this->resource->is_default,
        ];
    }
}
