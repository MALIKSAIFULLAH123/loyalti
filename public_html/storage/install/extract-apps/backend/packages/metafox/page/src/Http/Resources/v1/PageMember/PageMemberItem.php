<?php

namespace MetaFox\Page\Http\Resources\v1\PageMember;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Models\PageMember as Model;
use MetaFox\Page\Support\Browse\Traits\PageMember\ExtraTrait;
use MetaFox\User\Http\Resources\v1\User\UserItem;

/**
 * Class PageMemberItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageMemberItem extends JsonResource
{
    use ExtraTrait;
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $page = $this->resource->page;
        $user = $this->resource->user;

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'page',
            'resource_name' => $this->resource->entityType(),
            'user'          => new UserItem($user),
            'page_id'       => $this->resource->page_id,
            'member_type'   => $this->resource->member_type,
            'role_label'    => $page->getRoleLabel($user),
            'extra'         => $this->getExtra(),
        ];
    }
}
