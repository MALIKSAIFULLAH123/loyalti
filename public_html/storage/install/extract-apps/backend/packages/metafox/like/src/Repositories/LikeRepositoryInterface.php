<?php

namespace MetaFox\Like\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Like\Models\Like;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Like.
 * @mixin BaseRepository
 * @method Like getModel()
 * @method Like find($id, $columns = ['*'])
 */
interface LikeRepositoryInterface
{
    /**
     * @param  User                  $context
     * @param  array<string, mixed>  $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewLikes(User $context, array $attributes): Paginator;

    /**
     * @param  User    $context
     * @param  int     $itemId
     * @param  string  $itemType
     *
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function viewLikeTabs(User $context, int $itemId, string $itemType): array;

    /**
     * @param  User    $context
     * @param  int     $itemId
     * @param  string  $itemType
     * @param  int     $reactionId
     *
     * @return array<string,          mixed>
     * @throws AuthorizationException
     */
    public function createLike(User $context, int $itemId, string $itemType, int $reactionId): array;

    /**
     * @param  User  $context
     * @param  int   $id
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteLikeById(User $context, int $id): bool;

    /**
     * @param  User  $context
     *
     * @return bool
     */
    public function deleteByUser(User $context): bool;

    /**
     * @param  User    $context
     * @param  int     $itemId
     * @param  string  $itemType
     *
     * @return array<string,          mixed>
     * @throws AuthorizationException
     */
    public function deleteByUserAndItem(User $context, int $itemId, string $itemType): array;

    public function getLike(User $context, HasTotalLike $content): ?Like;

    public function isLiked(User $context, HasTotalLike $content): bool;

    public function getMostReactions(User $context, HasTotalLike $content, int $limit = 3): Collection;

    /**
     * @param User         $context
     * @param HasTotalLike $content
     * @param int          $limit
     * @return array
     */
    public function getItemReactionAggregation(User $context, HasTotalLike $content, int $limit = 3): array;
}
