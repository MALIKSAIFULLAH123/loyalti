<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Support\Browse\Traits\Group\StatisticTrait;
use MetaFox\Group\Support\Membership;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;

/**
 * Class GroupPreview.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class GroupPreview extends JsonResource
{
    use HasExtra;
    use StatisticTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $context = user();

        $groupText        = $this->resource->groupText;
        $shortDescription = '';
        if ($groupText) {
            $shortDescription = parse_output()->getDescription($groupText->text_parsed);
        }

        return [
            'id'                   => $this->resource->entityId(),
            'module_name'          => $this->resource->entityType(),
            'resource_name'        => $this->resource->entityType(),
            'full_name'            => ban_word()->clean($this->resource->name),
            'avatar'               => $this->resource->avatar,
            'cover'                => $this->resource->cover,
            'cover_photo_position' => empty($this->resource->cover) ? null : $this->resource->cover_photo_position,
            'statistic'            => $this->getStatistic(),
            'membership'           => Membership::getMembership($this->resource, $context),
            'privacy'              => $this->resource->privacy,
            'description'          => $shortDescription,
            'location'             => [
                'name'      => $this->resource->location_name,
                'longitude' => $this->resource->location_longitude,
                'latitude'  => $this->resource->location_latitude,
                'address'   => $this->resource->location_address,
            ],
        ];
    }
}
