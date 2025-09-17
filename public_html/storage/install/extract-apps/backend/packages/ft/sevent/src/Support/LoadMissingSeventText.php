<?php

namespace Foxexpert\Sevent\Support;

use Illuminate\Database\Eloquent\Collection;
use Foxexpert\Sevent\Models\Sevent;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingSeventText
{
    public function after(Reducer $reducer)
    {
        $items = $reducer->entities()
            ->filter(fn ($x) => $x instanceof Sevent);

        if ($items->isEmpty()) {
            return;
        }

        $items->reduce(fn ($carry, $x) => $carry->add($x), new Collection())->loadMissing('seventText');
    }
}
