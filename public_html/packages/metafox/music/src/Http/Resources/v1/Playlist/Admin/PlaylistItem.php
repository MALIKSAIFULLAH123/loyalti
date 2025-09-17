<?php

namespace MetaFox\Music\Http\Resources\v1\Playlist\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Traits\HasDescriptionTrait;
use MetaFox\Music\Models\Playlist as Model;
use MetaFox\Music\Support\Browse\Traits\Playlist\ExtraTrait;
use MetaFox\Music\Support\Browse\Traits\Playlist\StatisticTrait;
use MetaFox\Platform\Facades\ResourceGate;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class PlaylistItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class PlaylistItem extends JsonResource
{
    use StatisticTrait;
    use ExtraTrait;
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
        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->moduleName(),
            'resource_name'     => $this->resource->entityType(),
            'name'              => $this->resource->name,
            'description'       => htmlspecialchars_decode(parse_output()->getDescription($this->getShortDescription())),
            'statistic'         => $this->getStatistic(),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'creation_date'     => $this->resource->created_at,
            'extra'             => $this->getExtra(),
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
