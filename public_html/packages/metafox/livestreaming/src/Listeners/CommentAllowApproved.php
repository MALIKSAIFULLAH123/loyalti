<?php

namespace MetaFox\LiveStreaming\Listeners;

use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\Platform\Contracts\Content;

class CommentAllowApproved
{
    public function handle(?Content $item)
    {
        if (!$item instanceof LiveVideo) {
            return null;
        }

        return $item->is_streaming;
    }
}
