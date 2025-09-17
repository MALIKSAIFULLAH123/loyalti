<?php

namespace MetaFox\Story\Http\Resources\v1\Story;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StorySet as Model;
use MetaFox\Story\Support\Facades\StoryFacades;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Browse\Traits\User\ExtraTrait;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Traits\UserStatisticTrait;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class UserStory.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class UserStory extends JsonResource
{
    use ExtraTrait;
    use UserStatisticTrait;

    public function toArray($request): array
    {
        $user = $this->resource->user;
        if (!$user instanceof User) {
            return [];
        }

        $context = user();
        $stories = StoryFacades::getStories($context, $this->resource);
        $stories = $stories->isNotEmpty() ? new StoryItemCollection($stories) : [];
        $profile = $user->profile;
        $summary = UserFacade::getSummary($context, $user);

        $data = [
            'id'           => $user->entityId(),
            'full_name'    => $user->full_name,
            'display_name' => $user->display_name,
            'user_name'    => $user->user_name,
            'avatar'       => $profile?->avatar,
            'friendship'   => UserFacade::getFriendship($context, $user),
            'short_name'   => UserFacade::getShortName($user->full_name),
            'summary'      => $summary,
            'link'         => $user->toLink(),
            'url'          => $user->toUrl(),
            'is_owner'     => $profile?->isOwner($context),
            'is_deleted'   => $user->isDeleted(),
        ];

        $data = $this->handleAttributeExtra($context, $user, $data);

        return array_merge($data, [
            'module_name'         => Story::ENTITY_TYPE,
            'resource_name'       => 'user_story',
            'last_item_timestamp' => $this->resource->updated_at,
            'stories'             => $stories,
        ]);
    }

    protected function handleAttributeExtra($context, $user, array $data): array
    {
        $extraAttributes = app('events')->dispatch('user_story.attributes.extra', [$context, $user]);

        if (!is_array($extraAttributes)) {
            return $data;
        }

        foreach ($extraAttributes as $extraAttribute) {
            if (is_array($extraAttribute) && count($extraAttribute)) {
                $data = array_merge($data, $extraAttribute);
            }
        }

        return $data;
    }
}
