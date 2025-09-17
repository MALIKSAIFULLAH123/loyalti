<?php
namespace MetaFox\Forum\Observers;

use Illuminate\Support\Facades\Notification;
use MetaFox\Forum\Models\Moderator;

class ModeratorObserver
{
    public function created(Moderator $moderator)
    {
        $response = $moderator->toAddModeratorNotification();

        Notification::send(...$response);
    }

    public function deleted(Moderator $moderator)
    {
        app('events')->dispatch('notification.delete_mass_notification_by_item', [$moderator], true);
    }
}
