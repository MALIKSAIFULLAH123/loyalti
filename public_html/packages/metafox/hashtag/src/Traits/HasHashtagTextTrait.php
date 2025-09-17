<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Hashtag\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Core\Support\Output;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Trait HasHashtagTextTrait.
 * @property Model $resource
 */
trait HasHashtagTextTrait
{
    /**
     * @param  string $content
     * @return string
     */
    public function getTransformContent(string $content): string
    {
        if ($this->resource instanceof HasHashTag) {
            $content = $this->parseHashtags($content);
        }

        if ($content !== null) {
            app('events')->dispatch('core.parse_content', [$this->resource, &$content]);
        }

        return $content;
    }

    protected function parseHashtags(?string $content): ?string
    {
        if (null === $content) {
            return null;
        }

        return parse_output()->convertResourceHashtagsToLink($content, $this->buildResourceTags());
    }

    protected function convertToHashtag(string $text): string
    {
        return Str::lower($text);
    }

    protected function buildContentTags(?string $content): array
    {
        if (null === $content) {
            return [];
        }

        $tags = parse_output()->getHashtags($content);

        if (!count($tags)) {
            return [];
        }

        $mapping = [];

        foreach ($tags as $tag) {
            $mapping[$tag] = $this->convertToHashtag($tag);
        }

        return $mapping;
    }

    protected function buildResourceTags(?HasHashTag $resource = null): array
    {
        if (null === $resource) {
            $resource = $this->resource;
        }

        if (null === $resource) {
            return [];
        }

        $tags = $resource->tagData;

        if (!count($tags)) {
            return [];
        }

        $mapping = [];

        foreach ($tags as $tag) {
            Arr::set($mapping, $tag->text, $tag->tag_url);
        }

        return $mapping;
    }
}
