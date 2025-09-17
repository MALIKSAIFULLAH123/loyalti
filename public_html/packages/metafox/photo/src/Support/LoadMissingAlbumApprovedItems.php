<?php

namespace MetaFox\Photo\Support;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFox\Photo\Models\AlbumItem;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingAlbumApprovedItems
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

        $key = fn ($id) => sprintf('photo_album::approved_items(%s)', $id);

        $data = $items->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x->id)] = null;

            return $carry;
        }, []);

        $items->reduce(function ($carry, $x) use ($key, $reducer) {
            $limit = 4;

            if (!$x->relationLoaded('items')) {
                $x->loadMissing([
                    'items' => function (HasMany $query) use ($limit) {
                        $query->where('is_approved', '=', 1)->limit($limit);
                    },
                ]);

                $carry[$key($x->id)] = $x->items;

                return $carry;
            }

            $carry[$key($x->id)] = $x->items->filter(function (AlbumItem $item) {
                return $item->is_approved;
            })->values()->take($limit);

            return $carry;
        }, $data);
    }
}
