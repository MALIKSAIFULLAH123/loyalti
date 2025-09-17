<?php

namespace MetaFox\Page\Support\Browse\Traits\PageMember;

trait StatisticTrait
{
    /**
     * @return array
     */
    public function getStatistic(): array
    {
        return [
            'total_like'  => $this->resource->total_member,
            'total_admin' => $this->resource->total_admin,
        ];
    }
}
