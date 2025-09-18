<?php

namespace MetaFox\Story\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Story\Models\StorySet;
use MetaFox\Story\Repositories\MuteRepositoryInterface;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Repositories\StorySetRepositoryInterface;
use MetaFox\Story\Support\Facades\StoryFacades;
use MetaFox\Story\Support\StorySupport;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class StorySetRepository
 * @method StorySet find($id, $columns = ['*'])
 * @method StorySet getModel()
 */
class StorySetRepository extends AbstractRepository implements StorySetRepositoryInterface
{
    use UserMorphTrait;

    public function model()
    {
        return StorySet::class;
    }

    protected function storyRepository(): StoryRepositoryInterface
    {
        return resolve(StoryRepositoryInterface::class);
    }

    protected function muteRepository(): MuteRepositoryInterface
    {
        return resolve(MuteRepositoryInterface::class);
    }

    public function createStorySet(User $context, array $attributes): Model
    {
        return $this->getModel()->newQuery()->updateOrCreate([
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ], [
            'auto_archive' => Arr::get($attributes, 'auto_archive', StorySupport::STORY_AUTO_ARCHIVE),
            'expired_at'   => Arr::get($attributes, 'expired_at', StoryFacades::setExpired()),
        ]);
    }

    public function getStorySets(User $context, array $attributes): Builder
    {
        $query         = $this->getModel()->newQuery();
        $table         = $this->getModel()->getTable();
        $userId        = Arr::get($attributes, 'user_id', 0);
        $relatedUserId = Arr::get($attributes, 'related_user_id', 0);
        $ignoreMuted   = Arr::get($attributes, 'ignore_muted', true);
        $isOnlyFriends = (bool) Settings::get('story.only_friends', true);
        $nowTimestamp  = Carbon::now()->subMinute()->timestamp;
        $contextId     = $context->entityId();

        $orderByRaw = "CASE WHEN {$table}.user_id = {$contextId} THEN 1 WHEN v.total_stories != v.total_view THEN 2 ELSE 3 END as orderByUserId";

        /**
         * related_user_id: Used to sort the specified user to the top
         */
        if ($relatedUserId > 0 && $relatedUserId != $contextId) {
            $orderByRaw = "CASE WHEN {$table}.user_id = {$contextId} THEN 1
                                WHEN {$table}.user_id = {$relatedUserId} THEN 2
                                WHEN v.total_stories != v.total_view THEN 3
                                ELSE 4 END as orderByUserId";
        }

        $subQuery        = $this->storyRepository()->getSubQuery($context, $attributes);
        $subQueryPrivacy = $this->storyRepository()->getSubQueryPrivacy($context, $attributes);

        $query->select("$table.*", DB::raw($orderByRaw))
            ->where("$table.expired_at", '>=', $nowTimestamp)
            ->joinSub($subQuery, 'v', "$table.user_id", '=', 'v.user_id');

        $query->whereIn("$table.id", $subQueryPrivacy);

        switch ($userId) {
            case 0:
                if (!$isOnlyFriends) {
                    break;
                }

                $followings = $this->getFollowings($context, $relatedUserId);
                $query->whereIn("$table.user_id", $followings);
                break;
            default:
                $query->where("$table.user_id", $userId);
                Arr::set($attributes, 'related_user_id', $userId);
                break;
        }

        if ($ignoreMuted) {
            $ignoreQuery = $this->muteRepository()->getUserMutedBuilder($context, $attributes);

            $query->whereNotIn("$table.user_id", $ignoreQuery);
        }

        $query->orderByRaw('orderByUserId');

        return $query;
    }

    protected function getFollowings(User $context, ?int $relatedUserId): Builder
    {
        $contextId = $context->entityId();
        $table     = $this->getModel()->getTable();

        $query = $this->getModel()->newQuery()->select("$table.user_id")
            ->leftJoin('activity_subscriptions', function (JoinClause $join) use ($table, $contextId) {
                $join->on('activity_subscriptions.owner_id', '=', "$table.user_id");
            })->whereNull('activity_subscriptions.special_type')
            ->where('activity_subscriptions.user_id', $contextId)
            ->where('activity_subscriptions.is_active', MetaFoxConstant::IS_ACTIVE);

        if ($relatedUserId > 0) {
            $query->orWhere("$table.user_id", $relatedUserId);
        }

        return $query;
    }

    public function getStorySet(User $context, User $user): ?Model
    {
        $query           = $this->getModel()->newQuery();
        $table           = $this->getModel()->getTable();
        $nowTimestamp    = Carbon::now()->subMinute()->timestamp;
        $subQueryPrivacy = $this->storyRepository()->getSubQueryPrivacy($context, []);

        $query->where("$table.expired_at", '>=', $nowTimestamp)
            ->where("$table.user_id", $user->entityId());

        $query->whereIn("$table.id", $subQueryPrivacy);

        return $query->first();
    }
}
