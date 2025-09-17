<?php

namespace MetaFox\Event\Http\Resources\v1\Event\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Event\Http\Resources\v1\Traits\EventHasExtra;
use MetaFox\Event\Models\Event as Model;
use MetaFox\Event\Support\Facades\Event;
use MetaFox\Event\Support\Facades\EventMembership;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class EventItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EventItem extends JsonResource
{
    use EventHasExtra;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'title'             => $this->resource->name,
            'description'       => parse_output()->getDescription($this->resource->getDescription()),
            'start_time'        => $this->resource->start_time,
            'end_time'          => $this->resource->end_time,
            'image'             => [
                'url'       => $this->resource->image,
                'file_type' => 'image/*',
            ], 'event_url'      => $this->resource->event_url,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'is_online'         => $this->resource->is_online,
            'is_sponsored'      => $this->resource->is_sponsor,
            'is_approved'       => $this->resource->isApproved(),
            'is_featured'       => $this->resource->is_featured,
            'is_ended'          => $this->resource->isEnded(),
            'status_text'       => Event::getStatusTexts($this->resource),
            'rsvp'              => EventMembership::getMembership($this->resource, user()),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'location'          => $this->resource->toLocationObject(),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'extra'             => $this->getEventExtra(),
        ];
    }
}
