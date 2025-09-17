<?php

namespace MetaFox\Announcement\Repositories;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\AnnouncementClose;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface AnnouncementClose.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface AnnouncementCloseRepositoryInterface
{
    /**
     * @param  User              $context
     * @param  Announcement      $resource
     * @return AnnouncementClose
     */
    public function closeAnnouncement(User $context, Announcement $resource): AnnouncementClose;

    /**
     * @param  User    $context
     * @return Builder
     */
    public function getCloseAnnouncements(User $context): Builder;
}
