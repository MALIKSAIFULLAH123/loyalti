<?php

namespace MetaFox\Core\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Core\Models\Link;
use MetaFox\Core\Policies\LinkPolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;

class FeedComposerListener
{
    /**
     * @param  User|null  $user
     * @param  User|null  $owner
     * @param  string     $postType
     * @param  array      $params
     * @return array|null
     */
    public function handle(?User $user, ?User $owner, string $postType, array $params): ?array
    {
        if ($postType != Link::FEED_POST_TYPE) {
            return null;
        }

        if (false === policy_check(LinkPolicy::class, 'hasCreateFeed', $owner, $postType)) {
            return [
                'error_message' => __('validation.no_permission'),
            ];
        }

        $content = Arr::get($params, 'content', '');

        unset($params['content']);

        $location = [];

        if (Arr::has($params, 'location_latitude')) {
            $location = [
                'location_latitude'  => $params['location_latitude'],
                'location_longitude' => $params['location_longitude'],
                'location_name'      => parse_output()->limit(Arr::get($params, 'location_name'), MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH),
                'location_address'   => parse_output()->limit(Arr::get($params, 'location_address'), MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH),
            ];
        }

        $link = new Link();

        $statusBackgroundId = Arr::get($params, 'status_background_id');

        if (!$statusBackgroundId) {
            $statusBackgroundId = null;
        }

        $link->fill(array_merge([
            'user_id'              => $user->entityId(),
            'user_type'            => $user->entityType(),
            'owner_id'             => $owner->entityId(),
            'owner_type'           => $owner->entityType(),
            'privacy'              => $params['privacy'],
            'feed_content'         => $content,
            'title'                => parse_output()->limit(Arr::get($params, 'link_title'), MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH),
            'link'                 => Arr::get($params, 'link_url'),
            'host'                 => Arr::has($params, 'link_url') ? parse_url($params['link_url'], PHP_URL_HOST) : null,
            'image'                => Arr::get($params, 'link_image'),
            'description'          => Arr::get($params, 'link_description'),
            'has_embed'            => 0,
            'is_preview_hidden'    => Arr::get($params, 'is_preview_hidden', false),
            'status_background_id' => $statusBackgroundId,
        ], $location));

        if ($link->privacy == MetaFoxPrivacy::CUSTOM) {
            $link->setPrivacyListAttribute($params['list']);
        }

        $link->save();

        LoadReduce::flush();

        $link->load('activity_feed');

        return [
            'id' => $link->activity_feed ? $link->activity_feed->entityId() : 0,
        ];
    }
}
