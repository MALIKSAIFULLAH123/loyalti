<?php

namespace MetaFox\Photo\Http\Resources\v1\PhotoGroup;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Photo\Models\PhotoGroup as Model;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Photo\Support\Browse\Traits\PhotoGroup\ExtraTrait;
use MetaFox\Photo\Support\Facades\PhotoGroup;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;

/**
 * |--------------------------------------------------------------------------
 * | Resource Detail
 * |--------------------------------------------------------------------------
 * | stub: /packages/resources/detail.stub
 * | @link https://laravel.com/docs/8.x/eloquent-resources#concept-overview.
 **/

/**
 * Class PhotoGroupDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PhotoGroupDetail extends JsonResource
{
    use HasStatistic;
    use HasFeedParam;
    use IsLikedTrait;
    use ExtraTrait;

    /**
     * @return array<string, mixed>
     */
    public function getStatistic(): array
    {
        $default = [
            'total_like'    => $this->resource->total_like,
            'total_comment' => $this->resource->total_comment,
            'total_view'    => $this->resource->total_view,
            'total_photo'   => 0,
            'total_item'    => $this->resource->total_item,
            'total_video'   => 0,
        ];

        return array_merge($default, $this->resource->statistic?->toAggregateData() ?? []);
    }

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

        [$items, $total, $remain] = PhotoGroup::getMediaItems($this->resource);

        if ($this->resource->isApproved()) {
            $total = $this->resource->total_item;
        }

        return [
            'id'            => $this->resource->entityId(),
            'module_name'   => $this->resource->moduleName(),
            'resource_name' => $this->resource->entityType(),
            'total_item'    => $total,
            'remain_photo'  => $total,
            'album_id'      => $this->resource->album_id,
            'description'   => $this->resource->content,
            'is_liked'      => $this->isLike($context, $this->resource),
            'user'          => ResourceGate::user($this->resource->userEntity),
            'owner'         => ResourceGate::user($this->resource->ownerEntity),
            'photos'        => $items,
            'link'          => $this->resource->toLink(),
            'url'           => $this->resource->toUrl(),
            'statistic'     => $this->getStatistic(),
            'extra'         => $this->getExtra(),
            'feed_param'    => $this->getFeedParams(),
        ];
    }
}
