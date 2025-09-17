<?php

namespace MetaFox\Photo\Support;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingAlbumItems
{
    /**
     * @param Reducer $reducer
     * @return void
     */
    public function after($reducer)
    {
        /** @var Collection $items */
        $items = $reducer->entities()
            ->filter(fn ($x) => $x instanceof Media)
            ->map(fn ($x) => $x->album)
            ->filter(fn ($x) => $x instanceof Content);

        if ($items->isEmpty()) {
            return;
        }

        $key = fn ($id) => sprintf('photo_album::items(%s)', $id);

        $data = $items->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x->id)] = null;

            return $carry;
        }, []);

        $items->reduce(function ($carry, $x) use ($key, $reducer) {
            $limit = 4;
            if (!$x->relationLoaded('items')) {
                $x->loadMissing([
                    'items' => function (HasMany $query) use ($limit) {
                        $query->limit($limit);
                    },
                ]);

                $carry[$key($x->id)] = $x->items;

                return $carry;
            }

            $carry[$key($x->id)] = $x->items->take($limit);

            return $carry;
        }, $data);

    }
}
