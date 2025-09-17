<?php

namespace MetaFox\Activity\Http\Resources\v1\Feed;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Traits\FeedSupport;
use MetaFox\Platform\Contracts\HasLocationCheckin;
use MetaFox\User\Http\Resources\v1\User\UserPropertiesSchema;

/**
 * Class FeedItem.
 * Do not use Gate in here to improve performance.
 *
 * @property Feed $resource
 */
class FeedPropertiesSchema extends JsonResource
{
    use FeedSupport;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        $user           = new UserPropertiesSchema($this->resource?->user);
        $userProperties = Arr::dot($user->toArray($request), 'user_');
        $userProperties = Arr::undot($userProperties);

        if (!$this->resource instanceof Feed) {
            return array_merge($this->resourcesDefault(), $userProperties);
        }

        return array_merge([
            'id'                 => $this->resource->entityId(),
            'module_name'        => $this->resource->entityType(),
            'status'             => $this->getParsedContent(),
            'title_seo'          => $this->resource->seo_title,
            'creation_date'      => Carbon::parse($this->resource->created_at)->format('c'),
            'modification_date'  => Carbon::parse($this->resource->updated_at)->format('c'),
            'link'               => $this->resource->toLink(),
            'url'                => $this->resource->toUrl(),
            'structure_location' => $this->getStructureLocation(),
        ], $userProperties);
    }

    protected function getStructureLocation(): array
    {
        if (!$this->resource instanceof Feed) {
            return [];
        }

        $item = $this->getActionResource();

        if (!$item instanceof HasLocationCheckin) {
            return [];
        }

        $address = ['@type' => 'PostalAddress'];

        if ($item->location_address) {
            Arr::set($address, 'streetAddress', $item->location_address);
        }

        return [
            '@type'   => 'Place',
            'address' => $address,
        ];
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                 => null,
            'module_name'        => null,
            'status'             => null,
            'title_seo'          => null,
            'location'           => null,
            'creation_date'      => null,
            'modification_date'  => null,
            'link'               => null,
            'url'                => null,
            'structure_location' => null,
        ];
    }
}
