<?php

namespace MetaFox\Platform\Support;

/**
 * Trait HasBigNumberId
 * @package MetaFox\Platform\Support
 *
 * @property string $primaryKey
 */
trait HasBigNumberId
{
    public function setEntityId(int $id): void
    {
        $this->{$this->primaryKey} = $id;
    }
}
