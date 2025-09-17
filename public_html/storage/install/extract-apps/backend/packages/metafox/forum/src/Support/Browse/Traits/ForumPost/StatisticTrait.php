<?php

namespace MetaFox\Forum\Support\Browse\Traits\ForumPost;

trait StatisticTrait
{
    public function getStatistic(): array
    {
        $resource = $this->resource;

        return [
            'total_like'       => $resource->getTotalLike(),
            'total_attachment' => $resource->getTotalAttachment(),
            'total_share'      => $resource->getTotalShare(),
        ];
    }
}
