<?php

namespace MetaFox\Event\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Event\Models\Event;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface EventRepositoryInterface.
 * @mixin BaseRepository
 * @mixin CollectTotalItemStatTrait
 * @method Event getModel()
 * @method Event find($id, $columns = ['*'])()
 */
interface EventRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Event
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function createEvent(User $context, User $owner, array $attributes): Event;

    /**
     * @param  User         $context
     * @param  User         $owner
     * @param  array<mixed> $attributes
     * @return Paginator
     */
    public function viewEvents(User $context, User $owner, array $attributes): Paginator;

    /**
     * @param  User  $context
     * @param  int   $id
     * @return Event
     */
    public function getEvent(User $context, int $id): Event;

    /**
     * @param  User         $context
     * @param  int          $id
     * @param  array<mixed> $attributes
     * @return Event
     */
    public function updateEvent(User $context, int $id, array $attributes): Event;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function deleteEvent(User $context, int $id): bool;

    /**
     * @param  int       $limit
     * @return Paginator
     */
    public function findSponsor(int $limit = 4): Paginator;

    /**
     * @param  int       $limit
     * @return Paginator
     */
    public function findFeature(int $limit = 4): Paginator;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Content
     * @throws AuthorizationException
     */
    public function approve(User $context, int $id): Content;

    /**
     * @param  Event $event
     * @return array
     */
    public function getExtraStatistics(User $context, Event $event): array;

    /**
     * @param  User  $context
     * @param  User  $user
     * @param  int   $id
     * @return array
     */
    public function getUserExtraStatistics(User $context, User $user, int $id): array;

    /**
     * @param  int  $userId
     * @return void
     */
    public function deleteUserData(int $userId): void;

    /**
     * @param  int  $eventId
     * @return void
     */
    public function handleSendInviteNotification(int $eventId): void;

    /**
     * @param  User  $context
     * @param  int   $eventId
     * @param  array $attributes
     * @return void
     */
    public function massEmail(User $context, int $eventId, array $attributes): void;

    /**
     * @param  User        $user
     * @param  int         $eventId
     * @return string|null
     */
    public function getLatestMassEmailByUser(User $user, int $eventId): ?string;

    /**
     * @param  Event $event
     * @param  User  $context
     * @return array
     */
    public function toPendingNotifiables(Event $event, User $context): array;

    /**
     * @return Collection
     */
    public function getMissingLocationEvent(): Collection;

    /**
     * @param  User  $context
     * @param  int   $eventId
     * @param  array $attributes
     * @return array
     */
    public function updateBanner(User $context, int $eventId, array $attributes): array;

    /**
     * @param  User $context
     * @param  int  $id
     * @return bool
     */
    public function removeBanner(User $context, int $id): bool;

    /**
     * @param  User  $user
     * @param  Event $event
     * @return Event
     */
    public function updateLastMassInvite(User $user, Event $event): Event;

    /**
     * @param  array $data
     * @return void
     */
    public function sendEventDeletedNotifications(array $data): void;
}
