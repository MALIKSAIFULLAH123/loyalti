<?php

namespace MetaFox\Event\Http\Resources\v1\Event;

use MetaFox\Event\Http\Requests\v1\Event\CreateFormRequest;
use MetaFox\Event\Models\Event as Model;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Event\Repositories\MemberRepositoryInterface;
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
class UpdateEventMobileForm extends StoreEventMobileForm
{
    /** @var bool */
    protected $isEdit = false;

    protected function memberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }

    public function boot(CreateFormRequest $request, EventRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
        $owner          = $this->resource->owner;

        $this->setOwner($owner);

        if ($owner instanceof HasPrivacyMember) {
            $ownerId           = $owner->entityId();
            $this->apiEndpoint = $this->getApiEndpoint($ownerId);
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

        $hosts = $this->memberRepository()->getEventHostsForForm($this->resource);

        $this->title(__p('event::phrase.edit_event'))
            ->action(url_utility()->makeApiUrl('event/' . $this->resource->entityId()))
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
                'host'          => new UserItemCollection($hosts),
                'location'      => $this->resource->toLocationObject(),
                'location_name' => $this->resource->location_name,
            ]);
    }

    protected function isDisableEventFields(): bool
    {
        return $this->resource instanceof Model && $this->resource->isEnded();
    }
}
