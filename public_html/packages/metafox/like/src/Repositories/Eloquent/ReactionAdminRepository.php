<?php

namespace MetaFox\Like\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Like\Jobs\DeleteReactionJob;
use MetaFox\Like\Models\Like;
use MetaFox\Like\Models\LikeAgg;
use MetaFox\Like\Models\Reaction;
use MetaFox\Like\Policies\ReactionPolicy;
use MetaFox\Like\Repositories\ReactionAdminRepositoryInterface;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;

/**
 * Class ReactionRepository.
 * @method Reaction getModel()
 * @method Reaction find($id, $columns = ['*'])
 *
 * @ignore
 * @codeCoverageIgnore
 */
class ReactionAdminRepository extends AbstractRepository implements ReactionAdminRepositoryInterface
{
    public function model(): string
    {
        return Reaction::class;
    }

    public function viewReactions(User $context, array $attributes): Paginator
    {
        policy_authorize(ReactionPolicy::class, 'viewAny', $context);

        $limit         = Arr::get($attributes, 'limit');
        $search        = Arr::get($attributes, 'q');
        $status        = Arr::get($attributes, 'is_active');
        $table         = $this->getModel()->getTable();
        $query         = $this->getModel()->newQuery()->select("$table.*");
        $defaultLocale = Language::getDefaultLocaleId();

        if ($search) {
            $searchScope = new SearchScope($search, ['ps.text']);
            $searchScope->asLeftJoin();
            $searchScope->setTableField('title');
            $searchScope->setJoinedTable('phrases');
            $searchScope->setAliasJoinedTable('ps');
            $searchScope->setJoinedField('key');

            $query->where('ps.locale', '=', $defaultLocale);
            $query = $query->addScope($searchScope);
        }

        if ($status != null) {
            $query->where("$table.is_active", $status);
        }

        return $query
            ->orderBy("$table.ordering")
            ->orderBy("$table.id")
            ->simplePaginate($limit);
    }

    public function createReaction(User $context, array $attributes): Reaction
    {
        policy_authorize(ReactionPolicy::class, 'create', $context);

        $tempFileId = Arr::get($attributes, 'image.temp_file');
        $isActive   = Arr::get($attributes, 'is_active', false);

        if ($isActive) {
            $this->checkActiveReaction();
        }

        if (is_numeric($tempFileId)) {
            $storageFile = upload()->getFile($tempFileId);

            Arr::set($attributes, 'icon_file_id', $storageFile->entityId());
            Arr::set($attributes, 'image_path', $storageFile->path);
            Arr::set($attributes, 'icon_path', null);
            Arr::set($attributes, 'server_id', $storageFile->storage_id);

            upload()->rollUp($tempFileId);
        }
        Arr::set($attributes, 'ordering', $this->lastOrdering() + 1);

        /** @var Reaction $reaction */
        $reaction = $this->create($attributes);
        $reaction->refresh();

        return $reaction;
    }

    public function updateReaction(User $context, int $id, array $attributes): Reaction
    {
        policy_authorize(ReactionPolicy::class, 'update', $context);

        $reaction   = $this->find($id);
        $tempFileId = Arr::get($attributes, 'image.temp_file');

        if (is_numeric($tempFileId)) {
            $storageFile = upload()->getFile($tempFileId);

            Arr::set($attributes, 'icon_file_id', $storageFile->entityId());
            Arr::set($attributes, 'image_path', $storageFile->path);
            Arr::set($attributes, 'icon_path', null);
            Arr::set($attributes, 'server_id', $storageFile->storage_id);

            upload()->rollUp($tempFileId);
            upload()->rollUp($reaction->icon_file_id);
        }

        $reaction->fill($attributes);

        if ($reaction->is_active && $reaction->isDirty('is_active')) {
            $this->checkActiveReaction();
        }

        $reaction->save();
        $reaction->refresh();

        return $reaction;
    }

    /**
     * @inheritDoc
     */
    public function ordering(User $context, array $orders): void
    {
        foreach ($orders as $order => $id) {
            $this->getModel()->newQuery()
                ->where('id', $id)->update(['ordering' => $order]);
        }
    }

    /**
     * @param User $context
     * @param int  $id
     * @param int  $newReactionId
     */
    public function deleteReaction(User $context, int $id, int $newReactionId): void
    {
        $reaction = $this->find($id);

        if ($reaction->is_default) {
            abort(403, __p('like::phrase.cannot_delete_default_reaction'));
        }

        DeleteReactionJob::dispatchSync($reaction, $newReactionId);

        $reaction->delete();
    }

    public function deleteAllBelongTo(Reaction $reaction): void
    {
        $reaction->likes()->each(function (Like $like) {
            $like->delete();
        });

        $reaction->items()->each(function (LikeAgg $agg) {
            $agg->delete();
        });
    }

    public function moveToNewReaction(Reaction $reaction, int $newReactionId): void
    {
        $likeIds = $reaction->likes()->pluck('id')->toArray();

        if (!empty($likeIds)) {
            Like::query()->whereIn('id', $likeIds)
                ->update([
                    'reaction_id' => $newReactionId,
                ]);
        }

        $reaction->items()->each(function (LikeAgg $agg) use ($newReactionId) {
            if ($agg->total_reaction == 0) {
                $agg->delete();

                return;
            }

            $likeAggData = [
                'item_id'     => $agg->itemId(),
                'item_type'   => $agg->itemType(),
                'reaction_id' => $newReactionId,
            ];

            $likeAgg = LikeAgg::query()->firstOrCreate($likeAggData);

            if ($likeAgg instanceof HasAmounts) {
                $likeAgg->update([
                    'reaction_id' => $newReactionId,

                ]);

                $likeAgg->incrementAmount('total_reaction', $agg->total_reaction);
            }

            $agg->delete();
        });
    }

    public function deleteOrMoveToNewReaction(Reaction $reaction, int $newReactionId): void
    {
        app('events')->dispatch('like.delete_or_move_reaction', [$reaction, $newReactionId]);

        if ($newReactionId > 0) {
            $this->moveToNewReaction($reaction, $newReactionId);

            $reaction->forceDelete();

            return;
        }

        $this->deleteAllBelongTo($reaction);

        $reaction->forceDelete();
    }

    public function lastOrdering(): int
    {
        return $this->getModel()->newQuery()->max('ordering');
    }

    public function getTotalReactionActive(): int
    {
        return $this->getModel()
            ->newModelQuery()
            ->where('is_active', MetaFoxConstant::IS_ACTIVE)
            ->count('id');
    }

    public function checkActiveReaction(): void
    {
        if ($this->getTotalReactionActive() >= Reaction::LIMIT_ACTIVE_REACTION) {
            abort(403, json_encode([
                'title'   => __p('core::phrase.oops'),
                'message' => __p('like::phrase.you_can_only_enable_numbers_reaction', ['numbers' => Reaction::LIMIT_ACTIVE_REACTION]),
            ]));
        }
    }
}
