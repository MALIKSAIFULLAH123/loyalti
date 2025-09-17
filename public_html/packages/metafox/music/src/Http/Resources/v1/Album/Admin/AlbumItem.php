<?php

namespace MetaFox\Music\Http\Resources\v1\Album\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Traits\HasDescriptionTrait;
use MetaFox\Music\Models\Album as Model;
use MetaFox\Music\Support\Browse\Traits\Album\StatisticTrait;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class AlbumItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class AlbumItem extends JsonResource
{
    use StatisticTrait;
    use HasExtra;
    use HasFeedParam;
    use IsLikedTrait;
    use HasDescriptionTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $text = $this->getShortDescription(['albumText' => 'text_parsed']);

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->moduleName(),
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'description'   => htmlspecialchars_decode(parse_output()->getDescription($text)),
            'is_featured'   => (bool) $this->resource->is_featured,
            'is_sponsored'  => (bool) $this->resource->is_sponsor,
            'image'         => [
                'url'       => $this->resource->image,
                'file_type' => 'image/*',
            ],
            'statistic'     => $this->getStatistic(),
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'user'          => ResourceGate::user($this->resource->userEntity),
            'owner'         => ResourceGate::user($this->resource->ownerEntity),
            'extra'         => $this->getExtra(),
            'creation_date' => $this->resource->created_at,
            'year'          => $this->resource->year,
        ];
    }
}
