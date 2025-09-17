<?php

namespace MetaFox\User\Listeners;

use Illuminate\Support\Facades\DB;
use MetaFox\User\Models\UserEntity as Model;

class UniqueSlugListener
{
    public function handle(string $type, string $slug, mixed $id): ?bool
    {
        /** @var Model $found */
        $found = DB::selectOne(
            'select id, entity_type from user_entities where lower(user_name)=lower(?) LIMIT 1',
            [$slug]
        );

        if (!$found) {
            return null;
        }

        if ($type == $found->entity_type && $found->id == $id) {
            return null;
        }

        return false;
    }
}
