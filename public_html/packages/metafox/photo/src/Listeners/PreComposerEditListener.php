<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Photo\Models\Photo;
use MetaFox\Platform\Contracts\User;

class PreComposerEditListener
{
    /**
     * @param  User  $context
     * @param  mixed $params
     * @return void
     */
    public function handle(User $context, mixed $params): void
    {
        if (!is_array($params)) {
            return;
        }

        $newItems     = Arr::get($params, 'photo_files.new', []);
        $removedItems = Arr::get($params, 'photo_files.remove', []);

        $totalNew = collect($newItems)->groupBy('type')->map(function ($item) {
            return count($item);
        })->get('photo');

        $totalRemove = collect($removedItems)->groupBy('type')->map(function ($item) {
            return count($item);
        })->get('photo');

        app('quota')->checkQuotaControlWhenCreateItem($context, Photo::ENTITY_TYPE, $totalNew - $totalRemove);
    }
}
