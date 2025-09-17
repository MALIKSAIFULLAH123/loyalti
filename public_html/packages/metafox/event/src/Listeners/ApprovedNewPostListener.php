<?php

namespace MetaFox\Event\Listeners;

use Illuminate\Support\Facades\Notification;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Notifications\NewEventDiscussion;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * Class ApprovedNewPostListener.
 * @ignore
 * @codeCoverageIgnore
 */
class ApprovedNewPostListener
{
    /**
     * @param  Content $item
     * @param  User    $resource
     * @return void
     */
    public function handle(Content $item, User $resource): void
    {
        if (!$resource instanceof Event) {
            return;
        }

        if (!$item->isApproved()) {
            return;
        }

        $authorizers = $resource->authorizers()
            ->with(['user'])
            ->get();

        $notification = new NewEventDiscussion($item);

        foreach ($authorizers as $authorizer) {
            if ($item->userId() == $authorizer->userId()) {
                continue;
            }

            $notificationParams = [$authorizer->user, $notification];

            Notification::send(...$notificationParams);
        }
    }
}
