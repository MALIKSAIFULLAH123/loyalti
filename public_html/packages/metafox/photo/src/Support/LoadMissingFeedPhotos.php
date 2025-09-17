<?php

namespace MetaFox\Photo\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingFeedPhotos
{
    public function reject($listing)
    {
        return !$listing;
    }

    /**
     * @param  Reducer  $reducer
     * @return void
     */
    public function before($reducer)
    {
        /** @var Collection $items */
        $items = $reducer->entities()
            ->filter(fn($x) => $x->entityType() === 'feed')
            ->map(fn($x) => $x->item)
            ->filter(fn($x) => $x != null && "photo_set" == $x->entityType());



        if ($items->isEmpty()) {
            return;
        }

        $items->each(function ($x) use ($reducer) {
            $reducer->addEntity($x);
            $reducer->addEntity($x->album);

            foreach ($x->media_items[0] as $photo) {
                $reducer->addEntity($photo->detail);
            }
        });
    }
}
