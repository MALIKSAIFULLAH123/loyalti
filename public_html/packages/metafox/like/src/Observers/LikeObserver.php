<?php

namespace MetaFox\Like\Observers;

use Illuminate\Support\Facades\Cache;
use MetaFox\Like\Models\Like;
use MetaFox\Like\Models\LikeAgg;
use MetaFox\Like\Support\CacheManager;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Contracts\HasTotalLike;

class LikeObserver
{
    public function created(Like $model): void
    {
        $item = $model->item;

        if ($item instanceof HasTotalLike) {
            $item->incrementAmount('total_like');
        }

        $likeAggData = [
            'item_id'     => $model->itemId(),
            'item_type'   => $model->itemType(),
            'reaction_id' => $model->reaction_id,
        ];

        /** @var LikeAgg $likeAgg */
        $likeAgg = LikeAgg::query()->where($likeAggData)->first();

        if (null == $likeAgg) {
            (new LikeAgg($likeAggData))->save();
        }

        if ($likeAgg instanceof HasAmounts) {
            $likeAgg->touch();
            $likeAgg->incrementAmount('total_reaction');
        }

        $this->clearCache($model);

        $this->redundantFeed($item);
    }

    public function updated(Like $model): void
    {
        $item = $model->item;

        if ($model->wasChanged(['reaction_id'])) {
            $likeAggData = [
                'item_id'     => $model->itemId(),
                'item_type'   => $model->itemType(),
                'reaction_id' => $model->getOriginal('reaction_id'),
            ];

            /** @var LikeAgg $likeAgg */
            $likeAgg = LikeAgg::query()->where($likeAggData)->first();

            if ($likeAgg instanceof HasAmounts && $likeAgg->total_reaction > 0) {
                $likeAgg->touch();
                $likeAgg->decrementAmount('total_reaction');
            }

            $likeAggData['reaction_id'] = $model->reaction_id;
            /** @var LikeAgg $likeAgg */
            $likeAgg = LikeAgg::query()->where($likeAggData)->first();

            if (null == $likeAgg) {
                (new LikeAgg($likeAggData))->save();
            }

            if ($likeAgg instanceof HasAmounts) {
                $likeAgg->touch();
                $likeAgg->incrementAmount('total_reaction');
            }
        }

        $this->clearCache($model);

        $this->redundantFeed($item);
    }

    public function deleted(Like $model): void
    {
        $item = $model->item;

        if ($item instanceof HasTotalLike) {
            $item->decrementAmount('total_like');
        }

        $likeAggData = [
            'item_id'     => $model->itemId(),
            'item_type'   => $model->itemType(),
            'reaction_id' => $model->reaction_id,
        ];

        /** @var LikeAgg $likeAgg */
        $likeAgg = LikeAgg::query()->where($likeAggData)->first();

        if ($likeAgg instanceof HasAmounts && $likeAgg->total_reaction > 0) {
            $likeAgg->touch();
            $likeAgg->decrementAmount('total_reaction');
        }

        $this->clearCache($model);

        $this->redundantFeed($item);
    }

    private function clearCache(Like $model): void
    {
        Cache::forget(sprintf(CacheManager::IS_LIKED_CACHE, $model->itemId(), $model->itemType(), $model->userId()));
        Cache::forget(sprintf(CacheManager::USER_REACTED_CACHE, $model->itemId(), $model->itemType(), $model->userId()));
    }

    private function redundantFeed(?Entity $item): void
    {
        app('events')->dispatch('activity.redundant', [$item], true);
    }
}
