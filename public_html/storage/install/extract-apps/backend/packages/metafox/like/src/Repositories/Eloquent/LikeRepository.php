<?php

namespace MetaFox\Like\Repositories\Eloquent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use MetaFox\Like\Http\Resources\v1\Reaction\ReactionDetail;
use MetaFox\Like\Http\Resources\v1\Reaction\ReactionItemCollection;
use MetaFox\Like\Models\Like;
use MetaFox\Like\Models\LikeAgg;
use MetaFox\Like\Policies\LikePolicy;
use MetaFox\Like\Policies\ReactionPolicy;
use MetaFox\Like\Repositories\LikeRepositoryInterface;
use MetaFox\Like\Repositories\ReactionRepositoryInterface;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Repositories\AbstractRepository;
use stdClass;

/**
 * Class LikeRepository.
 * @method Like getModel()
 * @method Like find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @ignore
 * @codeCoverageIgnore
 */
class LikeRepository extends AbstractRepository implements LikeRepositoryInterface
{
    public function model(): string
    {
        return Like::class;
    }

    protected function reactionRepository(): ReactionRepositoryInterface
    {
        return resolve(ReactionRepositoryInterface::class);
    }

    public function viewLikes(User $context, array $attributes): Paginator
    {
        policy_authorize(LikePolicy::class, 'viewAny', $context);

        $limit      = $attributes['limit'];
        $itemId     = $attributes['item_id'];
        $itemType   = $attributes['item_type'];
        $reactionId = $attributes['react_id'];

        $like = new Like([
            'item_id'   => $itemId,
            'item_type' => $itemType,
        ]);

        $item = $like->item;

        if (null == $item) {
            throw (new ModelNotFoundException())->setModel($itemType);
        }

        $query = $this->getModel()->newQuery()
            ->where('item_id', $item->entityId())
            ->where('item_type', $item->entityType());

        if ($reactionId > 0) {
            $query->where('likes.reaction_id', $reactionId);
        }

        return $query->with(['user', 'reaction'])->paginate($limit);
    }

    public function viewLikeTabs(User $context, int $itemId, string $itemType): array
    {
        policy_authorize(LikePolicy::class, 'viewAny', $context);

        $likeAgg = new LikeAgg([
            'item_id'   => $itemId,
            'item_type' => $itemType,
        ]);

        $item = $likeAgg->item;

        if (null == $item) {
            throw (new ModelNotFoundException())->setModel($itemType);
        }

        $likeTabs = LikeAgg::query()->with('reaction')
            ->where('item_id', $itemId)
            ->where('item_type', $itemType)
            ->where('total_reaction', '>', 0)
            ->get();

        $totalReaction = 0;

        $likeTabs = $likeTabs->map(function (LikeAgg $item) use (&$totalReaction) {
            $reaction = $item->reaction;

            $totalReaction += $item->total_reaction;

            return [
                'id'            => $reaction->entityId(),
                'title'         => __p($reaction->title),
                'total_reacted' => $item->total_reaction,
                'icon'          => $reaction->icon,
                'color'         => "#$reaction->color",
            ];
        })->toArray();

        if (count($likeTabs) > 1) {
            $tabAll = [
                [
                    'id'            => 0,
                    'title'         => __p('core::phrase.all'),
                    'total_reacted' => $totalReaction,
                    'icon'          => null,
                    'color'         => null,
                ],
            ];

            $likeTabs = array_merge($tabAll, $likeTabs);
        }

        return $likeTabs;
    }

    /**
     * @param User         $context
     * @param HasTotalLike $content
     *
     * @return bool
     * @link \MetaFox\Like\Support\LoadMissingIsLiked
     */
    public function isLiked(User $context, HasTotalLike $content): bool
    {
        return LoadReduce::get(
            sprintf('like::isLiked(user:%s,%s:%s)', $context->userId(), $content->entityType(), $content->entityId()),
            fn () => $this->getModel()->newQuery()->where([
                'item_id'   => $content->entityId(),
                'item_type' => $content->entityType(),
                'user_id'   => $context->entityId(),
                'user_type' => $context->entityType(),
            ])->exists()
        );
    }

    public function getLike(User $context, HasTotalLike $content): ?Like
    {
        $like = $this->getModel()->newQuery()->with('reaction')
            ->where([
                'item_id'   => $content->entityId(),
                'item_type' => $content->entityType(),
                'user_id'   => $context->entityId(),
                //'user_type' => $context->entityType(),
            ])->first();

        if (!$like instanceof Like) {
            return null;
        }

        return $like;
    }

    public function createLike(User $context, int $itemId, string $itemType, int $reactionId): array
    {
        $checkItem = new Like([
            'item_id'   => $itemId,
            'item_type' => $itemType,
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ]);

        $item = $checkItem->item;

        if (null == $item) {
            abort(404, __p('core::phrase.this_post_is_no_longer_available'));
        }

        policy_authorize(LikePolicy::class, 'create', $context, $item);

        $reactions = $this->reactionRepository()->find($reactionId);

        if (!$reactions->is_active) {
            abort(403, json_encode([
                'title'   => __p('core::phrase.oops'),
                'message' => __p('like::phrase.the_reaction_has_been_deactivated'),
            ]));
        }

        $like = $this->processLike($context, $item, $reactionId);

        $item->refresh();

        $totalLike = 0;

        $reactions = $reactionsInfo = null;

        if ($item instanceof HasTotalLike) {
            $totalLike = $item->total_like;

            $reactions = $this->getMostReactions($context, $item);

            $reactionsInfo = $this->getItemReactionAggregation($context, $item);
        }

        $feedId = null;

        if ($item instanceof ActivityFeedSource) {
            try {
                /** @var Content $feed */
                $feed   = app('events')->dispatch('activity.get_feed', [$context, $item], true);
                $feedId = $feed?->entityId();
            } catch (Exception $e) {
                $feedId = null;
            }
        }

        $results = [
            'total_like'                 => $totalLike,
            'like_phrase'                => '', //@todo: implement later if FE need it
            'is_liked'                   => true,
            'feed_id'                    => $feedId,
            'most_reactions'             => $reactions !== null ? new ReactionItemCollection($reactions) : [],
            'most_reactions_information' => is_array($reactionsInfo) ? $reactionsInfo : [],
            'user_reacted'               => $like != null ? (new ReactionDetail($like->reaction)) : (new stdClass()),
            'id'                         => $like->entityId(),
            'item_id'                    => $item->entityId(),
            'item_type'                  => $item->entityType(),
        ];

        if (!policy_check(LikePolicy::class, 'view', $context, $item)) {
            Arr::set($results, 'total_like', null);
            Arr::set($results, 'most_reactions', []);
            Arr::set($results, 'most_reactions_information', []);
        }

        return $results;
    }

    private function processLike(User $context, Content $item, int $reactionId): Like
    {
        $params = [
            'item_id'   => $item->entityId(),
            'item_type' => $item->entityType(),
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ];

        $like = $this->getModel()->newQuery()->where($params)->first();

        if (!$like instanceof Like) {
            $like   = new Like();
            $params = array_merge($params, [
                'owner_id'    => $item->userId(),
                'owner_type'  => $item->userType(),
                'reaction_id' => $reactionId,
            ]);
            $like->fill($params);
            $like->save();

            return $like;
        }

        if ($reactionId != $like->reaction_id) {
            $like->fill(['reaction_id' => $reactionId]);
            $like->save();
        }

        return $like;
    }

    public function deleteLikeById(User $context, int $id): bool
    {
        $like = $this->find($id);

        policy_authorize(LikePolicy::class, 'delete', $context, $like);

        return (bool) $like->delete();
    }

    public function deleteByUser(User $context): bool
    {
        return $this->getModel()
            ->where('user_id', $context->entityId())
            ->where('user_type', $context->entityType())
            ->each(function (Like $like) {
                $like->delete();
            });
    }

    /**
     * @param User   $context
     * @param int    $itemId
     * @param string $itemType
     *
     * @return array<string,          mixed>
     * @throws AuthorizationException
     */
    public function deleteByUserAndItem(User $context, int $itemId, string $itemType): array
    {
        /** @var Like $like */
        $like = $this->getModel()->newModelInstance()
            ->with(['item'])
            ->where('item_id', $itemId)
            ->where('item_type', $itemType)
            ->where('user_id', $context->entityId())
            ->where('user_type', $context->entityType())
            ->firstOrFail();

        policy_authorize(LikePolicy::class, 'delete', $context, $like);

        $item = $like->item;
        $like->delete();
        $item->refresh();

        $totalLike     = 0;
        $reactionsInfo = $reactions = null;
        $feedId        = null;

        if ($item instanceof HasTotalLike) {
            $totalLike = $item->total_like;

            $reactions = $this->getMostReactions($context, $item);

            $reactionsInfo = $this->getItemReactionAggregation($context, $item);
        }

        if ($item instanceof ActivityFeedSource) {
            try {
                /** @var Content $feed */
                $feed   = app('events')->dispatch('activity.get_feed', [$context, $item], true);
                $feedId = $feed?->entityId();
            } catch (Exception $e) {
                $feedId = null;
            }
        }

        $results = [
            'total_like'                 => $totalLike,
            'like_phrase'                => '', //@todo: implement later if FE need it
            'is_liked'                   => false,
            'feed_id'                    => $feedId,
            'most_reactions'             => $reactions !== null ? new ReactionItemCollection($reactions) : [],
            'most_reactions_information' => is_array($reactionsInfo) ? $reactionsInfo : [],
        ];

        if (!policy_check(LikePolicy::class, 'view', $context, $item)) {
            Arr::set($results, 'total_like', null);
            Arr::set($results, 'most_reactions', []);
            Arr::set($results, 'most_reactions_information', []);
        }

        return $results;
    }

    public function getMostReactions(User $context, HasTotalLike $content, int $limit = 3): Collection
    {
        $results = new Collection();

        if (!policy_check(ReactionPolicy::class, 'viewAny', $context)) {
            return $results;
        }

        /** @var LikeAgg[]|Collection $likeAggs */
        $likeAggs = LikeAgg::query()
            ->with(['reaction'])
            ->where([
                'item_id'   => $content->entityId(),
                'item_type' => $content->entityType(),
            ])
            ->where('total_reaction', '>', 0)
            ->orderBy('total_reaction', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->limit($limit)
            ->get();

        if ($likeAggs->count() > 0) {
            foreach ($likeAggs as $likeAgg) {
                $likeAgg->reaction->setAttribute('total_reaction', $likeAgg->total_reaction);

                $results->add($likeAgg->reaction);
            }
        }

        LoadReduce::remember(sprintf('like::getItemReactionAggregation(%s,%s,%s)', $context->entityId(), $content->entityType(), $content->entityId()), fn () => $likeAggs->map(fn (LikeAgg $likeAgg) => ['id' => $likeAgg->reaction_id, 'total_reaction' => $likeAgg->total_reaction])->toArray());

        return $results;
    }

    public function getItemReactionAggregation(User $context, HasTotalLike $content, int $limit = 3): array
    {
        $aggregation = LoadReduce::remember(sprintf('like::getItemReactionAggregation(%s,%s,%s)', $context->entityId(), $content->entityType(), $content->entityId()), function () use ($context, $content, $limit) {
            return LikeAgg::query()
                ->where([
                    'item_id'   => $content->entityId(),
                    'item_type' => $content->entityType(),
                ])
                ->where('total_reaction', '>', 0)
                ->orderByDesc('total_reaction')
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get()
                ->map(fn (LikeAgg $agg) => ['id' => $agg->reaction_id, 'total_reaction' => $agg->total_reaction])
                ->toArray();
        });

        if (!is_array($aggregation) || !count($aggregation)) {
            return [];
        }

        return $aggregation;
    }
}
