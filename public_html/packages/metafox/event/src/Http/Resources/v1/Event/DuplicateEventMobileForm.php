<?php

namespace MetaFox\Event\Http\Resources\v1\Event;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Event\Http\Requests\v1\Event\CreateFormRequest;
use MetaFox\Event\Models\Event as Model;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\User\Http\Resources\v1\User\UserItemCollection;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreEventMobileForm.
 * @property Model $resource
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class DuplicateEventMobileForm extends StoreEventMobileForm
{
    public function boot(CreateFormRequest $request, EventRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
        $owner          = $this->resource->owner;
        $context        = user();

        policy_authorize(EventPolicy::class, 'duplicateEvent', $context, $this->resource);
        $this->setOwner($owner);

        if ($owner instanceof HasPrivacyMember) {
            $ownerId           = $owner->entityId();
            $this->apiEndpoint = $this->getApiEndpoint($ownerId);

            return;
        }

        if ($context->hasSuperAdminRole()) {
            $this->setOwner($context);
        }
    }

    protected function prepare(): void
    {
        $eventText = $this->resource->eventText?->text_parsed;
        $privacy   = $this->resource->privacy;
        if ($privacy == MetaFoxPrivacy::CUSTOM) {
            $lists = PrivacyPolicy::getPrivacyItem($this->resource);

            $listIds = [];
            if (!empty($lists)) {
                $listIds = array_column($lists, 'item_id');
            }

            $privacy = $listIds;
        }

        $hosts           = $this->memberRepository()->getEventHostsForForm($this->resource);
        $now             = Carbon::now();
        $startTimestamps = Carbon::make($this->resource->start_time)->timestamp;
        $startDate       = $startTimestamps < $now->timestamp ? $now->toISOString() : $this->resource->start_time;
        $endDate         = $startTimestamps < $now->timestamp ? $now->addHour()->toISOString() : $this->resource->end_time;
        $values          = [
            'name'           => $this->resource->name ?? '',
            'text'           => $eventText ?? '',
            'privacy'        => $privacy,
            'owner_id'       => $this->owner?->entityId() ?? $this->resource->owner_id,
            'attachments'    => $this->resource->attachmentsForForm(),
            'categories'     => $this->resource->categories->pluck('id')->toArray(),
            'is_online'      => $this->resource->is_online,
            'event_url'      => $this->resource->event_url,
            'start_time'     => $startDate,
            'end_time'       => $endDate,
            'host'           => new UserItemCollection($hosts),
            'location'       => $this->resource->toLocationObject(),
            'location_name'  => $this->resource->location_name,
            'duplicate_from' => $this->resource->entityId(),
        ];

        if ($this->resource->image_file_id) {
            Arr::set($values['file'], 'file_type', 'photo');
            Arr::set($values['file'], 'temp_file', $this->resource->image_file_id);
        }

        $this->title(__p('event::phrase.duplicate_event'))
            ->action(url_utility()->makeApiUrl('event'))
            ->asPost()
            ->setValue($values);
    }
}
