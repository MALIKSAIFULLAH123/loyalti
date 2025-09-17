<?php

namespace MetaFox\Announcement\Observers;

use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\AnnouncementView as Model;

/**
 * Class AnnouncementViewObserver.
 */
class AnnouncementViewObserver
{
    public function created(Model $model): void
    {
        $announcement = $model->announcement;
        if ($announcement instanceof Announcement) {
            $announcement->incrementTotalView();
        }
    }
}

// end stub
