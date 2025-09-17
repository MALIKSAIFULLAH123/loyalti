<?php

namespace MetaFox\Forum\Support\Browse\Traits\ForumThread;

trait StatisticTrait
{
    public function getStatistic(): array
    {
        $resource = $this->resource;

        return [
            'total_comment' => $resource->getTotalPost(),
            'total_view'    => $resource->getTotalView(),
            'total_like'    => $resource->getTotalLike(),
            'total_share'   => $resource->getTotalShare(),
        ];
    }
}
