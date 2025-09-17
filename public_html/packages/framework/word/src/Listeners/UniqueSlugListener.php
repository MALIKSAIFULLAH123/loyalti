<?php

namespace MetaFox\Word\Listeners;

use Illuminate\Support\Facades\DB;
use MetaFox\Word\Models\Block as Model;

class UniqueSlugListener
{
    public function handle(string $type, string $slug, mixed $id)
    {
        /** @var Model $found */
        $found = DB::selectOne('select id from core_word_block where lower(word)=lower(?) LIMIT 1', [$slug]);

        if (!$found) {
            return null;
        }

        if ($type === Model::ENTITY_TYPE && $found->id == $id) {
            return null;
        }

        return 'word::validation.this_word_is_preserved';
    }
}
