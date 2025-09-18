<?php

namespace MetaFox\Page\Support;

use MetaFox\Page\Models\Block;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\LoadReduce\Reducer;

class LoadMissingIsUserBlocked
{
    public function after($context, Reducer $reducer)
    {
        $userId = $context->id;
        $pages  = $reducer->entities()
            ->filter(fn ($x) => $x instanceof Page)
            ->map(fn ($x) => $x->id);

        if ($pages->isEmpty()) {
            return;
        }

        $key = fn ($id) => sprintf('user::blocked(user:%s,owner:%s)', $userId, $id);

        $data = $pages->reduce(function ($carry, $x) use ($key) {
            $carry[$key($x)] = false;

            return $carry;
        }, []);

        return Block::query()
            ->where('user_id', $userId)
            ->whereIn('page_id', $pages->all())
            ->get(['page_id'])
            ->reduce(function ($carry, $x) use ($key) {
                $carry[$key($x->page_id)] = true;

                return $carry;
            }, $data);
    }
}
