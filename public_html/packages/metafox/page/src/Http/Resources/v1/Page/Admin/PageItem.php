<?php

namespace MetaFox\Page\Http\Resources\v1\Page\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Http\Resources\v1\Traits\PageHasExtra;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Support\Browse\Traits\PageMember\StatisticTrait;
use MetaFox\Platform\Contracts\ResourceText;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Support\Facades\User;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PageItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PageItem extends JsonResource
{
    use PageHasExtra;
    use StatisticTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request                 $request
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request)
    {
        $pageText    = $this->resource->pageText;
        $description = MetaFoxConstant::EMPTY_STRING;

        if ($pageText instanceof ResourceText) {
            $description = parse_output()->getDescription($pageText->text_parsed);
        }

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'title'         => $this->resource->name,
            'user'          => ResourceGate::user($this->resource->userEntity),
            'description'   => $description,
            'external_link' => $this->resource->external_link,
            'is_approved'   => (bool) $this->resource->is_approved,
            'is_featured'   => (bool) $this->resource->is_featured,
            'is_sponsored'  => (bool) $this->resource->is_sponsor,
            'avatar'        => $this->resource->avatar,
            'image'         => [
                'url'       => $this->resource->avatar,
                'file_type' => 'image/*',
            ],
            'latitude'          => $this->resource->location_latitude,
            'longitude'         => $this->resource->location_longitude,
            'location_name'     => $this->resource->location_name,
            'location_address'  => $this->resource->location_address,
            'short_name'        => User::getShortName(ban_word()->clean($this->resource->name)),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'statistic'         => $this->getStatistic(),
            'extra'             => $this->getExtra(),
        ];
    }
}
