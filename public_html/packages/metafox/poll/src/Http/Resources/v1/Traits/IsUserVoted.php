<?php

namespace MetaFox\Poll\Http\Resources\v1\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Contracts\User;

/**
 * @mixin JsonResource
 */
trait IsUserVoted
{
    public function isUserVoted(User $context): bool
    {
        $votedUserIds = $this->resource->results->pluck('user_id')->toArray();

        return in_array($context->entityId(), $votedUserIds);
    }
}
