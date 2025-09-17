<?php

namespace MetaFox\Group\Support\Browse\Traits\Group;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Group\Support\Facades\Group;

trait StatisticTrait
{
    /**
     * @throws AuthenticationException
     */
    public function getStatistic(): array
    {
        return [
            'total_member'           => Group::getTotalMemberByPrivacy($this->resource),
            'total_pending_requests' => $this->resource->total_pending_request,
            'total_admin'            => $this->resource->total_admin,
        ];
    }
}
