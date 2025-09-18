<?php

namespace MetaFox\Like\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Like\Models\Reaction;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface ReactionAdminRepositoryInterface.
 * @mixin BaseRepository
 * @method Reaction getModel()
 * @method Reaction find($id, $columns = ['*'])
 */
interface ReactionAdminRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewReactions(User $context, array $attributes): Paginator;

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Reaction
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function createReaction(User $context, array $attributes): Reaction;

    /**
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Reaction
     * @throws AuthorizationException
     */
    public function updateReaction(User $context, int $id, array $attributes): Reaction;

    /**
     * @param User  $context
     * @param array $orders
     */
    public function ordering(User $context, array $orders): void;

    /**
     * @param User $context
     * @param int  $id
     * @param int  $newReactionId
     * @return void
     */
    public function deleteReaction(User $context, int $id, int $newReactionId): void;

    /**
     * @param Reaction $reaction
     * @return void
     */
    public function deleteAllBelongTo(Reaction $reaction): void;

    /**
     * @param Reaction $reaction
     * @param int      $newReactionId
     * @return void
     */
    public function moveToNewReaction(Reaction $reaction, int $newReactionId): void;

    /**
     * @param Reaction $reaction
     * @param int      $newReactionId
     * @return void
     */
    public function deleteOrMoveToNewReaction(Reaction $reaction, int $newReactionId): void;

    public function lastOrdering(): int;

    /**
     * @return int
     */
    public function getTotalReactionActive(): int;

    /**
     * @return void
     */
    public function checkActiveReaction(): void;
}
