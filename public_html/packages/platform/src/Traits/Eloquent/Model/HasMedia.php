<?php

namespace MetaFox\Platform\Traits\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Media;

/**
 * @mixin Model
 * @mixin Media
 * @property int $in_process
 */
trait HasMedia
{
    public function getIsProcessingAttribute(): bool
    {
        return $this->getInProcessValue() == 1;
    }

    public function getIsSuccessAttribute(): bool
    {
        return $this->getInProcessValue() == 0;
    }

    public function getIsFailedAttribute(): bool
    {
        return $this->getInProcessValue(false) == 2;
    }

    protected function getInProcessValue(bool $default = true): int
    {
        if (array_key_exists('in_process', $this->attributes)) {
            return (int) $this->attributes['in_process'];
        }

        return $default;
    }
}
