<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\PrivacyTypeHandler;

class FilterPublicGroupListener
{
    public function handle(Collection $userEntities): Collection
    {
        $filtered = $userEntities->filter(function ($entity) {
            return $entity->entity_type == Group::ENTITY_TYPE;
        });

        if (!$filtered->count()) {
            return $filtered;
        }

        return Group::query()
            ->whereIn('id', $filtered->pluck('id')->toArray())
            ->where('privacy_type', PrivacyTypeHandler::PUBLIC)
            ->get();
    }
}
