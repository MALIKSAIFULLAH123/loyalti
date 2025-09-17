<?php

namespace MetaFox\Hashtag\Listeners;

use MetaFox\Hashtag\Repositories\Eloquent\TagRepository;
use MetaFox\Hashtag\Repositories\TagRepositoryInterface;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\User as ContractUser;

class ItemTagAwareListener
{
    /**
     * @param  ContractUser $context
     * @param  HasHashTag   $hasHashTag
     * @param  string|null  $content
     * @param  bool         $allowSpace
     * @param  bool         $returnHashTagIdsOnly
     * @return array
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(
        ContractUser $context,
        HasHashTag $hasHashTag,
        ?string $content,
        bool $allowSpace = false,
        bool $returnHashTagIdsOnly = false
    ): array {
        if (!method_exists($hasHashTag, 'tagData')) {
            return [];
        }

        // must support tags & topic value
        $hashtags = $tags = parse_output()->getHashtags($content, $allowSpace);

        if (is_array($hasHashTag->tags)) {
            $tags = array_merge($tags, $hasHashTag->tags);
        }

        if (!count($tags)) {
            return [];
        }

        $tagIds = resolve(TagRepositoryInterface::class)->getTagIds($tags);

        $hasHashTag->tagData()->sync($tagIds);

        if (is_array($tagIds)) {
            if ($returnHashTagIdsOnly) {
                $tagIds = resolve(TagRepositoryInterface::class)->getTagIds($hashtags);
            }

            return $tagIds;
        }

        return [];
    }
}
