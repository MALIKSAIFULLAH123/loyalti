<?php

namespace MetaFox\Event\Http\Resources\v1\Event;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Event\Models\Event;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;

/**
 * Class EventPropertiesSchema.
 * @property ?Event $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class EventPropertiesSchema extends JsonResource
{
    use HasHashtagTextTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $user           = new UserPropertiesSchema($this->resource?->user);
        $userProperties = Arr::dot($user->toArray($request), 'user_');
        $userProperties = Arr::undot($userProperties);

        if (!$this->resource instanceof Event) {
            return array_merge($this->resourcesDefault(), $userProperties);
        }

        return array_merge([
            'id'                 => $this->resource->entityId(),
            'title'              => ban_word()->clean($this->resource->name),
            'privacy'            => $this->resource->privacy,
            'description'        => $this->resource->getDescription(),
            'start_time'         => $this->resource->start_time,
            'end_time'           => $this->resource->end_time,
            'image'              => $this->resource->image,
            'event_url'          => $this->resource->event_url,
            'creation_date'      => $this->getCreationDate(),
            'modification_date'  => $this->getModificationDate(),
            'is_online'          => $this->resource->is_online,
            'status'             => $this->resource->getStatus(),
            'link'               => $this->resource->toLink(),
            'url'                => $this->resource->toUrl(),
            'address'            => $this->resource->location_address ?? $this->resource->location_name,
            'lat'                => $this->resource->location_latitude,
            'lng'                => $this->resource->location_longitude,
            'short_name'         => $this->resource->country_iso,
            'structure_location' => $this->getStructureLocation(),
        ], $userProperties);
    }

    protected function getCreationDate(): string
    {
        return Carbon::parse($this->resource->created_at)->format('c');
    }
    protected function getModificationDate(): string
    {
        return Carbon::parse($this->resource->updated_at)->format('c');
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                 => null,
            'title'              => null,
            'privacy'            => null,
            'description'        => null,
            'start_time'         => null,
            'end_time'           => null,
            'image'              => null,
            'event_url'          => null,
            'creation_date'      => null,
            'modification_date'  => null,
            'is_online'          => null,
            'status'             => null,
            'link'               => null,
            'url'                => null,
            'address'            => null,
            'lat'                => null,
            'lng'                => null,
            'short_name'         => null,
            'structure_location' => null,
        ];
    }

    protected function getStructureLocation(): array
    {
        if (!$this->resource instanceof Event) {
            return [];
        }

        if ($this->resource->is_online) {
            return [
                '@type' => 'VirtualLocation',
                'url'   => $this->resource->event_url,
            ];
        }

        $address = ['@type' => 'PostalAddress'];

        if ($this->resource->country_iso) {
            Arr::set($address, 'addressCountry', $this->resource->country_iso);
        }

        if ($this->resource->location_name) {
            Arr::set($address, 'streetAddress', $this->resource->location_address ?? $this->resource->location_name);
        }

        return [
            '@type'   => 'Place',
            'address' => $address,
        ];
    }
}
