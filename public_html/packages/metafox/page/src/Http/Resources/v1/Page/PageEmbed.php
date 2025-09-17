<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Http\Resources\v1\Traits\IsUserInvited;
use MetaFox\Page\Http\Resources\v1\Traits\PageHasExtra;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Support\Browse\Traits\PageMember\StatisticTrait;
use MetaFox\Page\Support\Facade\PageMembership;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Support\Facades\User;

/**
 * Class PageEmbed.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageEmbed extends JsonResource
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
        $context      = user();
        $coverExists  = !empty($this->resource->cover);
        $avatarExists = !empty($this->resource->avatar);
        $avatars      = $avatarExists ? $this->resource->avatars : null;

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'title'         => ban_word()->clean($this->resource->name),
            'full_name'     => ban_word()->clean($this->resource->name),
            'user_name'     => $this->resource->profile_name,
            'is_liked'      => $this->resource->isMember($context),
            'is_member'     => $this->resource->isMember($context),
            'is_owner'      => $this->resource->isUser($context),
            'membership'    => PageMembership::getMembership($this->resource, $context),
            'is_admin'      => $this->resource->isAdmin($context),
            'is_invited'    => !$this->isUserInvited($context),
            'is_featured'   => (bool) $this->resource->is_featured,
            'is_sponsor'    => (bool) $this->resource->is_sponsor,
            'user'          => ResourceGate::user($this->resource->userEntity),
            'image'         => $avatars,
            'image_id'      => $this->resource->getAvatarId(),
            'avatar'        => $avatars,
            'avatar_id'     => $avatarExists ? $this->resource->getAvatarId() : 0,
            'short_name'    => User::getShortName(ban_word()->clean($this->resource->name)),
            'summary'       => $this->resource->summary,
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'extra'         => $this->getExtra(),
            'statistic'     => $this->getStatistic(),
        ];
    }
}
