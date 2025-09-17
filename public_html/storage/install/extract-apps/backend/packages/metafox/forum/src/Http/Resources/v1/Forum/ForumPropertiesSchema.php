<?php

namespace MetaFox\Forum\Http\Resources\v1\Forum;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use MetaFox\Forum\Models\Forum;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;

/**
 * Class ForumPropertiesSchema.
 * @property ?Forum $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ForumPropertiesSchema extends JsonResource
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
        if (!$this->resource instanceof Forum) {
            return $this->resourcesDefault();
        }
        $resource = $this->resource;

        $title = parse_output()->parse($resource->toTitle());

        return [
            'id'                => $resource->entityId(),
            'title'             => $title,
            'description'       => $resource->description,
            'creation_date'     => Carbon::parse($resource->getCreatedAt())->format('c'),
            'modification_date' => Carbon::parse($resource->getUpdatedAt())->format('c'),
            'is_closed'         => $resource->is_closed,
            'is_opened'         => !$resource->is_closed,
            'link'              => $resource->toLink(),
            'url'               => $resource->toUrl(),
        ];
    }

    protected function resourcesDefault(): array
    {
        return [
            'id'                => null,
            'title'             => null,
            'description'       => null,
            'statistic'         => null,
            'creation_date'     => null,
            'modification_date' => null,
            'is_closed'         => null,
            'is_opened'         => null,
            'sub_link'          => null,
            'link'              => null,
            'url'               => null,
        ];
    }
}
