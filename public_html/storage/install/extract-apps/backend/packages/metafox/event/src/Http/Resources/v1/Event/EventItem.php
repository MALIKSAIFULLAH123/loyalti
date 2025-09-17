<?php

namespace MetaFox\Event\Http\Resources\v1\Event;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Event\Http\Resources\v1\Traits\EventHasExtra;
use MetaFox\Event\Http\Resources\v1\Traits\EventHasStatistic;
use MetaFox\Event\Models\Event as Model;
use MetaFox\Event\Support\Facades\EventInvite;
use MetaFox\Event\Support\Facades\EventMembership;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class EventItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EventItem extends JsonResource
{
    use EventHasStatistic;
    use EventHasExtra;

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request                 $request
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request)
    {
        $context       = user();
        $pendingInvite = EventInvite::getPendingInvite($this->resource, $context);

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->entityType(),
            'resource_name'     => $this->resource->entityType(),
            'title'             => ban_word()->clean($this->resource->name),
            'privacy'           => $this->resource->privacy,
            'description'       => $this->resource->getDescription(),
            'view_id'           => $this->resource->view_id,
            'start_time'        => $this->resource->start_time,
            'end_time'          => $this->resource->end_time,
            'image'             => $this->resource->images,
            'image_position'    => $this->resource->image_position,
            'event_url'         => $this->resource->event_url,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'is_online'         => $this->resource->is_online,
            'is_approved'       => $this->resource->is_approved,
            'is_pending'        => !$this->resource->is_approved,
            'is_sponsor'        => $this->resource->is_sponsor,
            'is_featured'       => $this->resource->is_featured,
            'is_sponsored_feed' => $this->resource->sponsor_in_feed,
            'is_saved'          => PolicyGate::check(
                $this->resource->entityType(),
                'isSavedItem',
                [$context, $this->resource]
            ),
            'is_ended'       => $this->resource->isEnded(),
            'is_host'        => $this->resource->isModerator($context),
            'status'         => $this->resource->getStatus(),
            'rsvp'           => EventMembership::getMembership($this->resource, user()),
            'user'           => ResourceGate::user($this->resource->userEntity),
            'attachments'    => ResourceGate::items($this->resource->attachments, false),
            'categories'     => ResourceGate::embeds($this->resource->categories, false),
            'location'       => $this->resource->toLocationObject(),
            'link'           => $this->resource->toLink(),
            'url'            => $this->resource->toUrl(),
            'statistic'      => $this->getStatistic(),
            'extra'          => $this->getEventExtra(),
            'pending_invite' => $pendingInvite ? ResourceGate::detail($pendingInvite, false) : null,
            'owner_id'       => $this->resource->ownerId(),
        ];
    }
}
