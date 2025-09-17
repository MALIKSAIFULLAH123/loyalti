<?php

namespace MetaFox\Activity\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Policies\FeedPolicy;
use MetaFox\Activity\Repositories\FeedAdminRepositoryInterface;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;

/**
 * Class FeedAdminRepository.
 * @property Feed $model
 * @method   Feed find($id, $columns = ['*'])
 * @method   Feed getModel()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class FeedAdminRepository extends AbstractRepository implements FeedAdminRepositoryInterface
{
    public function model(): string
    {
        return Feed::class;
    }

    public function viewFeeds(User $context, array $params): Paginator
    {
        $limit = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = $this->buildQueryViewFeeds($context, $params);

        return $query
            ->orderBy('id', 'desc')
            ->paginate($limit);
    }

    protected function buildQueryViewFeeds(User $context, array $params): Builder
    {
        $search      = Arr::get($params, 'q');
        $searchUser  = Arr::get($params, 'user_name');
        $searchOwner = Arr::get($params, 'owner_name');
        $type        = Arr::get($params, 'type_id');
        $itemType    = Arr::get($params, 'item_type');
        $fromDate    = Arr::get($params, 'from_date');
        $toDate      = Arr::get($params, 'to_date');

        $query = $this->getModel()->newQuery()
            ->select(['activity_feeds.*'])
            ->where('activity_feeds.status', MetaFoxConstant::ITEM_STATUS_APPROVED);

        if ($search) {
            $query = $query->addScope(new SearchScope($search, ['content']));
        }

        $userSearchScope = new UserSearchScope();
        $userSearchScope->setTable($this->getModel()->getTable());

        if ($searchUser) {
            $userSearchScope->setAliasJoinedTable('user');
            $userSearchScope->setSearchText($searchUser);
            $userSearchScope->setFieldJoined('user_id');
            $query->addScope($userSearchScope);
        }

        if ($searchOwner) {
            $userSearchScope->setAliasJoinedTable('owner');
            $userSearchScope->setSearchText($searchOwner);
            $userSearchScope->setFieldJoined('owner_id');
            $query->addScope($userSearchScope);
        }

        if ($type) {
            $query->where('activity_feeds.type_id', $type);
        }

        if ($itemType) {
            $query->where('activity_feeds.item_type', $itemType);
        }

        $dateFormat = $this->getModel()->getDateFormat();

        if ($fromDate) {
            $fromDate = Carbon::parse($fromDate)->format($dateFormat);

            $query->where('activity_feeds.created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $toDate = Carbon::parse($toDate)->format($dateFormat);

            $query->where('activity_feeds.created_at', '<=', $toDate);
        }

        return $query->with(['userEntity', 'ownerEntity', 'item']);
    }

    public function deleteFeed(User $user, int $id): bool
    {
        $resource = $this->find($id);

        policy_authorize(FeedPolicy::class, 'delete', $user, $resource);

        if (!$this->delete($id)) {
            return false;
        }

        if ($resource->from_resource == Feed::FROM_FEED_RESOURCE) {
            $this->deleteRelatedItems($resource);
        }

        return true;
    }

    public function deleteFeedWithItems(User $user, int $id): bool
    {
        $resource = $this->find($id);

        policy_authorize(FeedPolicy::class, 'deleteWithItems', $user, $resource);

        if (!$this->delete($id)) {
            return false;
        }

        $this->deleteRelatedItems($resource);

        return true;
    }

    private function deleteRelatedItems(Feed $feed): void
    {
        if (!$feed->item instanceof ActivityFeedSource) {
            return;
        }

        app('events')->dispatch('activity.feed.deleted', [$feed->item]);
    }
}
