<?php

namespace MetaFox\User\Support;

use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasItemMorph;
use MetaFox\Platform\LoadReduce\Reducer;
use MetaFox\Platform\Support\Browse\Scopes\EntityTypeScope;

class LoadMissingUserAndOwner
{
    /**
     * @param  Collection $items
     * @param  Reducer    $reducer
     * @return void
     */
    public function before($items, $reducer)
    {
        $items->filter(fn ($item) => $item instanceof Entity)
            ->each(function ($item) use ($reducer) {
                $reducer->addEntity($item);
            });

        // Preload item if needed
        $c = new ModelCollection([]);

        $items->filter(fn ($x) => $x instanceof Content && $x instanceof HasItemMorph)
            ->each(fn ($x) => $c->add($x));

        if (!$c->isEmpty()) {
            $c->loadMissing(['item'])->each(function ($x) use ($reducer) {
                $reducer->addEntity($x);
            });
        }

        $items->filter(fn ($x) => $x instanceof Content && $x instanceof HasItemMorph && $x->item instanceof Model)
            ->map(fn ($x) => $x->item)
            ->each(fn ($x) => $reducer->addEntity($x));

        $items->filter(fn ($item) => $item instanceof Content)
            ->map(fn ($x) => $x->reactItem())
            ->each(function ($item) use ($reducer) {
                $reducer->addEntity($item);
            });
    }

    /**
     * @param  Reducer $reducer
     * @return void
     */
    public function handle($reducer)
    {
        $users = $reducer->types('users');

        if (empty($users)) {
            return null;
        }

        // preload alls.
        foreach ($users as $type => $ids) {
            $reducer->loadMissingEntities($type, collect($ids));
            $reducer->loadMissingEntities('user_profile', collect($ids));
            $reducer->loadMissingEntities('user_entity', collect($ids));
        }
    }
}
