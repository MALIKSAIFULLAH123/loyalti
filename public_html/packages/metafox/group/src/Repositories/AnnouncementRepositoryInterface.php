<?php

namespace MetaFox\Group\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Group\Models\Announcement;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Announcement.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface AnnouncementRepositoryInterface
{
    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewAnnouncements(User $context, array $attributes): Paginator;

    /**
     * @param  User         $context
     * @param  array        $attributes
     * @return Announcement
     */
    public function createAnnouncement(User $context, array $attributes): Announcement;

    /**
     * @param User  $context
     * @param array $attributes
     */
    public function deleteAnnouncement(User $context, array $attributes);

    /**
     * @param  User  $context
     * @param  array $attributes
     * @return bool
     */
    public function hideAnnouncement(User $context, array $attributes): bool;

    /**
     * @param  int    $groupId
     * @param  int    $itemId
     * @param  string $itemType
     * @return bool
     */
    public function checkExistsAnnouncement(int $groupId, int $itemId, string $itemType): bool;

    /**
     * @param  int    $itemId
     * @param  string $itemType
     * @return void
     */
    public function deleteByItem(int $itemId, string $itemType): void;

    /**
     * @param User $context
     * @param , int $groupId
     * @return int
     */
    public function getTotalUnread(User $context, int $groupId): int;
}
