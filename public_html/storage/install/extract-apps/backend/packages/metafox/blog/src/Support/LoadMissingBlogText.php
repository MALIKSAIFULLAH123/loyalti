<?php

namespace MetaFox\Blog\Support;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Blog\Models\Blog;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingBlogText
{
    public function after(Reducer $reducer)
    {
        $items = $reducer->entities()
            ->filter(fn ($x) => $x instanceof Blog);

        if ($items->isEmpty()) {
            return;
        }

        $items->reduce(fn ($carry, $x) => $carry->add($x), new Collection())->loadMissing('blogText');
    }
}
