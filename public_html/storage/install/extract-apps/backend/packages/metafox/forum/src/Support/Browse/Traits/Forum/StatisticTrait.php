<?php

namespace MetaFox\Forum\Support\Browse\Traits\Forum;

use MetaFox\Forum\Support\Facades\Forum as ForumFacade;

trait StatisticTrait
{
    public function getStatistic(): array
    {
        $resource = $this->resource;

        return [
            'total_thread'     => $resource->total_thread,
            'total_all_thread' => $resource->total_thread,
            'total_sub_forum'  => $resource->total_sub,
            'total_replies'    => $resource->total_comment,
        ];
    }
}
