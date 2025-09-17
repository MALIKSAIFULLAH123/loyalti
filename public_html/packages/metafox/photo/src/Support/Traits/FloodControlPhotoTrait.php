<?php

namespace MetaFox\Photo\Support\Traits;

use Illuminate\Support\Arr;
use MetaFox\Photo\Models\Photo;
use MetaFox\Platform\Contracts\User;

trait FloodControlPhotoTrait
{
    protected function checkFloodControlWhenCreatePhoto(User $context, array $params, string $key): void
    {
        $files           = Arr::get($params, $key, []);
        $collectionFiles = collect($files)->groupBy('type')->map->count();
        $hasPhoto        = (bool) Arr::get($collectionFiles, Photo::ENTITY_TYPE, 0);

        if (!$hasPhoto) {
            return;
        }

        app('flood')->checkFloodControlWhenCreateItem($context, Photo::ENTITY_TYPE);
    }
}
