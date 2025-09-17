<?php
namespace MetaFox\Photo\Http\Resources\v1\PhotoGroup;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewDetailMediaItem')
            ->apiUrl('photo-set/:id/items')
            ->apiParams([
                'media_id' => ':media_id'
            ]);
    }
}
