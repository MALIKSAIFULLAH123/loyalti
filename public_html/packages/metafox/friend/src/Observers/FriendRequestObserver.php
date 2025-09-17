<?php

namespace MetaFox\Friend\Observers;

use MetaFox\Friend\Models\FriendRequest;

/**
 * Class FriendRequestObserver.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class FriendRequestObserver
{
    public function created(FriendRequest $model): void
    {
        $this->clearCache();
    }

    public function updated(FriendRequest $model): void
    {
        if ($model->is_deny == FriendRequest::IS_DENY) {
            $requestClass = get_class($model);
            event("eloquent.deleted: $requestClass", $model);
        }
        $this->clearCache();
    }

    public function deleted(FriendRequest $model): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        // Todo here.
    }
}

// end stub
