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
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;

/**
 * Class EventDetail.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EventDetail extends JsonResource
{
    use EventHasExtra;
    use HasFeedParam;
    use EventHasStatistic;

    private ?string $inviteCode = null;

    public function setInviteCode(string $inviteCode): self
    {
        $this->inviteCode = $inviteCode;

        return $this;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request)
    {
        $context           = user();
        $pendingInvite     = EventInvite::getAvailableInvite($this->resource, $context, $this->inviteCode);
        $pendingHostInvite = EventInvite::getPendingHostInvite($this->resource, $context);
        $owner             = ResourceGate::asDetail($this->resource->owner, false);
        $privacyDetail     = $this->getPrivacyDetail();
        $feedParams        = array_merge($this->getFeedParams()->toArray($request), [
            'privacy_detail' => $privacyDetail,
            'feed_status'    => '',
        ]);

        $data = [
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
            'pending_mode'      => $this->resource->isPendingMode(),
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
            'is_ended'            => $this->resource->isEnded(),
            'is_host'             => $this->resource->isModerator($context),
            'status'              => $this->resource->getStatus(),
            'rsvp'                => EventMembership::getMembership($this->resource, $context),
            'user'                => ResourceGate::user($this->resource->userEntity),
            'owner'               => $owner,
            'owner_type_name'     => __p_type_key($this->resource->ownerType()),
            'attachments'         => ResourceGate::items($this->resource->attachments, false),
            'categories'          => ResourceGate::embeds($this->resource->categories),
            'location'            => $this->resource->toLocationObject(),
            'link'                => $this->resource->toLink(),
            'url'                 => $this->resource->toUrl(),
            'statistic'           => $this->getStatistic(),
            'extra'               => $this->getEventExtra(),
            'feed_param'          => $feedParams,
            'pending_invite'      => $pendingInvite ? ResourceGate::asResource($pendingInvite, 'item', false) : null,
            'pending_host_invite' => $pendingHostInvite ? ResourceGate::asResource(
                $pendingHostInvite,
                'item',
                false
            ) : null,
            'parent_id'        => 0,
            'owner_id'         => $this->resource->ownerId(),
            'privacy_detail'   => $privacyDetail,
            'privacy_feed'     => $this->getPrivacyFeed(),
            'info'             => 'added_an_event',
            'is_show_location' => false,
        ];

        if ($this->resource->owner instanceof HasPrivacyMember) {
            $data['parent_id'] = $this->resource->ownerId();
        }

        return $data;
    }

    protected function getPrivacyDetail(): ?array
    {
        $context = user();

        $owner = $this->resource->owner;

        if (!$owner instanceof HasPrivacyMember) {
            return app('events')->dispatch('core.privacy.get_default', [$this->resource->privacy, $context, $owner], true);
        }

        return app('events')->dispatch(
            'activity.get_privacy_detail_on_owner',
            [$context, $this->resource],
            true
        );
    }

    protected function getPrivacyFeed(): ?array
    {
        $info = app('events')->dispatch(
            'activity.get_privacy_detail_on_owner',
            [user(), $this->resource],
            true
        );

        if (null === $info) {
            return null;
        }

        unset($info['label']);

        return $info;
    }
}
