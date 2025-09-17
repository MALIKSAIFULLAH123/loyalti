<?php

namespace MetaFox\Announcement\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface AnnouncementRepositoryInterface.
 * @mixin BaseRepository
 */
interface AnnouncementRepositoryInterface
{
    /**
     * @param User  $context
     * @param array $attributes
     * @return array
     */
    public function viewAnnouncementsWithLastId(User $context, array $attributes): array;

    /**
     * @param  User      $context
     * @param  array     $attributes
     * @return Paginator
     */
    public function viewAnnouncements(User $context, array $attributes): Paginator;

    /**
     * @param  User         $context
     * @param  int          $id
     * @return Announcement
     * @throw AuthorizationException
     */
    public function viewAnnouncement(User $context, int $id): Announcement;

    /**
     * @param  User                 $context
     * @param  array<string, mixed> $attributes
     * @return Announcement
     * @throw AuthorizationException
     */
    public function createAnnouncement(User $context, array $attributes): Announcement;

    /**
     * @param  User                 $context
     * @param  int                  $id
     * @param  array<string, mixed> $attributes
     * @return Announcement
     * @throw AuthorizationException
     */
    public function updateAnnouncement(User $context, int $id, array $attributes): Announcement;

    /**
     * @param  User $context
     * @param  int  $id
     * @return int
     * @throw AuthorizationException
     */
    public function deleteAnnouncement(User $context, int $id): int;

    /**
     * @param  User         $context
     * @param  int          $id
     * @return Announcement
     * @throw AuthorizationException
     */
    public function hideAnnouncement(User $context, int $id): Announcement;

    /**
     * @param  User                   $context
     * @param  array<string, mixed>   $attributes
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewAnnouncementsForAdmin(User $context, array $attributes): Paginator;

    /**
     * @param  User         $context
     * @param  int          $id
     * @return Announcement
     */
    public function activateAnnouncement(User $context, int $id): Announcement;

    /**
     * @param  User         $context
     * @param  int          $id
     * @return Announcement
     */
    public function deactivateAnnouncement(User $context, int $id): Announcement;

    public function getTotalUnread(User $context): int;

    /**
     * @param  User         $context
     * @param  int          $id
     * @return Announcement
     */
    public function closeAnnouncement(User $context, int $id): Announcement;
}
