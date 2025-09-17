<?php

namespace MetaFox\Activity\Repositories\Eloquent;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MetaFox\Activity\Models\ActivityHistory;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Repositories\ActivityHistoryRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class ActivityHistoryRepository.
 *
 * @property ActivityHistory $model
 * @method   ActivityHistory find($id, $columns = ['*'])
 * @method   ActivityHistory getModel()
 */
class ActivityHistoryRepository extends AbstractRepository implements ActivityHistoryRepositoryInterface
{
    public function model()
    {
        return ActivityHistory::class;
    }

    /**
     * @inheritDoc
     */
    public function createHistory(User $user, Feed $feed): ActivityHistory
    {
        $data = [
            'feed_id'    => $feed->entityId(),
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'content'    => $feed->content,
            'created_at' => Carbon::now(),
        ];

        if (!$this->checkExists($feed->entityId())) {
            $data['created_at'] = $feed->created_at;
        }
        $activityHistory = new ActivityHistory($data);
        $activityHistory->save($data);

        return $activityHistory->refresh();
    }

    /**
     * @inheritDoc
     */
    public function viewHistories(User $user, int $id): Collection
    {
        $results = $this->getModel()->newModelQuery()
            ->with(['feed'])
            ->where('feed_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get();

        foreach ($results as $result) {
            $extra = json_decode($result->extra, true);

            if (!$extra) {
                continue;
            }

            $data = [];
            foreach ($extra as $key => $value) {
                $response = app('events')->dispatch('feed.get_url_item_by_id', [$key, $value], true);
                if ($response == null) {
                    $data[] = [
                        'phrase'    => __p('activity::phrase.attachment_is_not_valid'),
                        'image_url' => null,
                    ];
                }

                if (is_array($response)) {
                    $data[] = $response;
                }
            }
            $result->extra = $data;
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function checkExists(int $id): bool
    {
        return $this->getModel()->newQuery()->where('feed_id', $id)->exists();
    }

    /**
     * @inheritDoc
     */
    public function updateHistory(ActivityHistory $activityHistory, array $attributes): void
    {
        $activityHistory->update([
            'phrase' => $attributes['phrase'],
            'extra'  => $attributes['extra'],
        ]);
    }
}
