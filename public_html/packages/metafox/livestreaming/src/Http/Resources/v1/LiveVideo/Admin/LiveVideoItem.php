<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class LiveVideoItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class LiveVideoItem extends JsonResource
{
    use HasExtra;
    use RepoTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        $isApproved       = $this->resource->is_approved;
        $shortDescription = '';
        $thumbs           = $this->getLiveVideoAdminRepository()->getThumbnailPlayback($this->resource->entityId());
        if ($this->resource->liveVideoText) {
            $shortDescription = parse_output()->getDescription($this->resource->liveVideoText->text);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => $this->resource->moduleName(),
            'resource_name'     => $this->resource->entityType(),
            'title'             => $this->resource->toTitle(),
            'duration'          => $this->getLiveVideoRepository()->getDuration($this->resource),
            'description'       => $shortDescription,
            'is_approved'       => $isApproved,
            'is_sponsored'      => $this->resource->is_sponsor,
            'is_streaming'      => (bool) $this->resource->is_streaming,
            'is_featured'       => $this->resource->is_featured,
            'is_landscape'      => $this->resource->is_landscape,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'tags'              => $this->resource->tags,
            'is_sponsored_feed' => $this->resource->sponsor_in_feed,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'extra'             => $this->getExtra(),
            'playback'          => $this->resource->playback,
            'video_url'         => $this->getLiveVideoRepository()->getVideoPlayback($this->resource->entityId()),
            'thumbnail'         => [
                'url'       => Arr::get($thumbs, 'origin'),
                'file_type' => 'image/*',
            ],
        ];
    }

}
