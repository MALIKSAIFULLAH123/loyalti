<?php

namespace MetaFox\Poll\Http\Resources\v1\Traits;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\AppSetting\ResourceExtraTrait;
use MetaFox\Poll\Support\ResourcePermission;

/**
 * @property Content $resource
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
trait PollHasExtra
{
    use ResourceExtraTrait;

    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    protected function getPollExtra(): array
    {
        $context = user();

        $canVote     = $context->can('vote', [$this->resource, $this->resource]);

        $permissions = $this->getResourceExtra($this->resource, $context);

        $morePermissions = [
            ResourcePermission::CAN_VOTE_WITH_CLOSE_TIME    => !$this->resource->is_closed,
            ResourcePermission::CAN_VOTE                    => !$this->resource->is_closed && $canVote,
            ResourcePermission::CAN_CHANGE_VOTE             => $this->canChangeOwnVote($this->resource, $context),
            ResourcePermission::CAN_VIEW_RESULT             => $context->can('viewResult', [$this->resource, $this->resource]),
            ResourcePermission::CAN_VIEW_RESULT_BEFORE_VOTE => $context->can('viewResultBeforeVote', [$this->resource, $this->resource]),
            ResourcePermission::CAN_VIEW_RESULT_AFTER_VOTE  => $context->can('viewResultAfterVote', [$this->resource, $this->resource]),
            ResourcePermission::CAN_VIEW_HIDE_VOTE          => $context->can('viewHiddenVote', [$this->resource]),
        ];

        return array_merge($permissions, $morePermissions);
    }

    /**
     * @param  Content $resource
     * @param  User    $user
     * @return bool
     */
    protected function canChangeOwnVote(Content $resource, User $user): bool
    {
        return $user->can('changeVote', [$resource, $resource]);
    }
}
