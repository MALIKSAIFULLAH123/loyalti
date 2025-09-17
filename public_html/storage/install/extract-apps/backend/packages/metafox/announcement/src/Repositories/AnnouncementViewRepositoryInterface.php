<?php

namespace MetaFox\Announcement\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Announcement\Models\AnnouncementView as Model;
use MetaFox\Platform\Contracts\User;

/**
 * Interface AnnouncementView.
 *
 * @method Model getModel()
 * @method Model find($id, $columns = ['*'])
 */
interface AnnouncementViewRepositoryInterface
{
    /**
     * @param  User                 $context
     * @param  array<string, mixed> $params
     * @return Paginator
     */
    public function viewAnnouncementViews(User $context, array $params): Paginator;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $params
     * @return Model
     */
    public function createAnnouncementView(User $context, array $params): Model;

    /**
     * @param  int  $userId
     * @param  int  $announcementId
     * @return bool
     */
    public function checkViewAnnouncement(int $userId, int $announcementId): bool;

    /**
     * @param  int   $userId
     * @return array
     */
    public function getViewedIdsByUserId(int $userId): array;
}
