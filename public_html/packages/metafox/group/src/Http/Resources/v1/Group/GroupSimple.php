<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Models\Group as Model;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class GroupSimple.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class GroupSimple extends JsonResource
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
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->entityType(),
            'resource_name' => $this->resource->entityType(),
            'title'         => ban_word()->clean($this->resource->name),
            'cover'         => empty($this->resource->covers) ? null : $this->resource->covers,
            'short_name'    => UserFacade::getShortName(ban_word()->clean($this->resource->name)),
            'link'          => $this->resource->toLink(),
            'router'        => $this->resource->toRouter(),
            'url'           => $this->resource->toUrl(),
        ];
    }
}
