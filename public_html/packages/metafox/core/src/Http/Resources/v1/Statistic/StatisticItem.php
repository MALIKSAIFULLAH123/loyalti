<?php

namespace MetaFox\Core\Http\Resources\v1\Statistic;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Core\Models\StatsContent as Model;
use MetaFox\Core\Repositories\StatsContentTypeAdminRepositoryInterface;

/**
 * Class StatisticItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class StatisticItem extends JsonResource
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
        $typeData = $this->getStatsTypeData($this->resource->name);
        $link     = $this->resource->value ? Arr::get($typeData, 'to') : null;

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'core',
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'period'        => $this->resource->period,
            'value'         => $this->resource->value,
            'label'         => $this->resource->label,
            'icon'          => Arr::get($typeData, 'icon', 'ico-square-o'),
            'url'           => $link ? url_utility()->makeApiFullUrl($link) : null,
        ];
    }

    protected function getStatsTypeData(string $type): array
    {
        $allTypes = resolve(StatsContentTypeAdminRepositoryInterface::class)->getAllKeyByName();

        return Arr::get($allTypes, $type, []);
    }
}
