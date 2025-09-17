<?php

namespace MetaFox\Like\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Like\Models\Reaction;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Reaction.
 * @mixin BaseRepository
 * @method Reaction getModel()
 * @method Reaction find($id, $columns = ['*'])
 */
interface ReactionRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewReactionsForAdmin(User $context, array $attributes): Paginator;

    /**
     * @param User $context
     *
     * @return Collection
     * @throws AuthorizationException
     */
    public function viewReactionsForFE(User $context): Collection;

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Reaction
     * @throws AuthorizationException
     */
    public function viewReaction(User $context, int $id): Reaction;

    /**
     * @param  int        $isActive
     * @return Collection
     */
    public function getReactionsForConfig(int $isActive = 1): Collection;
}
