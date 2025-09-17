<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Http\Resources\v1\Traits\IsUserInvited;
use MetaFox\Page\Http\Resources\v1\Traits\PageHasExtra;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Support\Browse\Traits\PageMember\StatisticTrait;
use MetaFox\Page\Support\Facade\Page as PageFacade;
use MetaFox\Page\Support\Facade\PageMembership;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Support\Facades\User;

/**
 * Class PageItem.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageItem extends JsonResource
{
    use PageHasExtra;
    use StatisticTrait;
    use IsUserInvited;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $context = user();

        $coverExists  = !empty($this->resource->cover);
        $avatarExists = !empty($this->resource->avatar);
        return [
            'id'                   => $this->resource->entityId(),
            'module_name'          => $this->resource->entityType(),
            'resource_name'        => $this->resource->entityType(),
            'title'                => ban_word()->clean($this->resource->name),
            'privacy'              => $this->resource->privacy,
            'view_id'              => $this->resource->is_approved ? 0 : 1,
            'is_liked'             => $this->resource->isMember($context),
            'is_member'            => $this->resource->isMember($context),
            'is_admin'             => $this->resource->isAdmin($context),
            'is_owner'             => $this->resource->isUser($context),
            'is_pending'           => !$this->resource->is_approved,
            'is_invited'           => $this->resource->is_invited,
            'is_featured'          => (bool) $this->resource->is_featured,
            'is_sponsor'           => (bool) $this->resource->is_sponsor,
            'membership'           => PageMembership::getMembership($this->resource, $context),
            'image'                => $this->resource->avatars, //@todo: remove later if not used anymore
            'image_id'             => $this->resource->getAvatarId(), //@todo: remove later if not used anymore
            'avatar'               => $avatarExists ? $this->resource->avatars : null,
            'avatar_id'            => $avatarExists ? $this->resource->getAvatarId() : 0,
            'cover'                => $coverExists ? $this->resource->covers : null,
            'cover_photo_id'       => $coverExists ? $this->resource->getCoverId() : 0,
            'cover_photo_position' => $coverExists ? $this->resource->cover_photo_position : null,
            'user'                 => ResourceGate::user($this->resource->userEntity),
            'short_name'           => User::getShortName(ban_word()->clean($this->resource->name)),
            'summary'              => $this->resource->summary,
            'link'                 => $this->resource->toLink(),
            'url'                  => $this->resource->toUrl(),
            'defaultActiveTabMenu' => PageFacade::getDefaultTabMenu($context, $this->resource),
            'creation_date'        => $this->resource->created_at,
            'modification_date'    => $this->resource->updated_at,
            'statistic'            => $this->getStatistic(),
            'extra'                => $this->getExtra(),
            'cover_resource'       => $this->getCoverResources(),
            'avatar_resource'      => $this->getAvatarResources(),
        ];
    }

    protected function getCoverResources(): ?JsonResource
    {
        if (!$this->resource->cover_id || !$this->resource->cover_type) {
            return null;
        }

        return !empty($this->resource->cover)
            ? ResourceGate::asDetail($this->resource->cover()->first())
            : null;
    }

    protected function getAvatarResources(): ?JsonResource
    {
        if (!$this->resource->avatar_type || !$this->resource->avatar_id) {
            return null;
        }

        return !empty($this->resource->avatar)
            ? ResourceGate::asDetail($this->resource->avatar()->first())
            : null;
    }
}
