<?php

namespace MetaFox\Platform\Traits\Eloquent\Model;

/**
 * Trait HasSetActivityTypeIdTrait
 * @package MetaFox\Platform\Traits\Eloquent\Model
 */
trait HasSetActivityTypeIdTrait
{
    public ?string $activity_type_id = null;

    public function setActivityTypeIdAttribute(string $typeId)
    {
        $this->activity_type_id = $typeId;
    }

    public function getActivityTypeIdAttribute(): ?string
    {
        return $this->activity_type_id;
    }
}
