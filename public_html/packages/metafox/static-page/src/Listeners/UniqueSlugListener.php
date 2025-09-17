<?php

namespace MetaFox\StaticPage\Listeners;

use Illuminate\Support\Facades\DB;
use MetaFox\StaticPage\Models\StaticPage as Model;

class UniqueSlugListener
{
    public function handle(string $type, string $slug, mixed $id)
    {
        /** @var Model $found */
        $found = DB::selectOne(
            'select id, slug from static_pages where lower(slug)=lower(?) LIMIT 1',
            [$slug]
        );

        if (!$found) {
            return null;
        }

        if ($type === Model::ENTITY_TYPE && $found->id == $id) {
            return null;
        }

        return false;
    }
}
