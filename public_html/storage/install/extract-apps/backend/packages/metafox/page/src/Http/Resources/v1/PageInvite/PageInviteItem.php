<?php

namespace MetaFox\Page\Http\Resources\v1\PageInvite;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Models\PageInvite as Model;
use MetaFox\User\Http\Resources\v1\User\UserItem;

/**
 * Class PageInviteItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageInviteItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $expiredAt = $this->resource->expired_at;
        $expiredDay = null;
        $expiredDescription = null;

        if ($expiredAt !== null) {
            $expiredDay = calculatorExpiredDay($expiredAt);
            $expiredDescription = __p('page::phrase.expired_invite_day', ['value' => $expiredDay]);
        }
        return [
            'id'                  => $this->resource->entityId(),
            'module_name'         => 'page',
            'page_id'             => $this->resource->page->entityId(),
            'resource_name'       => $this->resource->entityType(),
            'invited_member'      => $this->resource->isInviteMember(),
            'invited_admin'       => $this->resource->isInviteAdmin(),
            'status_id'           => $this->resource->status_id,
            'user'                => new UserItem($this->resource->user),
            'owner'               => new UserItem($this->resource->owner), 'invite_type' => $this->resource->invite_type,
            'expired_day'         => $expiredDay,
            'expired_description' => $expiredDescription,
        ];
    }
}
