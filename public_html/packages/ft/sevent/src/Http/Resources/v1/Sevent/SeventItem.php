<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Foxexpert\Sevent\Models\Sevent;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;

/**
 * Class SeventItem.
 * @property Sevent $resource
 */
class SeventItem extends SeventDetail
{
    use HasStatistic;
    use HasExtra;
    use IsLikedTrait;
    use IsFriendTrait;

    /**
     * @return array<string, mixed>
     */
    public function getStatistic(): array
    {
        return [
            'total_like'       => $this->resource->total_like,
            'total_view'       => $this->resource->total_view,
            'total_share'      => $this->resource->total_share,
            'total_comment'    => $this->resource->total_comment, // @todo improve or remove.
            'total_attachment' => $this->resource->total_attachment,
        ];
    }
}
