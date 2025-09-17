<?php

namespace MetaFox\Platform\Traits\Http\Request;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Trait TagRequestTrait.
 * @property Content $resource
 */
trait TagRequestTrait
{
    /**
     * @param  array<string, mixed> $rules
     * @return array<string, mixed>
     */
    protected function applyTagRules(array $rules): array
    {
        $ownerId = request()->get('owner_id');
        if (!$ownerId) {
            return $rules;
        }

        $owner = UserEntity::getById($ownerId)->detail;
        if ($owner instanceof HasPrivacyMember) {
            unset($rules['tags']);
            unset($rules['tags.*']);
        }

        return $rules;
    }
}
