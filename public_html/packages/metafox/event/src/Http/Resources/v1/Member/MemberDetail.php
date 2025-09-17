<?php

namespace MetaFox\Event\Http\Resources\v1\Member;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Event\Http\Resources\v1\Traits\MemberHasExtra;
use MetaFox\Event\Models\Member as Model;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\User\Http\Resources\v1\User\UserItem;

/**
|--------------------------------------------------------------------------
| Resource Detail
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
| @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview
 **/

/**
 * Class MemberDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class MemberDetail extends JsonResource
{
    use MemberHasExtra;

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'event',
            'resource_name' => $this->resource->entityType(),
            'user'          => new UserItem($this->resource->user),
            'event_id'      => $this->resource->event_id,
            'role_id'       => $this->resource->role_id,
            'rsvp_id'       => $this->resource->rsvp_id,
            'event'         => ResourceGate::asResource($this->resource->event, 'item'),
            'extra'         => $this->getMemberExtra(),
        ];
    }
}
