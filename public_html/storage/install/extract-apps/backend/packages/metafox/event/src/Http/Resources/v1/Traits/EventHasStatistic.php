<?php

namespace MetaFox\Event\Http\Resources\v1\Traits;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Support\ResourcePermission;
use MetaFox\Event\Models\Event as Model;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
trait EventHasStatistic
{
    use HasExtra;

    /**
     * @return array<string,           bool>
     */
    protected function getStatistic(): array
    {
        return [
            'total_like'                => $this->resource->total_like,
            'total_share'               => $this->resource->total_share,
            'total_view'                => $this->resource->total_view,
            'total_attachment'          => $this->resource->total_attachment,
            'total_member'              => $this->resource->total_member,
            'total_interested'          => $this->resource->total_interested,
            'total_pending_invite'      => $this->resource->total_pending_invites,
            'total_pending_host_invite' => $this->resource->total_pending_host_invite,
            'total_host'                => $this->resource->total_host,
        ];
    }
}
