<?php

namespace MetaFox\Video\Traits;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Video\Models\Video;

trait FloodControlVideoTrait
{
    protected function checkFloodControlWhenCreateVideo(User $context, array $params, string $key): void
    {
        $files           = Arr::get($params, $key, []);
        $collectionFiles = collect($files)->groupBy('type')->map->count();
        $hasVideo        = (bool) Arr::get($collectionFiles, Video::ENTITY_TYPE, 0);

        if (!$hasVideo) {
            return;
        }

        app('flood')->checkFloodControlWhenCreateItem($context, Video::ENTITY_TYPE);
    }
}
