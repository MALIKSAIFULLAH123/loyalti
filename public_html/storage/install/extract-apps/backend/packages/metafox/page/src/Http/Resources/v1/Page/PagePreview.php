<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Support\Browse\Traits\PageMember\StatisticTrait;
use MetaFox\Page\Support\Facade\PageMembership;
use MetaFox\Platform\Contracts\ResourceText;

/**
 * Class PagePreview.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PagePreview extends JsonResource
{
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

        $pageText    = $this->resource->pageText;
        $description = '';

        if ($pageText instanceof ResourceText) {
            $description = parse_output()->getDescription($pageText->text_parsed);
        }

        $coverExists  = !empty($this->resource->cover);
        $avatarExists = !empty($this->resource->avatar);

        return [
            'id'                   => $this->resource->entityId(),
            'module_name'          => $this->resource->entityType(),
            'resource_name'        => $this->resource->entityType(),
            'full_name'            => ban_word()->clean($this->resource->name),
            'avatar'               => $avatarExists ? $this->resource->avatar : null,
            'cover'                => $coverExists ? $this->resource->cover : null,
            'cover_photo_position' => $coverExists ? $this->resource->cover_photo_position : null,
            'statistic'            => $this->getStatistic(),
            'is_featured'          => (bool) $this->resource->is_featured,
            'is_liked'             => $this->resource->isMember($context),
            'privacy'              => $this->resource->privacy,
            'description'          => $description,
            'membership'           => PageMembership::getMembership($this->resource, $context),
            'location'             => [
                'name'      => $this->resource->location_name,
                'longitude' => $this->resource->location_longitude,
                'latitude'  => $this->resource->location_latitude,
                'address'   => $this->resource->location_address,
            ],
        ];
    }
}
