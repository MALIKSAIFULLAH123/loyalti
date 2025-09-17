<?php

namespace MetaFox\Activity\Http\Resources\v1\Snooze;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Snooze;
use MetaFox\Activity\Support\Constants;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class SnoozeItem.
 *
 * @property Snooze $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SnoozeItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $owner = $this->resource->owner;

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => Feed::ENTITY_TYPE,
            'resource_name' => $this->resource->entityType(),
            'user'          => ResourceGate::user($this->resource->ownerEntity),
            'link'          => url_utility()->makeApiResourceUrl($owner->entityType(), $owner->entityId()),
            'url'           => url_utility()->makeApiResourceFullUrl($owner->entityType(), $owner->entityId()),
            'extra'         => $this->getExtra(),
        ];
    }

    protected function getExtra(): array
    {
        $policy = PolicyGate::getPolicyFor(Snooze::class);

        if (null === $policy) {
            return [];
        }

        return [
            Constants::CAN_SNOOZE_FOREVER => $policy->snoozeForever($this->resource->user, $this->resource->owner),
            Constants::CAN_UNSNOOZE       => $policy->unSnooze($this->resource->user, $this->resource->owner),
        ];
    }
}
