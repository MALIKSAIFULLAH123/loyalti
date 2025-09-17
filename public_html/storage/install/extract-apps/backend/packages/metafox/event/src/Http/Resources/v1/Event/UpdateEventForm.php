<?php

namespace MetaFox\Event\Http\Resources\v1\Event;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Event\Http\Requests\v1\Event\CreateFormRequest;
use MetaFox\Event\Models\Event as Model;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateEventForm.
 * @property Model $resource
 */
class UpdateEventForm extends StoreEventForm
{
    /** @var bool */
    protected bool $isEdit = true;

    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function boot(CreateFormRequest $request, EventRepositoryInterface $repository, ?int $id = null): void
    {
        $context        = user();
        $this->resource = $repository->find($id);
        $owner          = $this->resource->owner;
        $this->setOwner($owner);
        if ($owner instanceof HasPrivacyMember) {
            $ownerId           = $owner->entityId();
            $this->apiEndpoint = $this->getApiEndpoint($ownerId);
        }
        policy_authorize(EventPolicy::class, 'update', $context, $this->resource);
    }

    protected function prepare(): void
    {
        $eventText = $this->eventText?->text_parsed;

        $privacy = $this->resource->privacy;

        if ($privacy == MetaFoxPrivacy::CUSTOM) {
            $lists = PrivacyPolicy::getPrivacyItem($this->resource);

            $listIds = [];
            if (!empty($lists)) {
                $listIds = array_column($lists, 'item_id');
            }

            $privacy = $listIds;
        }

        $this->title(__p('event::phrase.edit_event'))
            ->setBackProps(__p('core::phrase.events'))
            ->action(url_utility()->makeApiUrl('/event/' . $this->resource->entityId()))
            ->asPut()
            ->setValue([
                'name'          => $this->resource->name ?? '',
                'text'          => $eventText != null ? parse_output()->parseItemDescription($eventText, false, true) : '',
                'privacy'       => $privacy,
                'owner_id'      => $this->resource->owner_id,
                'attachments'   => $this->resource->attachmentsForForm(),
                'categories'    => $this->resource->categories->pluck('id')->toArray(),
                'is_online'     => $this->resource->is_online,
                'event_url'     => $this->resource->event_url,
                'start_time'    => $this->resource->start_time,
                'end_time'      => $this->resource->end_time,
                'host'          => $this->memberRepository()->getEventHostsForForm($this->resource),
                'location'      => $this->resource->is_online ? null : $this->resource->toLocationObject(),
                'location_name' => $this->resource->location_name,
            ]);
    }

    protected function isDisableEventFields(): bool
    {
        return $this->resource instanceof Model && $this->resource->isEnded();
    }

    protected function canManageHosts(): bool
    {
        $context = user();

        return policy_check(EventPolicy::class, 'manageHosts', $context, $this->resource);
    }
}
