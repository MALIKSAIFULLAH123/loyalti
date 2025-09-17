<?php

namespace MetaFox\Chat\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Chat\Models\Message;
use MetaFox\Core\Models\Attachment;
use MetaFox\Platform\Contracts\User;

/**
 * Interface BlogRepositoryInterface.
 * @method Message find($id, $columns = ['*'])
 * @method Message getModel()
 */
interface MessageRepositoryInterface
{
    /**
     * @param  User  $context
     * @param  array $attributes
     * @return mixed
     */
    public function viewMessages(User $context, array $attributes): Paginator;

    public function viewMessage(User $context, int $id): Message;

    /**
     * Create a room.
     *
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Message|null
     * @throws AuthorizationException
     * @see StoreBlockLayoutRequest
     */
    public function addMessage(User $context, array $attributes): Message|null;

    public function updateMessage(User $context, int $id, array $attributes): Message;

    public function getRoomLastMessage(int $userId, int $roomId): Message|null;

    public function reactMessage(User $context, int $id, array $param): Message;

    public function normalizeReactions(array|null $reactions): array;

    public function downloadAttachment(User $context, int $id): Attachment;

    public function getRelatedMessages(Message $message, array $attributes): array;

    public function deleteOrMoveReaction(int $reactionId, ?int $newReactionId = null): void;

    public function performActionDeleteOrMoveReaction(int $reactionId, ?int $newReactionId = null): void;
}
