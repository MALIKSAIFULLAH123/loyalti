<?php

namespace MetaFox\Activity\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MetaFox\Activity\Contracts\ActivityHiddenManager;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Activity\Repositories\PinRepositoryInterface;
use MetaFox\Activity\Support\Browse\Scopes\SortScope;
use MetaFox\Activity\Support\Browse\Scopes\TypeScope;
use MetaFox\Activity\Support\Contracts\StreamManagerInterface;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\TagScope;
use MetaFox\Platform\Support\Browse\Scopes\UserExistScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\User\Support\Browse\Scopes\User\BlockedScope;
use MetaFox\User\Support\Facades\UserEntity;
use Symfony\Component\HttpKernel\Exception\HttpException;
use MetaFox\Activity\Support\Facades\Snooze as SnoozeFacades;

/**
 * Class StreamManager.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class StreamManager implements StreamManagerInterface
{
    /** @var array<string, array<string, string|null>> */
    public array $allowSortFields = [
        Browse::SORT_RECENT => [
            'stream.created_at' => MetaFoxConstant::SORT_DESC,
            'stream.feed_id'    => MetaFoxConstant::SORT_DESC,
        ],
        Browse::SORT_MOST_DISCUSSED => [
            'feed.total_comment' => null,
            'stream.updated_at'  => MetaFoxConstant::SORT_DESC,
            'stream.feed_id'     => MetaFoxConstant::SORT_DESC,
        ],
        Browse::SORT_MOST_VIEWED => [
            'feed.total_view'   => null,
            'stream.updated_at' => MetaFoxConstant::SORT_DESC,
            'stream.feed_id'    => MetaFoxConstant::SORT_DESC,
        ],
        Browse::SORT_MOST_LIKED => [
            'feed.total_like'   => null,
            'stream.updated_at' => MetaFoxConstant::SORT_DESC,
            'stream.feed_id'    => MetaFoxConstant::SORT_DESC,
        ],
        SortScope::SORT_NEWEST_ACTIVITY => [
            'feed.latest_activity_at' => MetaFoxConstant::SORT_DESC,
            'stream.feed_id'          => MetaFoxConstant::SORT_DESC,
        ],
    ];

    /** @var array<string, string> */
    public array $sortMapping = [
        'stream.feed_id'          => 'id',
        'stream.updated_at'       => 'updated_at',
        'feed.total_like'         => 'total_like',
        'feed.total_comment'      => 'total_comment',
        'feed.total_view'         => 'total_view',
        'stream.created_at'       => 'created_at',
        'feed.latest_activity_at' => 'latest_activity_at',
    ];

    /**
     * @var string[]
     */
    public array $select = [
        //    'stream.id',
        'stream.feed_id',
        'stream.updated_at',
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $sortFields;

    /** @var int|null */
    protected $userId;

    /** @var int|null */
    protected $ownerId;

    protected int $limit = Pagination::DEFAULT_ITEM_PER_PAGE;

    protected bool $isViewOnProfile = false;

    protected bool $isPreviewTag = false;

    protected ?array $status = null;

    protected int $continuousTry = 1;

    protected bool $searchByStreamId = false;

    protected bool $onlyFriends;

    protected ?string $hashtag = null;

    protected ?string $searchString = null;

    protected bool $isViewSearchString = false;

    protected bool $isViewHashtag = false;

    protected string $sortView;

    protected string $sortType;

    protected ?User $user = null;

    /**
     * @var array
     */
    protected array $pinnedFeedIds = [];

    /**
     * @var array
     */
    protected array $sponsoredFeedIds = [];

    /**
     * @var array<string, mixed> | array<int, mixed> |null
     */
    protected ?array $additionalConditions = null;

    /** @var string[] */
    protected array $eagerLoads = [];

    /**
     * @var bool
     */
    protected bool $isGreaterThanLastFeed = false;

    /**
     * @var array|null
     */
    protected ?array $loadedSponsoredFeedIds = null;

    public function __construct()
    {
        $this->onlyFriends = (bool) Settings::get('activity.feed.only_friends', true);
        $this->sortFields  = $this->allowSortFields[Browse::SORT_RECENT];
        $this->sortView    = Browse::SORT_RECENT;
        $this->sortType    = MetaFoxConstant::SORT_DESC;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        $this->setUserId($this->user->entityId());

        return $this;
    }

    public function getEagerLoads(): array
    {
        return $this->eagerLoads;
    }

    public function setLoadedSponsoredFeedIds(?array $ids): self
    {
        $this->loadedSponsoredFeedIds = $ids;

        return $this;
    }

    //Support in case check new feeds
    public function setIsGreaterThanLastFeed(bool $value = true): self
    {
        $this->isGreaterThanLastFeed = $value;

        return $this;
    }

    //Support in case check new feeds
    public function getIsGreaterThanLastFeed(): bool
    {
        return $this->isGreaterThanLastFeed;
    }

    /**
     * @return string
     */
    public function getStatus(): array
    {
        if (null == $this->status) {
            $this->status = [MetaFoxConstant::ITEM_STATUS_APPROVED];
        }

        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return StreamManager
     */
    public function setStatus(?array $status = null): self
    {
        if (null == $status) {
            $status = [MetaFoxConstant::ITEM_STATUS_APPROVED];
        }

        $this->status = $status;

        return $this;
    }

    public function isOnlyFriends(): bool
    {
        return $this->onlyFriends;
    }

    public function setOnlyFriends(bool $onlyFriends): self
    {
        $this->onlyFriends = $onlyFriends;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSearchByStreamId(): bool
    {
        return $this->searchByStreamId;
    }

    /**
     * @param bool $value
     *
     * @return StreamManager
     */
    public function setSearchByStreamId(bool $value): self
    {
        $this->searchByStreamId = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isViewOnProfile(): bool
    {
        return $this->isViewOnProfile;
    }

    public function setPreviewTag(bool $isPreviewTag): self
    {
        $this->isPreviewTag = $isPreviewTag;

        return $this;
    }

    /**
     * @return int
     */
    public function isPreviewTag(): int
    {
        return (int) $this->isPreviewTag;
    }

    /**
     * @param bool $isViewOnProfile
     *
     * @return StreamManager
     */
    public function setIsViewOnProfile(bool $isViewOnProfile): self
    {
        $this->isViewOnProfile = $isViewOnProfile;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @return int|null
     */
    public function getOwnerId(): ?int
    {
        return $this->ownerId;
    }

    /**
     * @param int $ownerId
     *
     * @return self
     */
    public function setOwnerId(int $ownerId): self
    {
        $this->ownerId = $ownerId;

        $this->setIsViewOnProfile(true);

        return $this;
    }

    /**
     * @param int $userId
     *
     * @return self
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return self
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getSelect(): array
    {
        return $this->select;
    }

    /**
     * @param string[] $select
     *
     * @return self
     */
    public function setSelect(array $select): self
    {
        $this->select = $select;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSortFields(): array
    {
        return $this->sortFields;
    }

    /**
     * @param string $view
     * @param string $sortType
     *
     * @return self
     */
    public function setSortFields(string $view, string $sortType = MetaFoxConstant::SORT_DESC): self
    {
        if (array_key_exists($view, $this->allowSortFields)) {
            $this->sortView   = $view;
            $this->sortType   = $sortType;
            $this->sortFields = $this->allowSortFields[$view];
        }

        return $this;
    }

    public function getSortView(): string
    {
        return $this->sortView;
    }

    public function getSortType(): string
    {
        return $this->sortType;
    }

    /**
     * @return array<mixed>
     */
    public function getFeedSortView(): array
    {
        return [
            Browse::SORT_MOST_DISCUSSED,
            Browse::SORT_MOST_VIEWED,
            Browse::SORT_MOST_LIKED,
        ];
    }

    /**
     * @param string $hashtag
     *
     * @return StreamManager
     */
    public function setHashtag(string $hashtag): self
    {
        $this->hashtag = $hashtag;

        return $this;
    }

    /**
     * @param string $search
     *
     * @return $this
     */
    public function setSearchString(string $search): self
    {
        $this->searchString = $search;

        return $this;
    }

    /**
     * @param bool $isViewSearch
     *
     * @return $this
     */
    public function setIsViewSearch(bool $isViewSearch): self
    {
        $this->isViewSearchString = $isViewSearch;

        return $this;
    }

    /**
     * @return bool
     */
    public function isViewSearch(): bool
    {
        return $this->isViewSearchString;
    }

    /**
     * @return StreamManager
     */
    public function isApproved(): self
    {
        $this->status = [MetaFoxConstant::ITEM_STATUS_APPROVED];

        return $this;
    }

    /**
     * @return StreamManager
     */
    public function isDenied(): self
    {
        $this->status = [MetaFoxConstant::ITEM_STATUS_DENIED];

        return $this;
    }

    /**
     * @return StreamManager
     */
    public function isPending(): self
    {
        $this->status = [MetaFoxConstant::ITEM_STATUS_PENDING];

        return $this;
    }

    /**
     * @return StreamManager
     */
    public function isRemoved(): self
    {
        $this->status = [MetaFoxConstant::ITEM_STATUS_REMOVED];

        return $this;
    }

    protected function applyBlockedBuilder(Builder $query, $limit = 50): void
    {
        $blockedScope = new BlockedScope();
        $blockedScope->setContextId($this->getUserId())
            ->setTable('stream')
            ->setPrimaryKey('user_id')
            ->setSecondKey('owner_id');

        $query->addScope($blockedScope);
    }

    /**
     * @param int|null    $lastFeedId
     * @param string|null $timeFrom
     * @param string|null $timeTo
     *
     * @return Builder|null
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function buildQuery(?int $lastFeedId = null, ?string $timeFrom = null, ?string $timeTo = null): ?Builder
    {
        $query = $this->isViewOnProfile() ? $this->queryProfileFeed() : $this->queryHomeFeed();

        $query->join('activity_feeds as feed', function (JoinClause $join) {
            $join->on('feed.id', '=', 'stream.feed_id')
                ->whereIn('feed.status', $this->getStatus());

            if (is_array($this->loadedSponsoredFeedIds) && count($this->loadedSponsoredFeedIds)) {
                $join->whereNotIn('feed.id', $this->loadedSponsoredFeedIds);
            }
        });

        $this->applyBlockedBuilder($query);

        $lastFeed = null;

        if ($lastFeedId) {
            //@TODO check top stories
            $lastFeed = Feed::query()->where('id', '<=', $lastFeedId)
                ->orderByDesc('id')
                ->first(['id', 'updated_at', 'created_at', 'latest_activity_at']);

            if ($lastFeed == null) {
                return null;
            }
        }

        if ($lastFeed instanceof Feed) {
            if ($this->isSearchByStreamId()) {
                $query->where(function ($builder) use ($lastFeed) {
                    $builder->where('stream.created_at', $this->getIsGreaterThanLastFeed() ? '>' : '<', $lastFeed->created_at)
                        ->orWhere(function ($builder) use ($lastFeed) {
                            $builder->where('stream.created_at', '=', $lastFeed->created_at)
                                ->where('stream.feed_id', $this->getIsGreaterThanLastFeed() ? '>' : '<', $lastFeed->entityId());
                        });
                });
            } else {
                // @todo old phpfox 4 rule.
                if ($this->getOwnerId()) {
                    $query->where(function ($builder) use ($lastFeed) {
                        $builder->where('stream.created_at', '<', $lastFeed->created_at)
                            ->orWhere(function ($builder) use ($lastFeed) {
                                $builder->where('stream.created_at', '=', $lastFeed->created_at)
                                    ->where('stream.feed_id', '<', $lastFeed->entityId());
                            });
                    });
                } else {
                    $query->where(function ($builder) use ($lastFeed) {
                        $builder->where('stream.updated_at', '<', $lastFeed->updated_at)
                            ->orWhere(function ($builder) use ($lastFeed) {
                                $builder->where('stream.updated_at', '=', $lastFeed->updated_at)
                                    ->where('stream.feed_id', '<', $lastFeed->entityId());
                            });
                    });
                }
            }

            if ($this->sortView == SortScope::SORT_NEWEST_ACTIVITY) {
                $query->where(function ($builder) use ($lastFeed) {
                    $builder->where('feed.latest_activity_at', '<=', $lastFeed->latest_activity_at)
                        ->where('stream.feed_id', '!=', $lastFeed->entityId());
                });
            }
        } else {
            if ($timeFrom) {
                $query->where('stream.updated_at', '>=', $timeFrom);
            }

            if ($timeTo) {
                $query->where('stream.updated_at', '<=', $timeTo);
            }
        }

        if ($this->hasAdditionalConditions()) {
            $whereConditions = $this->additionalConditions['where'] ?? [];

            if (!empty($whereConditions)) {
                $query = $this->handleAdditionalConditions($query, $whereConditions);
            }
        }

        foreach ($this->getSortFields() as $sortField => $sortType) {
            if ($sortType === null) {
                $sortType = $this->getSortType();
            }

            $query->orderBy($sortField, $sortType);
        }

        $query->addScope(resolve(TypeScope::class)->setTableAlias('feed'));

        $query->orderBy('stream.id', 'DESC');

        return $query;
    }

    /**
     * Fetch pinned feed into a collections
     * when prepend to collection, we need to keep ordering of lasted pined at first.
     * So we need to sort out ordering in a right way, then collection just keep ordering only.
     *
     * @param Collection $collection
     */
    public function fetchPinnedFeeds(): void
    {
        $repository = resolve(PinRepositoryInterface::class);

        $isViewOnProfile = $this->isViewOnProfile();

        $pins = match ($isViewOnProfile) {
            true  => $repository->getPinsInProfilePage($this->ownerId),
            false => $repository->getPinsInHomePage(),
        };

        if (!count($pins)) {
            return;
        }

        foreach (array_reverse($pins) as $feedId) {
            $this->pinnedFeedIds[] = $feedId;
        }
    }

    /**
     * @param int|null    $lastFeedId
     * @param string|null $timeFrom
     * @param string|null $timeTo
     *
     * @return Collection
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @todo TBD business rule: How to get older feed ?
     */
    public function fetchStream(?int $lastFeedId = null, ?string $timeFrom = null, ?string $timeTo = null)
    {
        $query = $this->buildQuery($lastFeedId, $timeFrom, $timeTo);

        if ($query == null) {
            return collect([]);
        }

        return $query
            ->limit(10)
            ->pluck('feed_id');
    }

    public function getPinnedFeedIds(): array
    {
        return $this->pinnedFeedIds;
    }

    protected function buildQueryForFriendsOnly(Builder $query): void
    {
        $query->join('activity_subscriptions as owner_subscription', function (JoinClause $join) {
            $join->on('owner_subscription.owner_id', '=', 'stream.owner_id')
                ->where('owner_subscription.user_id', '=', $this->getUserId())
                ->where('owner_subscription.is_active', '=', true);
        });

        /*
         * This is for checking snooze user
         */
        $query->leftJoin('activity_subscriptions as user_subscription', function (JoinClause $join) {
            $join->on('user_subscription.owner_id', '=', 'stream.user_id')
                ->where('user_subscription.user_id', '=', $this->getUserId());
        })
            ->where(function (Builder $builder) {
                $builder->whereNull('user_subscription.id')
                    ->orWhere('user_subscription.is_active', '=', true);
            });

        $this->applySnoozeUserCondition($query);
    }

    protected function queryHomeFeed(): Builder
    {
        $hiddenSub = DB::table('activity_hidden')
            ->select('feed_id')
            ->where('user_id', '=', $this->getUserId());
        $privacySub = DB::table('activity_privacy_members')
            ->select('privacy_id')
            ->where('user_id', '=', $this->getUserId())
            ->where('privacy_id', '!=', MetaFoxPrivacy::NETWORK_FRIEND_OF_FRIENDS_ID);

        $entitiesBuilder = resolve(DriverRepositoryInterface::class)
            ->getLoadEntitiesEloquentBuilder()->select('name');

        $query = DB::table('activity_streams as stream')
            // Note: do not distinct, its fine if you get duplicate feed_id, using php logic code outside this method to get more feed.
            ->select($this->getSelect())
            ->whereIn('stream.privacy_id', $privacySub)
            ->whereIn('stream.item_type', $entitiesBuilder)
            ->whereNotIn('stream.feed_id', $hiddenSub)
            ->where('stream.status', '=', $this->isPreviewTag())
            ->limit($this->getLimit());

        if ($this->isViewHashtag()) {
            $query->addScope(new TagScope($this->hashtag, 'activity_tag_data', 'stream.feed_id'));
        }

        $userExistScope = new UserExistScope();
        $userExistScope->setOnFields(['stream.user_id']);
        $query->addScope($userExistScope);

        if ($this->isOnlyFriends()) {
            $this->buildQueryForFriendsOnly($query);

            return $query;
        }

        $ownerSubscriptionQuery = DB::table('activity_subscriptions')->select('owner_id')
            ->where('user_id', '=', $this->getUserId())
            ->where('is_active', '=', true);
        $ownerSubscriptionQuery->whereNull('special_type');

        $userEntitiesQuery = DB::table('user_entities')->select('id')
            ->where(function (Builder $builder) {
                $builder->where('user_entities.entity_type', '=', 'user');
            })
            ->orWhere(function (Builder $builder) use ($ownerSubscriptionQuery) {
                $builder->where('user_entities.entity_type', '<>', 'user')->whereIn('id', $ownerSubscriptionQuery);
            });

        $query->whereIn('stream.owner_id', $userEntitiesQuery);
        $this->applySnoozeUserCondition($query);

        return $query;
    }

    protected function applySnoozeUserCondition(Builder $query): void
    {
        $snoozedUserIds = $this->getSnoozedUserIds();

        $query->where(function (Builder $builder) use ($snoozedUserIds) {
            $builder->whereNotIn('stream.owner_id', $snoozedUserIds)
                ->whereNotIn('stream.user_id', $snoozedUserIds);
        });
    }

    protected function getSnoozedUserIds(): array|Builder
    {
        $snoozeUsers = SnoozeFacades::fetchSnoozedUsers($this->getUserId());

        $snoozeUserIds = array_keys($snoozeUsers);

        if (count($snoozeUserIds) <= 100) {
            return $snoozeUserIds;
        }

        return DB::table('activity_snoozes')
            ->select('owner_id')
            ->where('user_id', $this->getUserId())
            ->where(function (Builder $q) {
                $q->where('is_snooze_forever', '=', 1);
                $q->orWhereDate('snooze_until', '>', Carbon::now()->format('Y-m-d H:i:s'));
            });
    }

    /**
     * @param Builder      $query
     * @param array<mixed> $conditions
     *
     * @return Builder
     */
    protected function handleAdditionalConditions(Builder $query, array $conditions): Builder
    {
        foreach ($conditions as $condition) {
            if (is_array($condition[0])) {
                $query->where(function (Builder $q) use ($condition) {
                    $this->handleAdditionalConditions($q, $condition);
                });

                continue;
            }

            $query->where(...array_values($condition));
        }

        return $query;
    }

    protected function queryProfileFeed(): Builder
    {
        $userId = $this->getUserId();

        $ownerId = $this->getOwnerId();

        if ($userId === null || $ownerId === null) {
            throw new HttpException(400, 'Please set user_id and owner_id');
        }

        $isFriendOfFriend = false;

        $hasModerationPermission = false;

        if ($userId !== MetaFoxConstant::GUEST_USER_ID && app_active('metafox/friend') && $userId !== $ownerId) {
            $context = UserEntity::getById($userId)->detail;

            $owner = UserEntity::getById($ownerId)->detail;

            if (null !== $owner) {
                if (method_exists($owner, 'hasResourceModeration')) {
                    $hasModerationPermission = $owner->hasResourceModeration($context);
                }
            }

            if (!$hasModerationPermission) {
                $isFriendOfFriend = app('events')->dispatch('friend.is_friend_of_friend', [$context->id, $owner->id], true);
            }
        }

        $query = match ($hasModerationPermission) {
            true  => $this->buildProfileQueryForModeration(),
            false => $this->buildProfileQueryForMember($isFriendOfFriend),
        };

        $query->select($this->getSelect())
            ->limit($this->getLimit());

        $query->where('stream.status', '=', $this->isPreviewTag())
            ->where('stream.owner_id', '=', $this->getOwnerId());

        if ($this->isViewSearch()) {
            $search = $this->searchString;

            $query->join('search_items as si', function (JoinClause $joinClause) {
                $joinClause->on('stream.item_type', '=', 'si.item_type')
                    ->on('stream.item_id', '=', 'si.item_id');
            });

            $query = $query->addScope(new SearchScope($search, ['si.title', 'si.text']));
        }

        if ($this->isViewHashtag() && null !== $this->hashtag) {
            $query = $query->addScope(new TagScope($this->hashtag, 'activity_tag_data', 'stream.feed_id'));
        }

        return $query;
    }

    protected function buildProfileQueryForMember(?bool $isFriendOfFriend): Builder
    {
        return DB::table('activity_privacy_members', 'privacy')
            // Note: do not distinct, its fine if you get duplicate feed_id, using php logic code outside this method to get more feed.
            ->join('activity_streams as stream', function (JoinClause $join) use ($isFriendOfFriend) {
                $join->on('stream.privacy_id', '=', 'privacy.privacy_id');
                $join->where('privacy.user_id', '=', $this->getUserId());
                if (!$isFriendOfFriend) {
                    $join->where('stream.privacy_id', '!=', MetaFoxPrivacy::NETWORK_FRIEND_OF_FRIENDS_ID);
                }
            });
    }

    protected function buildProfileQueryForModeration(): Builder
    {
        return DB::table('activity_streams', 'stream');
    }

    /**
     * @param Collection $result
     * @param int        $need
     * @param int|null   $lastFeedId
     * @param int        $try
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function fetchStreamContinuous(
        Collection $result,
        int $need,
        ?int $lastFeedId,
        int $try
    ): void {
        $this->setSearchByStreamId(true);

        if ($try !== 0) {
            if ($result->count()) {
                $lastFeedId = $result->last();
            }
        }

        // Search by last stream id.
        $newData = $this->fetchStream($lastFeedId);

        /*
         * stop counting if there are no more
         */
        if ($newData->count() == 0) {
            return;
        }

        foreach ($newData as $item) {
            if (false === $result->search($item) && !in_array($item, $this->pinnedFeedIds)) {
                $result->add($item);
            }
        }

        if ($need <= $result->count()) {
            return;
        }

        // If we try x times and get nothing, return current collection.
        if (++$try > $this->continuousTry) {
            return;
        }

        $this->fetchStreamContinuous($result, $need, $lastFeedId, $try);
    }

    /**
     * Convert from get stream (feed ids) to collection of Feeds.
     *
     * @param int[] $feedIds
     *
     * @return Collection
     */
    public function toFeeds(
        array $feedIds
    ): Collection {
        $feeds = [];

        if (!empty($feedIds)) {
            $query = Feed::query()
                ->with($this->eagerLoads)
                ->whereIn('id', $feedIds);

            foreach ($this->getSortFields() as $sortField => $sortType) {
                if ($sortType === null) {
                    $sortType = $this->getSortType();
                }
                $query->orderBy($this->sortMapping[$sortField], $sortType);
            }

            $feeds = $query->get();
        }

        /*
         * ensure to keep ordering of items.
         */
        return collect($feeds)->sort(function ($a, $b) use (&$feedIds) {
            return (int) array_search($a->id, $feedIds) - (int) array_search($b->id, $feedIds);
        });
    }

    /**
     * @param bool $isViewHashtag
     *
     * @return StreamManager
     */
    public function setIsViewHashtag(bool $isViewHashtag): self
    {
        $this->isViewHashtag = $isViewHashtag;

        return $this;
    }

    public function isViewHashtag(): bool
    {
        return $this->isViewHashtag;
    }

    /**
     * @param array<int, mixed> | array<string, mixed> $additionalConditions
     *
     * @return StreamManager
     */
    public function setAdditionalConditions(array $additionalConditions): self
    {
        $this->additionalConditions = $additionalConditions;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAdditionalConditions(): bool
    {
        return is_array($this->additionalConditions) && count($this->additionalConditions);
    }

    public function addPinnedFeedIds(Collection $collection)
    {
        foreach ($this->pinnedFeedIds as $feedId) {
            $collection->prepend($feedId);
        }
    }

    public function fetchSponsoredFeeds(?array $loadedSponsoredFeedIds = null): array
    {
        $sponsorFeeds = resolve(FeedRepositoryInterface::class)
            ->getRandomSponsoredItems($this->user, $this->getSponsoredFeedLimit(), $loadedSponsoredFeedIds);

        if ($sponsorFeeds->isEmpty()) {
            return [];
        }

        $sponsorFeedIds = $sponsorFeeds->pluck('id')->toArray();
        $hiddenFeedIds  = $this->getHiddenFeedIds();

        $this->sponsoredFeedIds = array_diff($sponsorFeedIds, $hiddenFeedIds);

        return $this->sponsoredFeedIds;
    }

    protected function getHiddenFeedIds(): array
    {
        $hiddenFeeds = resolve(ActivityHiddenManager::class)->getHiddenFeeds($this->user);

        return array_values($hiddenFeeds);
    }

    public function getSponsoredFeedLimit(): int
    {
        return 1;
    }

    public function addSponsoredFeed(Collection $collection): void
    {
        $sponsoredFeedIds = $this->sponsoredFeedIds;

        if (!count($sponsoredFeedIds)) {
            return;
        }

        $pinnedFeedIds = $this->getPinnedFeedIds();

        if (count($pinnedFeedIds)) {
            $sponsoredFeedIds = array_diff($sponsoredFeedIds, $pinnedFeedIds);
        }

        if (!count($sponsoredFeedIds)) {
            return;
        }

        foreach ($sponsoredFeedIds as $feedId) {
            $collection->prepend($feedId);
        }
    }
}
