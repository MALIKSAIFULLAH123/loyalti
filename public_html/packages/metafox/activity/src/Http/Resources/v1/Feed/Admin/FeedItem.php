<?php

namespace MetaFox\Activity\Http\Resources\v1\Feed\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Repositories\TypeRepositoryInterface;
use MetaFox\Activity\Traits\FeedSupport;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;

/**
 * Class FeedItem.
 *
 * Do not use Gate in here to improve performance.
 *
 * @property Feed $resource
 */
class FeedItem extends JsonResource
{
    use FeedSupport;
    use HasExtra;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        $actionPhrase = $this->getTypeManager()->getTypePhraseWithContext($this->resource);
        $item         = $this->resource->item;
        $actionItem   = $this->getActionResource();

        return [
            'id'                   => $this->resource->entityId(),
            'module_name'          => $this->resource->entityType(),
            'resource_name'        => $this->resource->entityType(),
            'user'                 => ResourceGate::user($this->resource->userEntity),
            'owner'                => ResourceGate::user($this->resource->ownerEntity),
            'headline'             => $actionPhrase,
            'content'              => parse_input()->clean($this->getParsedContent(false), true),
            'embed_object'         => $this->getEmbedObject($item),
            'item_type_label'      => $this->getItemTypeLabel($item),
            'feed_type'            => $this->getFeedType($this->resource->type_id),
            'created_at'           => $this->resource->created_at,
            'parent_user'          => $this->getParentUser(),
            'tagged_friends'       => new UserEntityCollection($this->getTaggedFriendsForFeed(3)),
            'total_friends_tagged' => $this->getTotalFriendsTagged($actionItem),
            'location'             => $this->getLocation(),
            'is_show_location'     => $this->isShowLocation($this->getReactItem($actionItem)),
            'url'                  => $this->resource->toUrl(),
            'extra'                => $this->getFeedExtra(),
        ];
    }

    protected function getFeedType(?string $type): ?string
    {
        $feedType = resolve(TypeRepositoryInterface::class)->getTypeByType($type);

        return $feedType?->title;
    }

    protected function getItemTypeLabel(?Entity $item): ?string
    {
        if (!$item instanceof Entity) {
            return null;
        }

        return Str::headline(__p_type_key($this->resource->item->entityType()));
    }

    protected function getEmbedObject(?Entity $item): ?JsonResource
    {
        if (!$item instanceof Entity) {
            return null;
        }

        $resource = ResourceGate::asResource($item, 'feed_embed', false);

        if (null !== $resource) {
            return $resource;
        }

        return ResourceGate::asEmbed($item);
    }
}
