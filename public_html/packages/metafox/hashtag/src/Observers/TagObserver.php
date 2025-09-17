<?php

namespace MetaFox\Hashtag\Observers;

use Illuminate\Support\Str;
use MetaFox\Hashtag\Models\Tag;

/**
 * Class TagObserver.
 */
class TagObserver
{
    public function creating(Tag $tag): void
    {
        $textLength = mb_strlen($tag->text);

        $url = Str::lower(Str::slug($tag->text));

        $tag->tag_url =  $textLength === mb_strlen($url) ? $url : urlencode($tag->text);
    }
}
