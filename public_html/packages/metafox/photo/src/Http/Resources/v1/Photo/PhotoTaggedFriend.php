<?php

namespace MetaFox\Photo\Http\Resources\v1\Photo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Support\Traits\PhotoTagFriendHasExtra;
use MetaFox\Platform\Contracts\TagFriendModel as Model;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class PhotoItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PhotoTaggedFriend extends JsonResource
{
    use PhotoTagFriendHasExtra;

    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => 'photo',
            'resource_name' => 'photo_tag',
            'user'          => ResourceGate::user($this->resource->ownerEntity),
            'px'            => $this->resource->px,
            'py'            => $this->resource->py,
            'extra'         => $this->getTagFriendExtra(),
        ];
    }
}
