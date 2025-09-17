<?php

namespace MetaFox\Comment\Traits;

use Illuminate\Support\Str;
use MetaFox\Comment\Models\Comment;
use MetaFox\Hashtag\Models\Tag;
use MetaFox\Platform\Contracts\HasHashTag;

/**
 * @property Comment $resource
 */
trait HasTransformContent
{
    /**
     * @param  bool        $parseUserFullLink
     * @return string|null
     */
    public function getTransformContent(bool $parseUserFullLink = false): ?string
    {
        $content = $this->resource->text_parsed;

        /*if (is_string($content)) {
            $content = str_replace(['&amp;'], ['&'], $content);
        }*/

        if ($this->resource instanceof HasHashTag) {
            $this->resource->tagData->each(function (Tag $tag) use (&$content) {
                $hashtag = '#' . $tag->text;
                $link    = parse_output()->buildHashtagLink($hashtag, $tag->tag_url);
                $content = Str::of($content)->replaceFirst($hashtag, $link);
            });
        }

        $attributeParser = [
            'target'          => '_self',
            'parse_full_link' => $parseUserFullLink,
        ];

        if ($content !== null) {
            app('events')->dispatch('core.parse_content', [$this->resource, &$content, $attributeParser]);
        }

        return $content;
    }
}
