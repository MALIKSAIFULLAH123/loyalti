<?php

namespace MetaFox\Activity\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Activity\Models\ActivitySchedule;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface ActivitySchedule.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ActivityScheduleRepositoryInterface
{
    /**
     * @param  User             $context
     * @param  int              $id
     * @return ActivitySchedule
     */
    public function getForEdit(User $context, int $id): ActivitySchedule;

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewScheduledPosts(User $context, User $owner, ?array $attributes = []): Paginator;

    /**
     * View a scheduled post.
     *
     * @param User $context
     * @param int  $id
     *
     * @return ActivitySchedule
     * @throws AuthorizationException
     */
    public function viewScheduledPost(User $context, int $id): ActivitySchedule;

    /**
     * @param  ActivitySchedule $schedule
     * @param  int|null         $limit
     * @return array
     */
    public function getScheduledTaggedFriend(ActivitySchedule $schedule, ?int $limit = 3): array;

    /**
     * @param  ActivitySchedule $schedule
     * @param  bool             $toForm
     * @return array|null
     */
    public function getScheduledEmbedObject(ActivitySchedule $schedule, bool $toForm = false): ?array;

    /**
     * @return array
     */
    public function monitorScheduledPost(): array;

    /**
     * @param  int      $isTemp
     * @param  int|null $limit
     * @return void
     */
    public function sendScheduledPost(int $isTemp, ?int $limit = 10): void;
    /**
     * @param  User                   $user
     * @param  int                    $id
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteScheduledPost(User $user, int $id): bool;

    /**
     * @param  User $user
     * @param  int  $id
     * @return bool
     */
    public function sendNowScheduledPost(User $user, int $id): bool;

    /**
     * Update a feed.
     *
     * @param User                 $context
     * @param User                 $user
     * @param int                  $id
     * @param array<string, mixed> $params
     *
     * @return ActivitySchedule
     * @throws AuthorizationException
     */
    public function updateScheduledPost(User $context, User $user, int $id, array $params): ActivitySchedule;

    /**
     * @param  User             $context
     * @param  User             $user
     * @param  User             $owner
     * @param  array            $params
     * @return ActivitySchedule
     */
    public function createSchedule(User $context, User $user, User $owner, array $params): ActivitySchedule;
}
