<?php

namespace MetaFox\Core\Support;

use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Contracts\UniqueIdInterface;

class UniqueId implements UniqueIdInterface
{
    public function getUniqueId(string $itemType): int
    {
        return DB::table('core_items')->insertGetId(['item_type' => $itemType], 'id');
    }
}
