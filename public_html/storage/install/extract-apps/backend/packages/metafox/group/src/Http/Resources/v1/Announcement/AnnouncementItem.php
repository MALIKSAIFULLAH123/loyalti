<?php

namespace MetaFox\Group\Http\Resources\v1\Announcement;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Announcement as Model;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class AnnouncementItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class AnnouncementItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request                 $request
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request)
    {
        $context = user();

        return [
            'id'              => $this->resource->entityId(),
            'module_name'     => 'group',
            'resource_name'   => $this->entityType(),
            'user'            => ResourceGate::user($this->resource->userEntity),
            'item'            => ResourceGate::asEmbed($this->resource?->item),
            'creation_date'   => $this->resource->created_at,
            'moderation_date' => $this->resource->updated_at,
            'link'            => $this->resource?->item?->toLink(),
            'url'             => $this->resource?->item?->toUrl(),
            'is_marked_read'  => $this->resource->isMarkedRead($context),
        ];
    }
}
