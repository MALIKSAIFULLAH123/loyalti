<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Video\Models\Video;

class GetUrlVideoByIdListener
{

    public function __construct()
    {
    }

    /**
     * @param  int     $id
     * @param  string  $type
     * @return array|null
     */
    public function handle(int $id, string $type): ?array
    {
        if ($type != Video::ENTITY_TYPE) {
            return null;
        }

        return [
            'phrase'    => __p('video::phrase.video_are_not_showing'),
            'image_url' => null,
        ];
    }
}
