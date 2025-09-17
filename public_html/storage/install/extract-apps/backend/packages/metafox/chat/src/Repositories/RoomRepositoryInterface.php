<?php

namespace MetaFox\Chat\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Chat\Models\Room;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Room.
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface RoomRepositoryInterface
{
    /**
     * Create a room.
     *
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Room
     * @throws AuthorizationException
     * @see StoreBlockLayoutRequest
     */
    public function createChatRoom(User $context, array $attributes): Room;

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @throws AuthorizationException
     */
    public function viewRooms(User $context, User $owner, array $attributes): Paginator;

    public function viewRoom(User $context, int $id): Room;

    /**
     * Delete a room.
     *
     * @param User $user
     * @param int  $id
     *
     * @return int
     * @throws AuthorizationException
     */
    public function deleteRoom(User $user, int $id): int;
}
