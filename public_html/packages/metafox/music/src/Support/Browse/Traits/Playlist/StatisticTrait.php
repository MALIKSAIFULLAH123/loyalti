<?php

namespace MetaFox\Music\Support\Browse\Traits\Playlist;

use MetaFox\Music\Models\Playlist;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;

/**
 * @property Playlist $resource
 */
trait StatisticTrait
{
    public function getStatistic(): array
    {
        $reactItem = $this->resource->reactItem();

        return [
            'total_like'     => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_comment'  => $reactItem instanceof HasTotalComment ? $reactItem->total_comment : 0,
            'total_share'    => $this->resource->total_share,
            'total_view'     => $this->resource->total_view,
            'total_song'     => $this->resource->total_track,
            'total_play'     => $this->resource->total_play,
            'total_reply'    => $reactItem instanceof HasTotalCommentWithReply ? $reactItem->total_reply : 0,
            'total_duration' => $this->resource->total_duration,
        ];
    }
}
