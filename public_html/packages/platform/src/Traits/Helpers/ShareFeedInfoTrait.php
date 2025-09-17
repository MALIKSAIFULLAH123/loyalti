<?php

namespace MetaFox\Platform\Traits\Helpers;

use Illuminate\Support\Arr;
use MetaFox\Core\Support\Output;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasFeedContent;
use MetaFox\Platform\Contracts\HasHashTag;
use MetaFox\Platform\Contracts\HasLocationCheckin;

/**
 * @property
 */
trait ShareFeedInfoTrait
{
    use IsFriendTrait;
    use HasHashtagTextTrait;

    public function toLocation(HasLocationCheckin $item): ?array
    {
        [$address, $lat, $lng] = $item->toLocation();

        if ($address && $lat && $lng) {
            $data = [
                'address' => $address,
                'lat'     => (float) $lat,
                'lng'     => (float) $lng,
            ];

            if (is_bool($item->show_map_on_feed)) {
                Arr::set($data, 'show_map', $item->show_map_on_feed);
            }

            return $data;
        }

        return null;
    }

    public function toFeedContent(HasFeedContent $item): ?string
    {
        if (!$item instanceof Content) {
            return null;
        }

        $feed = $item->activity_feed;

        if (!$feed instanceof Content) {
            return null;
        }

        return $this->getFeedTransformContent($feed);
    }

    protected function parseFeedHashtags(HasHashTag $feed, string $content): string
    {
        $resourceHashtags = $this->buildResourceTags($feed);

        if (!count($resourceHashtags)) {
            return $content;
        }

        return parse_output()->convertResourceHashtagsToLink($content, $resourceHashtags);
    }

    protected function getFeedTransformContent(Content $feed): ?string
    {
        $content = $feed->content;

        if (!is_string($content)) {
            return null;
        }

        $content = parse_output()->parse($content);

        if ($feed instanceof HasHashTag) {
            $content = $this->parseFeedHashtags($feed, $content);
        }

        app('events')->dispatch('core.parse_content', [$feed, &$content]);

        return $content;
    }

    protected function isHideTaggedHeadline(Content $content): bool
    {
        if (!$content instanceof ActivityFeedSource) {
            return false;
        }

        if ($content->activity_feed == null) {
            return false;
        }

        return app('events')->dispatch('core.activity_feed.is_hidden_tagged_headline', [$content->activity_feed->type_id], true) ?? true;
    }
}
