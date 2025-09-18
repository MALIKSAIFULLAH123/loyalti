<?php

namespace MetaFox\Group\Observers;

use MetaFox\Group\Models\Request;
use MetaFox\Group\Support\Browse\Scopes\Request\StatusScope;

/**
 * Class RequestObserver.
 * @ignore
 */
class RequestObserver
{
    public function created(Request $request): void
    {
        if ($request->status_id == Request::STATUS_PENDING) {
            $request->group->incrementAmount('total_pending_request');
        }
    }

    public function updated(Request $request): void
    {
        $this->handleUpdateStatistic($request);
    }

    public function deleted(Request $request)
    {
        if ($request->status_id == StatusScope::STATUS_PENDING) {
            $request->group->decrementAmount('total_pending_request');
        }
        
        $request->answers()->delete();
    }

    private function handleUpdateStatistic(Request $request): void
    {
        if (!$request->isDirty('status_id')) {
            return;
        }

        if ($request->status_id == StatusScope::STATUS_PENDING) {
            return;
        }

        $request->group->decrementAmount('total_pending_request');
    }
}
