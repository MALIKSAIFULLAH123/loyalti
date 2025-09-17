<?php

namespace MetaFox\Core\Listeners;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use MetaFox\Core\Models\Link;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;

class FeedComposerEditListener
{
    /**
     * @param User|null            $user
     * @param User|null            $owner
     * @param mixed                $link
     * @param array<string, mixed> $params
     *
     * @return bool|array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(?User $user, ?User $owner, mixed $link, array $params): ?array
    {
        if ($link?->entityType() != Link::ENTITY_TYPE) {
            return null;
        }

        $location = [];

        if (Arr::has($params, 'location_latitude')) {
            $location = [
                'location_latitude'  => $params['location_latitude'],
                'location_longitude' => $params['location_longitude'],
                'location_name'      => $params['location_name'],
                'location_address'   => Arr::get($params, 'location_address'),
            ];
        }

        $statusBackgroundId = Arr::get($params, 'status_background_id');

        if (!$statusBackgroundId) {
            $statusBackgroundId = null;
        }

        $link->fill(array_merge([
            'privacy'              => $params['privacy'],
            'feed_content'         => Arr::get($params, 'content', ''),
            'title'                => Arr::get($params, 'link_title', MetaFoxConstant::EMPTY_STRING),
            'link'                 => Arr::get($params, 'link_url'),
            'host'                 => Arr::has($params, 'link_url') ? parse_url($params['link_url'], PHP_URL_HOST) : null,
            'image'                => Arr::get($params, 'link_image'),
            'description'          => Arr::get($params, 'link_description'),
            'has_embed'            => 0,
            'is_preview_hidden'    => Arr::get($params, 'is_preview_hidden', false),
            'status_background_id' => $statusBackgroundId,
        ], $location));

        if ($link->privacy == MetaFoxPrivacy::CUSTOM) {
            $link->loadPrivacyListAttribute();

            $link->setPrivacyListAttribute(Arr::get($params, 'list', $link->getPrivacyListAttribute()));
        }

        $link->save();

        return [
            'success' => true,
        ];
    }
}
