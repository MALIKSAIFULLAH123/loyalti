<?php

namespace MetaFox\Friend\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use MetaFox\Core\Models\Privacy;
use MetaFox\Friend\Models\Friend;
use MetaFox\Friend\Models\FriendSuggestionIgnore;
use MetaFox\Friend\Notifications\FriendAccepted;
use MetaFox\Friend\Policies\FriendListPolicy;
use MetaFox\Friend\Policies\FriendPolicy;
use MetaFox\Friend\Repositories\FriendListRepositoryInterface;
use MetaFox\Friend\Repositories\FriendRepositoryInterface;
use MetaFox\Friend\Repositories\FriendRequestRepositoryInterface;
use MetaFox\Friend\Support\Browse\Scopes\Friend\SortScope;
use MetaFox\Friend\Support\Browse\Scopes\Friend\TagScope;
use MetaFox\Friend\Support\Browse\Scopes\Friend\ViewBirthdayFriendsScope;
use MetaFox\Friend\Support\Browse\Scopes\Friend\ViewFriendsScope;
use MetaFox\Friend\Support\Browse\Scopes\Friend\ViewMutualFriendsScope;
use MetaFox\Friend\Support\Browse\Scopes\Friend\ViewProfileFriendsScope;
use MetaFox\Friend\Support\Browse\Scopes\Friend\WhenScope;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\PostAs;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\Platform\Support\Browse\Scopes\FeaturedScope;
use MetaFox\Platform\Support\Browse\Scopes\LocationScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\User\Models\User as UserModel;
use MetaFox\User\Models\UserEntity as UserEntityModel;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Support\Browse\Scopes\User\BlockedScope;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\User as UserSupport;
use MetaFox\User\Traits\UserLocationTrait;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class FriendRepository.
 *
 * @property Friend $model
 * @method   Friend getModel()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @ignore
 * @codeCoverageIgnore
 */
class FriendRepository extends AbstractRepository implements FriendRepositoryInterface
{
    use UserMorphTrait;
    use UserLocationTrait;

    public const FRIEND_SUGGESTION_FOR_USER_ID = 'friend_suggestion_for_user_%s';
    public const AVAILABLE_FRIEND_SUGGESTION_FOR_USER_ID = 'available_friend_suggestion_for_user_%s';

    public function model(): string
    {
        return Friend::class;
    }

    public function addFriend(User $user, User $owner, bool $hasCheckIsFriend): bool
    {
        policy_authorize(FriendPolicy::class, 'addFriend', $user, $owner);

        if ($hasCheckIsFriend) {
            if ($user->entityId() != $owner->entityId() && $this->isFriend($user->entityId(), $owner->entityId())) {
                return false;
            }
        }

        $pendingRequest = $this->getFriendRequestRepository()->isRequested($user->entityId(), $owner->entityId());

        $sendRequest = $this->getFriendRequestRepository()->isRequested($owner->entityId(), $user->entityId());

        if (!$pendingRequest && !$sendRequest) {
            return false;
        }

        $userFriendship = $this->create([
            'user_id'    => $owner->entityId(),
            'user_type'  => $owner->entityType(),
            'owner_id'   => $user->entityId(),
            'owner_type' => $user->entityType(),
        ]);

        //Send notification
        Notification::send($user, new FriendAccepted($userFriendship));

        $this->create([
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'owner_id'   => $owner->entityId(),
            'owner_type' => $owner->entityType(),
        ]);

        //Delete friend request
        $this->getFriendRequestRepository()->deleteAllRequestByUserIdAndOwnerId($user->entityId(), $owner->entityId());

        $this->clearFriendSuggestionCache($user->entityId(), $owner->entityId());

        return true;
    }

    public function isFriend(?int $userId, ?int $friendId): bool
    {
        if ($userId == $friendId || !$userId || !$friendId) {
            return false;
        }

        // sort $friendId,$userId to save count.

        return (bool) LoadReduce::remember(
            sprintf('friend::exists(user:%s,user:%s)', $userId, $friendId),
            fn () => Friend::query()->where(['user_id' => $userId, 'owner_id' => $friendId])->exists()
        );
    }

    public function unFriend(int $userId, int $friendId): bool
    {
        if (!$this->isFriend($userId, $friendId)) {
            return false;
        }

        $records = [
            ['user_id' => $userId, 'owner_id' => $friendId],
            ['user_id' => $friendId, 'owner_id' => $userId],
        ];

        foreach ($records as $record) {
            /** @var Friend $model */
            $model = $this->getModel()->where($record)->first();

            if (!$model instanceof Friend) {
                continue;
            }

            app('events')->dispatch(
                'notification.delete_notification_by_type_and_item',
                ['friend_accepted', $model->entityId(), $model->entityType()],
                true
            );

            $model->delete();
        }

        $this->clearFriendSuggestionCache($userId, $friendId);

        return true;
    }

    /**
     * @return FriendRequestRepositoryInterface
     */
    private function getFriendRequestRepository(): FriendRequestRepositoryInterface
    {
        return resolve(FriendRequestRepositoryInterface::class);
    }

    /**
     * @return FriendListRepositoryInterface
     */
    private function getFriendListRepository(): FriendListRepositoryInterface
    {
        return resolve(FriendListRepositoryInterface::class);
    }

    /**
     * @return UserRepositoryInterface
     */
    private function getUserRepository(): UserRepositoryInterface
    {
        return resolve(UserRepositoryInterface::class);
    }

    public function viewProfileFriends(User $context, User $owner, array $attributes): Paginator
    {
        $viewLatest = false;

        if ($context->entityId() == $owner->entityId()) {
            $viewLatest = true;
        }

        if ($context->entityId() == MetaFoxConstant::GUEST_USER_ID) {
            $viewLatest = true;
        }

        //View own profile
        if ($viewLatest) {
            return $this->viewFriends($context, $owner, array_merge($attributes, [
                'view' => 'latest',
            ]));
        }

        $limit = $attributes['limit'];

        $viewProfileFriendScope = new ViewProfileFriendsScope();

        $viewProfileFriendScope
            ->setUserId($context->entityId())
            ->setOwnerId($owner->entityId());

        $query = $this->buildFriends($viewProfileFriendScope);

        return $query
            ->simplePaginate($limit);
    }

    public function viewFriends(User $context, User $owner, array $attributes): Paginator
    {
        policy_authorize(FriendPolicy::class, 'viewAny', $context, $owner);

        $view       = Arr::get($attributes, 'view', Browse::VIEW_ALL);
        $limit      = Arr::get($attributes, 'limit');
        $listId     = Arr::get($attributes, 'list_id', 0);
        $search     = Arr::get($attributes, 'q');
        $sort       = Arr::get($attributes, 'sort', SortScope::SORT_DEFAULT);
        $sortType   = Arr::get($attributes, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
        $when       = Arr::get($attributes, 'when', Browse::WHEN_ALL);
        $isFeatured = Arr::get($attributes, 'is_featured');

        $isSuggestion = Arr::get($attributes, 'is_suggestion', false);

        if ($view == 'profile') {
            return $this->viewProfileFriends($context, $owner, $attributes);
        }

        if ($view == 'mutual') {
            if ($context->entityId() == $owner->entityId()) {
                abort(403, __p('friend::validate.user_not_same_user_context'));
            }

            return $this->viewMutualFriends($context->entityId(), $owner->entityId(), $search, $limit, $isSuggestion);
        }

        if ($listId > 0) {
            $list = $this->getFriendListRepository()->find($listId);
            policy_authorize(FriendListPolicy::class, 'view', $context, $list);
        }

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $whenScope = new WhenScope();
        $whenScope->setWhen($when);

        $viewFriendsScope = new ViewFriendsScope();

        $viewFriendsScope->setUserId($owner->entityId())
            ->setListId($listId)
            ->setSearchText($search)
            ->setIsMention(!empty($attributes['is_mention']));

        $query = match ($isSuggestion) {
            true  => $this->buildFriendsForSuggestion($context, $viewFriendsScope, $attributes),
            false => $this->buildFriends($viewFriendsScope, $sortScope, $whenScope),
        };

        $query->addScope(new FeaturedScope($isFeatured));

        return $query->simplePaginate($limit);
    }

    public function clearFriendSuggestionCache(int $userId, int $ownerId): void
    {
        $userCacheName  = sprintf(self::FRIEND_SUGGESTION_FOR_USER_ID, $userId);
        $ownerCacheName = sprintf(self::FRIEND_SUGGESTION_FOR_USER_ID, $ownerId);
        $availableUserCacheName  = sprintf(self::AVAILABLE_FRIEND_SUGGESTION_FOR_USER_ID, $userId);
        $availableOwnerCacheName = sprintf(self::AVAILABLE_FRIEND_SUGGESTION_FOR_USER_ID, $ownerId);

        Cache::deleteMultiple([$userCacheName, $ownerCacheName, $availableOwnerCacheName, $availableUserCacheName]);
    }

    protected function buildFriends(
        BaseScope  $friendScope,
        ?BaseScope $sortScope = null,
        ?BaseScope $whenScope = null
    ): Builder
    {
        $query = $this->getUserRepository()
            ->getModel()
            ->newQuery()
            ->with('profile');

        $query->addScope($friendScope);

        if ($whenScope instanceof BaseScope) {
            $query->addScope($whenScope);
        }

        if ($sortScope instanceof BaseScope) {
            $query->addScope($sortScope);
        }

        return $query;
    }

    protected function buildFriendsForSuggestion(User $context, BaseScope $friendScope, array $attributes = []): Builder
    {
        $friendScope->setSearchFields(['user_entities.name', 'user_entities.user_name']);

        $isShareOnProfile = Arr::get($attributes, 'share_on_profile');

        $query = UserEntityModel::query()
            ->join('users', function (JoinClause $joinClause) {
                $joinClause->on('user_entities.id', '=', 'users.id');
            });

        $query->addScope($friendScope)->orderByDesc('friends.id');

        if ($isShareOnProfile) {
            $query->leftJoin('user_privacy_values', function (JoinClause $joinClause) {
                $joinClause->on('user_privacy_values.user_id', '=', 'users.id')
                    ->where('user_privacy_values.name', '=', 'feed:share_on_wall');
            })
                ->leftJoin('core_privacy_members', function (JoinClause $joinClause) use ($context) {
                    $joinClause->on('user_privacy_values.privacy_id', '=', 'core_privacy_members.privacy_id')
                        ->where('core_privacy_members.user_id', '=', $context->entityId());
                })
                ->where(function (Builder $builder) {
                    $builder->whereNull('user_privacy_values.id')
                        ->orWhereNotNull('core_privacy_members.id');
                });
        }

        return $query
            ->with(['detail'])
            ->select('user_entities.*');
    }

    /**
     * @param int    $contextId
     * @param int    $userId
     * @param string $search
     * @param int    $limit
     *
     * @return Paginator
     */
    private function viewMutualFriends(
        int    $contextId,
        int    $userId,
        string $search,
        int    $limit,
        bool   $isSuggestion = false
    ): Paginator
    {
        $mutualFriendsScope = new ViewMutualFriendsScope();

        $mutualFriendsScope->setContextId($contextId)
            ->setUserId($userId)
            ->setSearchText($search);

        $query = match ($isSuggestion) {
            true  => UserEntityModel::query(),
            false => $this->getUserRepository()->getModel()->newQuery()->with(['profile'])
        };

        return $query
            ->addScope($mutualFriendsScope)
            ->simplePaginate($limit);
    }

    public function getMutualFriends(
        int $contextId,
        int $userId,
        int $limit = Pagination::DEFAULT_ITEM_PER_PAGE
    ): Collection
    {
        $mutualFriendsScope = new ViewMutualFriendsScope();
        $mutualFriendsScope
            ->setContextId($contextId)
            ->setUserId($userId);

        $results = $this->getUserRepository()
            ->getModel()
            ->newQuery()
            ->with('profile')
            ->addScope($mutualFriendsScope)
            ->limit($limit)
            ->get();

        if (!$results instanceof Collection) {
            return new Collection([]);
        }

        return $results;
    }

    public function countMutualFriends(int $contextId, int $userId): int
    {
        return LoadReduce::get(
            sprintf('friend::countMutualFriends(user:%s,owner:%s)', $contextId, $userId),
            fn () => $this->getUserRepository()
                ->getModel()
                ->newQuery()
                ->addScope((new ViewMutualFriendsScope())
                    ->setContextId($contextId)
                    ->setUserId($userId))
                ->count()
        );
    }

    public function countTotalFriends(int $userId): int
    {
        return $this->getModel()->newQuery()->where([
            'user_id' => $userId,
        ])->count();
    }

    public function getFriendIds(int $userId): array
    {
        return $this->getModel()->newQuery()->where([
            'user_id' => $userId,
        ])->get(['owner_id'])->pluck('owner_id')->toArray();
    }

    public function getAvailableAddingFriendSuggestion(User $context, array $params = []): array
    {
        if (!Settings::get('friend.enable_friend_suggestion')) {
            return [];
        }

        $suggestionIds = Cache::remember(sprintf(self::AVAILABLE_FRIEND_SUGGESTION_FOR_USER_ID, $context->entityId()), 300, function () use ($context, $params) {
            return $this->getSuggestionBuilder($context)
                ->whereIn('users.id',
                    DB::table('users')
                        ->leftJoin('user_privacy_values', function (JoinClause $joinClause) {
                            $joinClause->on('users.id', '=', 'user_privacy_values.user_id')
                                ->where('user_privacy_values.name', '=', 'friend:send_request');
                        })
                        ->whereNull('user_privacy_values.id')
                        ->orWhereIn('user_privacy_values.privacy', [MetaFoxPrivacy::MEMBERS, MetaFoxPrivacy::FRIENDS_OF_FRIENDS])
                        ->select('users.id')
                )
                ->limit(100)
                ->get()
                ->pluck('id')
                ->toArray();
        });

        if (!count($suggestionIds)) {
            return [];
        }

        $limit = Settings::get('friend.friend_suggestion_friend_check_count', 50);

        if (is_numeric($requestLimit = Arr::get($params, 'limit', 9)) && $requestLimit < $limit) {
            $limit = $requestLimit;
        }

        $limit = min($limit ?: 1, count($suggestionIds));

        shuffle($suggestionIds);

        $limitedSuggestionIds = array_slice($suggestionIds, 0, $limit);

        return UserModel::query()
            ->whereIn('users.id', $limitedSuggestionIds)
            ->whereIn('users.id',
                DB::table('users')
                    ->leftJoin('user_privacy_values', function (JoinClause $joinClause) use ($limitedSuggestionIds) {
                        $joinClause->on('users.id', '=', 'user_privacy_values.user_id')
                            ->where('user_privacy_values.name', '=', 'friend:send_request')
                            ->whereIn('user_privacy_values.user_id', $limitedSuggestionIds);
                    })
                    ->whereNull('user_privacy_values.id')
                    ->orWhereIn('user_privacy_values.privacy', [MetaFoxPrivacy::MEMBERS, MetaFoxPrivacy::FRIENDS_OF_FRIENDS])
                    ->select('users.id')
            )
            ->get()
            ->all();
    }

    public function getFriendSuggestion(User $context, array $params = []): array
    {
        $cacheName = sprintf(self::FRIEND_SUGGESTION_FOR_USER_ID, $context->entityId());

        if (!Settings::get('friend.enable_friend_suggestion')) {
            return [];
        }

        $cacheTimeSetting = Settings::get('friend.friend_suggestion_timeout');

        // Convert minutes to seconds if $cacheTimeSetting is not null
        $cacheTimeSetting = $cacheTimeSetting ? $cacheTimeSetting * 60 : null;

        $limit = Arr::get($params, 'limit', 9);

        return Cache::remember($cacheName, $cacheTimeSetting, function () use ($context, $params, $limit) {
            $suggestion = $this->getSuggestion($context, $params);

            if ($suggestion->isEmpty()) {
                return [];
            }

            if ($suggestion->count() <= $limit) {
                return $suggestion->all();
            }

            return $suggestion->random($limit)->all();
        });
    }

    /**
     * @warning Don't update any big changes on this method due to side effects
     * @param User $context
     * @return Builder
     */
    protected function getSuggestionBuilder(User $context): Builder
    {
        $canSuggestionLocation = Settings::get('friend.friend_suggestion_based_on_user_location', false);

        $query = UserModel::query()->select('users.*');

        $query = $this->getBuilderUserSuggestion($context, $query, ['can_suggestion_location' => $canSuggestionLocation]);

        $blockedScope = new BlockedScope();

        $blockedScope->setContextId($context->entityId())
            ->setTable('users')
            ->setPrimaryKey('id');

        $query->addScope($blockedScope);

        return $query;
    }

    public function getSuggestion(User $context, array $params = []): Collection
    {
        $query = $this->getSuggestionBuilder($context);

        $limit = Settings::get('friend.friend_suggestion_friend_check_count', 50);

        if (isset($params['limit'])) {
            $limit = $params['limit'];
        }

        return $query->orderBy('users.id', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function getBuilderUserSuggestion(User $context, Builder $builder, array $attributes = []): Builder
    {
        return $this->buildUserQuery($context, $builder, function ($subQuery) use ($context, $attributes) {
            if (Arr::get($attributes, 'can_suggestion_location', true)) {
                $this->addLocationSuggestionsToQuery($context, $subQuery);
            }
        });
    }

    public function getBuilderUserRecommend(User $context, Builder $builder, array $attributes = []): Builder
    {
        return $this->buildUserQuery($context, $builder, function ($subQuery) use ($context) {
            $this->addLocationSuggestionsToQuery($context, $subQuery, true);
        });
    }

    protected function buildUserQuery(User $context, Builder $builder, callable $additionalLogic): Builder
    {
        $builder->where('users.id', '!=', $context->entityId());

        $subQuery = $this->getBuilderMutualFriends($context);

        if ($subQuery === null) {
            return $builder;
        }

        $builder->whereNotIn('users.id', function ($query) use ($context) {
            $query->select('owner_id')->from('friends')->where('user_id', '=', $context->entityId());
        });

        if (is_callable($additionalLogic)) {
            $additionalLogic($subQuery);
        }

        $builder->whereIn('users.id', $subQuery);

        return $builder;
    }

    protected function addLocationSuggestionsToQuery(User $context, QueryBuilder $query, bool $useUnion = false): void
    {
        if (!$this->hasLocation($context)) {
            return;
        }

        $locationQuery = $this->getBuilderLocationSuggestions($context);
        $this->addFilterFriendToQuery($context, $locationQuery, 'users.id');

        if ($useUnion) {
            $query->union($locationQuery);

            return;
        }

        $query->joinSub($locationQuery, 'location', function (JoinClause $joinClause) {
            $joinClause->on('location.mutual_id', '=', 'friends.owner_id');
        });
    }

    protected function getBuilderLocationSuggestions(User $context): Builder
    {
        $query = UserModel::query()->select('users.id as mutual_id');
        $query->join('user_profiles', function (JoinClause $join) {
            $join->on('user_profiles.id', '=', 'users.id');
        });

        $profile = $context?->profile;

        $locationScope = new LocationScope();
        $locationScope->setTable('user_profiles');
        $locationScope->setCityField('country_city_code');
        $locationScope->setCity($profile?->country_city_code);
        $locationScope->setCountryField('country_iso');
        $locationScope->setCountry($profile?->country_iso);
        $locationScope->setStateField('country_state_id');
        $locationScope->setState($profile?->country_state_id);

        $query->addScope($locationScope);
        $query->where('users.id', '!=', $context->entityId());

        return $query;
    }

    protected function getBuilderMutualFriends(User $context): ?QueryBuilder
    {
        $userFriendPrivacyIds = Privacy::query()
            ->where('user_id', '=', $context->entityId())
            ->where('item_id', '=', $context->entityId())
            ->where('item_type', '=', UserModel::ENTITY_TYPE)
            ->where('privacy', '=', MetaFoxPrivacy::FRIENDS)
            ->where('privacy_type', '=', Friend::PRIVACY_FRIENDS)
            ->pluck('privacy_id');

        if ($userFriendPrivacyIds->isEmpty()) {
            return null;
        }

        $subQuery = DB::table('friends')->select(['friends.owner_id as mutual_id']);
        $subQuery->whereIn('friends.owner_id', function ($query) use ($context, $userFriendPrivacyIds) {
            $query->select('privacy.item_id')->from('core_privacy as privacy')
                ->where('privacy.item_type', '=', UserModel::ENTITY_TYPE)
                ->where('privacy.privacy', '=', MetaFoxPrivacy::FRIENDS)
                ->where('privacy.privacy_type', '=', Friend::PRIVACY_FRIENDS);

            $query->rightJoin('core_privacy_members as member', function (JoinClause $join) use ($context) {
                $join->on('privacy.privacy_id', '=', 'member.privacy_id');
                $join->on('member.user_id', '!=', 'privacy.user_id');
                $join->where('member.user_id', '!=', $context->entityId());
            });

            $query->leftJoin('core_privacy_members as our_member', function (JoinClause $join) use ($userFriendPrivacyIds) {
                $join->on('our_member.user_id', '=', 'member.user_id');
                $join->whereIn('our_member.privacy_id', $userFriendPrivacyIds->toArray());
            });

            $query->whereNotNull('our_member.user_id');
        });

        $this->addFilterFriendToQuery($context, $subQuery);

        $subQuery->whereIn('friends.user_id', function ($query) use ($context) {
            $query->select('owner_id')->from('friends')->where('user_id', '=', $context->entityId());
        });

        return $subQuery;
    }

    private function addFilterFriendToQuery(User $context, Builder|QueryBuilder $query, string $userIdColumn = 'friends.owner_id'): void
    {
        $query->whereNotIn($userIdColumn, function ($query) use ($context) {
            $query->select('owner_id')->from('friend_requests')->where('user_id', '=', $context->entityId());
        });

        $query->whereNotIn($userIdColumn, function ($query) use ($context) {
            $query->select('user_id')->from('friend_requests')->where('owner_id', '=', $context->entityId());
        });

        $query->whereNotIn($userIdColumn, function ($query) use ($context) {
            $query->select('owner_id')->from('friend_suggestion_ignore')->where('user_id', '=', $context->entityId());
        });
    }

    /**
     * @param User         $context
     * @param array<mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function getTagSuggestions(User $context, array $attributes): Paginator
    {
        policy_authorize(FriendPolicy::class, 'viewAny', $context);

        $limit    = Arr::get($attributes, 'limit');
        $search   = Arr::get($attributes, 'q');
        $itemId   = Arr::get($attributes, 'item_id');
        $itemType = Arr::get($attributes, 'item_type');
        $ownerId  = Arr::get($attributes, 'owner_id');
        $userId   = Arr::get($attributes, 'user_id', $context->entityId());
        $user     = UserEntity::getById((int) $userId)->detail;

        $excludedIds = Arr::get($attributes, 'excluded_ids', []);

        $query = $this->getUserRepository()
            ->getModel()
            ->newQuery()
            ->with('profile');

        $tagScope = new TagScope();

        $tagScope->setUserId($context->entityId())
            ->setSearchText($search)
            ->setItemId($itemId)
            ->setItemType($itemType);

        if ($itemId && $itemType) {
            $this->addExtraTagScopesForItem($query, $itemId, $itemType);
        }

        if ($ownerId) {
            $this->addExtraTagScopesForOwner($query, $ownerId);
        }

        if ($user instanceof PostAs) {
            $this->addExtraTagScopesByPostAs($query, $user);
        }

        if (count($excludedIds)) {
            $query->whereNotIn('users.id', $excludedIds);
        }

        return $query
            ->addScope($tagScope)
            ->simplePaginate($limit);
    }

    protected function addExtraTagScopesForOwner(Builder $query, int $ownerId): void
    {
        $owner = UserEntity::getById($ownerId)->detail;

        $this->addExtraTagScopesByOwner($query, $owner);
    }

    protected function addExtraTagScopesForItem(Builder $query, int $itemId, string $itemType): void
    {
        $item = ResourceGate::getItem($itemType, $itemId);
        if (!$item || !$item->owner) {
            return;
        }

        $this->addExtraTagScopesByOwner($query, $item->owner);
    }

    protected function addExtraTagScopesByOwner(Builder $query, mixed $owner): void
    {
        $extraTagScopes = app('events')->dispatch('core.get_extra_tag_scope', [$owner]);
        if (!is_array($extraTagScopes)) {
            return;
        }

        foreach ($extraTagScopes as $scope) {
            if ($scope instanceof BaseScope) {
                $query->addScope($scope);
            }
        }
    }

    protected function addExtraTagScopesByPostAs(Builder $query, mixed $user): void
    {
        $extraTagScopes = app('events')->dispatch('core.get_extra_tag_scope_post_as', [$user]);
        if (!is_array($extraTagScopes)) {
            return;
        }

        foreach ($extraTagScopes as $scope) {
            if ($scope instanceof BaseScope) {
                $query->addScope($scope);
            }
        }
    }

    public function hideUserSuggestion(User $context, User $user): bool
    {
        if ($context->entityId() == $user->entityId()) {
            abort(400, __p('validation.something_went_wrong_please_try_again'));
        }

        $data = [
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'owner_id'   => $user->entityId(),
            'owner_type' => $user->entityType(),
        ];

        $checkExist = $this->getModel()->newQuery()
            ->where($data)
            ->count();

        if ($checkExist) {
            return true;
        }

        $friendSuggestionIgnore = (new FriendSuggestionIgnore($data))->save();

        $this->clearFriendSuggestionCache($context->entityId(), $user->entityId());

        return $friendSuggestionIgnore;
    }

    /**
     * @throws AuthorizationException
     */
    public function getFriendBirthdays(User $user, array $attributes): Paginator
    {
        $limit = Arr::get($attributes, 'limit', 0);
        $view  = Arr::get($attributes, 'view', Browse::VIEW_ALL);
        $month = Arr::get($attributes, 'month', 0);
        $now   = Carbon::make(MetaFox::clientDate());

        if (!Settings::get('friend.enable_birthday_notices', true)) {
            throw new AuthorizationException();
        }

        $query = $this->getUserRepository()->getModel()->newQuery()
            ->select('users.*', 'user_profiles.birthday_month as months')
            ->join('friends', function (JoinClause $join) use ($user) {
                $join->on('friends.user_id', '=', 'users.id');
                $join->where('friends.owner_id', $user->entityId());
            })
            ->join('user_profiles', 'users.id', '=', 'user_profiles.id')
            ->where('users.id', '<>', $user->entityId())
            ->whereNotNull('user_profiles.birthday');

        if ($now->format('L') == '0') {                            //not leap year
            $query->where('user_profiles.birthday_doy', '<>', 60); // 60 is 29/2 of leap year
        }

        $query->join('user_values', function (JoinClause $join) use ($user) {
            $join->on('user_values.user_id', '=', 'users.id');
            $join->where(function (JoinClause $join) {
                $join->where('user_values.name', 'user_profile_date_of_birth_format');
                $join->whereIn('user_values.value', [UserSupport::DATE_OF_BIRTH_SHOW_ALL, UserSupport::DATE_OF_BIRTH_SHOW_DAY_MONTH]);
            });
        });

        $viewScope = new ViewBirthdayFriendsScope();
        $viewScope->setView($view)
            ->setMonth($month)
            ->setUser($user);

        return $query->addScope($viewScope)
            ->orderByRaw("(366 + user_profiles.birthday_doy - $now->dayOfYear) % 366")
            ->simplePaginate($limit);
    }

    public function inviteFriendsToItem(User $context, array $attributes): BaseCollection
    {
        $itemType = Arr::get($attributes, 'item_type');

        $itemId = Arr::get($attributes, 'item_id');

        $userId = Arr::get($attributes, 'user_id', 0);

        $ownerId = Arr::get($attributes, 'owner_id', 0);

        $excludedIds = Arr::get($attributes, 'excluded_ids', []);

        $limit = Arr::get($attributes, 'limit');

        $ownerEntity = UserEntity::getById($ownerId);

        $userEntity = UserEntity::getById($userId);

        $emptyCollection = collect([]);

        if (null === $ownerEntity) {
            return $emptyCollection;
        }

        if (null === $userEntity) {
            return $emptyCollection;
        }

        $owner = $ownerEntity->detail;

        $user = $userEntity->detail;

        if (null === $owner) {
            return $emptyCollection;
        }

        if (null === $user) {
            return $emptyCollection;
        }

        /**
         * Users who were invited before.
         *
         * @deprecated Remove this to optimize performance.
         * Instead, we should use events
         * <code> app('events')->dispatch('friend.invite.users.builder', [$context, $itemType, $itemId], true) </code>
         */
        $invitedUserIds = $this->getInvitedUsersFromItem($context, $itemType, $itemId);
        $builderUserIds = app('events')->dispatch('friend.invite.users.builder', [$context, $itemType, $itemId], true) ?? [];

        $excludedIds = array_unique(array_merge($excludedIds, $invitedUserIds));

        Arr::set($attributes, 'excluded_ids', $excludedIds);

        $query = $this->buildQueryForInviteFriendsToItem($context, $user, $owner, $attributes);

        if (null === $query) {
            return $emptyCollection;
        }

        if ($builderUserIds instanceof Builder) {
            $query->whereNotIn('user_entities.id', $builderUserIds);
        }

        $userIds = $query
            ->where('user_entities.id', '<>', $userId)
            ->orderBy('user_entities.id', 'DESC')
            ->limit($limit)
            ->get()
            ->pluck('id')
            ->toArray();

        if (!count($userIds)) {
            return $emptyCollection;
        }

        return UserModel::query()
            ->with(['profile'])
            ->whereIn('id', $userIds)
            ->get();
    }

    protected function buildQueryParentForInviteToItem(User $context, User $user, User $owner, array $attributes = []): ?QueryBuilder
    {
        $builder = app('events')->dispatch('friend.invite.members.builder', [$context, $user, $owner, $attributes], true);

        if (null === $builder) {
            return null;
        }

        if (is_array($builder)) {
            $builder = array_shift($builder);
        }

        return $builder;
    }

    protected function buildQueryAppForInviteToItem(
        User          $user,
        ?QueryBuilder $rootBuilder = null,
        array         $attributes = [],
        bool          $buildFriend = true
    ): ?QueryBuilder
    {
        if (!$buildFriend && $rootBuilder) {
            return $rootBuilder;
        }

        $privacyIds = $this->getItemPrivacyIds(
            $user,
            Arr::get($attributes, 'item_type'),
            Arr::get($attributes, 'item_id')
        );

        if (!count($privacyIds)) {
            return null;
        }

        if (null === $rootBuilder) {
            return DB::table('user_entities')
                ->select('user_entities.id')
                ->join(
                    'core_privacy_members as member',
                    function (JoinClause $joinClause) use ($privacyIds, $user) {
                        $joinClause->on('user_entities.id', '=', 'member.user_id')
                            ->whereIn('member.privacy_id', $privacyIds)
                            ->where('member.user_id', '<>', $user->entityId());
                    }
                )
                ->leftJoin('user_blocked as blocked_owner', function (JoinClause $join) use ($user) {
                    $join->on('user_entities.id', '=', 'blocked_owner.owner_id');
                    $join->where('blocked_owner.user_id', '=', $user->entityId());
                })->whereNull('blocked_owner.id')
                ->leftJoin('user_blocked as blocked_user', function (JoinClause $join) use ($user) {
                    $join->on('user_entities.id', '=', 'blocked_user.user_id');
                    $join->where('blocked_user.owner_id', '=', $user->entityId());
                })->whereNull('blocked_user.id');
        }

        $rootBuilder->join(
            'core_privacy_members as member',
            function (JoinClause $joinClause) use ($privacyIds, $user) {
                $joinClause->on('user_entities.id', '=', 'member.user_id')
                    ->whereIn('member.privacy_id', $privacyIds)
                    ->where('member.user_id', '<>', $user->entityId());
            }
        );

        return $rootBuilder;
    }

    protected function buildQueryForInviteFriendsToItem(
        User  $context,
        User  $user,
        User  $owner,
        array $attributes
    ): ?QueryBuilder
    {
        $search = Arr::get($attributes, 'q', MetaFoxConstant::EMPTY_STRING);

        $excludedIds = Arr::get($attributes, 'excluded_ids', []);

        $query = null;

        /*
         * In case item created in Group
         */
        if ($owner instanceof HasPrivacyMember) {
            $query = $this->buildQueryParentForInviteToItem($context, $user, $owner, $attributes);
        }

        $query = $this->buildQueryAppForInviteToItem($user, $query, $attributes);

        if (null === $query) {
            return null;
        }

        if (count($excludedIds)) {
            $query->whereNotIn('user_entities.id', $excludedIds);
        }

        if (MetaFoxConstant::EMPTY_STRING !== $search) {
            $query->where(function (QueryBuilder $builder) use ($search) {
                $builder->where('user_entities.user_name', $this->likeOperator(), '%' . $search . '%');
                $builder->orWhere('user_entities.name', $this->likeOperator(), '%' . $search . '%');
            });
        }

        return $query;
    }

    protected function getItemPrivacyIds(User $user, ?string $itemType, ?int $itemId): array
    {
        $userFriendPrivacy = Privacy::query()
            ->where('user_id', '=', $user->entityId())
            ->where('item_id', '=', $user->entityId())
            ->where('item_type', '=', UserModel::ENTITY_TYPE)
            ->where('privacy', '=', MetaFoxPrivacy::FRIENDS)
            ->where('privacy_type', '=', Friend::PRIVACY_FRIENDS)
            ->first();

        $privacyIds = [];

        if ($userFriendPrivacy instanceof Privacy) {
            $privacyIds = [$userFriendPrivacy->privacy_id];
        }

        if (!$itemType || !$itemId) {
            return $privacyIds;
        }

        $class = Relation::getMorphedModel($itemType);

        if (null === $class) {
            return [];
        }

        /**
         * @var Model $class
         */
        $model = resolve($class);

        if (!$model instanceof HasPrivacy) {
            return $privacyIds;
        }

        $item = $model->newQuery()
            ->where($model->getKeyName(), '=', $itemId)
            ->first();

        if (!$item instanceof HasPrivacy) {
            return $privacyIds;
        }

        if ($item->privacy != MetaFoxPrivacy::CUSTOM) {
            return $privacyIds;
        }

        $privacyIds = app('events')->dispatch('core.get_privacy_id', [$itemId, $itemType], true);

        return $privacyIds ?: [];
    }

    protected function getInvitedUsersFromItem(User $context, string $itemType, int $itemId): array
    {
        $userIds = app('events')->dispatch('friend.invite.users', [$context, $itemType, $itemId], true);

        if (null === $userIds) {
            return [];
        }

        return $userIds;
    }

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return array<int, mixed>
     */
    public function inviteFriendToOwner(User $context, array $attributes): array
    {
        $owner       = UserEntity::getById($attributes['owner_id'])->detail;
        $users       = [];
        $parentId    = Arr::get($attributes, 'parent_id', 0);
        $excludedIds = Arr::get($attributes, 'excluded_ids', []);
        $limit       = Arr::get($attributes, 'limit');

        if ($parentId > 0) {
            return $this->getInviteMembersOnOwner($context, $attributes);
        }

        $query = $this->buildQueryForInviteFriendsToOwner($context, $owner, $attributes);

        if (null === $query) {
            return [];
        }

        $builderUserIds = app('events')->dispatch('friend.invite.owner.builder', [$owner], true) ?? [];

        $query->whereNotIn('users.id', $builderUserIds);

        if (count($excludedIds)) {
            $query->whereNotIn('users.id', $excludedIds);
        }

        $query->limit($limit);

        $query->orderBy('users.id', 'DESC');

        foreach ($query->cursor() as $value) {
            if (!$value instanceof Friend) {
                continue;
            }

            if (!$value->owner instanceof UserModel) {
                continue;
            }

            $users[] = $value->owner;
        }

        return $users;
    }

    /**
     * @param User                $context
     * @param array<string,mixed> $attributes
     *
     * @return Paginator|null
     */
    public function getMentions(User $context, array $attributes): ?Paginator
    {
        $user = $context;

        $userId = Arr::get($attributes, 'user_id', 0);

        $owner = null;

        $ownerId = Arr::get($attributes, 'owner_id', 0);

        Arr::set($attributes, 'is_mention', true);

        if ($ownerId > 0) {
            $owner = UserEntity::getById($attributes['owner_id'])->detail;
        }

        if ($userId > 0) {
            $user = UserEntity::getById($userId)->detail;
        }

        if (null !== $owner) {
            return $this->viewMembers($context, $user, $owner, $attributes);
        }

        $view = Arr::get($attributes, 'view');

        if (in_array($view, ['mutual', 'friend'])) {
            Arr::set($attributes, 'is_suggestion', true);

            return $this->viewFriends($context, $user, $attributes);
        }

        return $this->getGlobalMentions($context, $user, $attributes);
    }

    /**
     * @param User  $context
     * @param User  $user
     * @param array $attributes
     *
     * @return Paginator|null
     */
    protected function getGlobalMentions(User $context, User $user, array $attributes): ?Paginator
    {
        $subQuery = $this->buildMentionUnions($context, $user, $attributes);

        if (null === $subQuery) {
            return null;
        }

        $query = $this->buildMentionQuery($subQuery, $attributes);

        $collection = $query->pluck('id');

        if (!$collection->count()) {
            return null;
        }

        $userEntity = new UserEntityModel();

        return $userEntity->newModelQuery()
            ->whereNull($userEntity->getQualifiedDeletedAtColumn())
            ->whereIn('user_entities.id', $collection->toArray())
            ->simplePaginate();
    }

    /**
     * @param User  $context
     * @param User  $user
     * @param array $attributes
     *
     * @return QueryBuilder|null
     */
    protected function buildMentionUnions(User $context, User $user, array $attributes): ?QueryBuilder
    {
        $unions = app('events')->dispatch('friend.mention.builder', [$context, $user, $attributes]);

        if (!is_array($unions)) {
            return null;
        }

        $subQuery = null;

        foreach ($unions as $union) {
            if (null === $union) {
                continue;
            }

            if (!is_array($union)) {
                $union = [$union];
            }

            foreach ($union as $value) {
                if (!$value instanceof QueryBuilder) {
                    continue;
                }

                if (null === $subQuery) {
                    $subQuery = $value;
                    continue;
                }

                $subQuery->unionAll($value);
            }
        }

        return $subQuery;
    }

    /**
     * @param QueryBuilder $subQuery
     * @param array        $attributes
     *
     * @return QueryBuilder
     */
    protected function buildMentionQuery(QueryBuilder $subQuery, array $attributes): QueryBuilder
    {
        $query = DB::table('user_entities');

        $search = Arr::get($attributes, 'q', '');

        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query->joinSub($subQuery, 'sub_user_entities', function (JoinClause $joinClause) {
            $joinClause->on('sub_user_entities.id', '=', 'user_entities.id');
        });

        $this->buildCanBeTaggedForQueryBuilder($query, 'sub_user_entities', 'id');

        if ('' !== $search) {
            $query = $query->addScope(new SearchScope(
                $search,
                ['user_entities.name', 'user_entities.user_name'],
                'user_entities'
            ));
        }

        return $query->limit($limit)
            ->select('user_entities.id')
            ->orderBy('user_entities.name');
    }

    /**
     * @param User  $context
     * @param User  $user
     * @param User  $owner
     * @param array $attributes
     *
     * @return Paginator|null
     */
    public function viewMembers(User $context, User $user, User $owner, array $attributes): ?Paginator
    {
        $subQuery = $this->buildMemberMentionUnions($context, $user, $owner, $attributes);

        $isMemberOnly = (bool) Arr::get($attributes, 'is_member_only', false);

        if (null === $subQuery) {
            return null;
        }

        $query = $this->buildQueryForMemberMention($subQuery, $attributes);

        $collection = $query->pluck('id');

        if (!$collection->count()) {
            return null;
        }

        if ($isMemberOnly) {
            $collection = $collection->diff([$context->entityId()]);
        }

        $userEntity = new UserEntityModel();

        return $userEntity->newModelQuery()
            ->with(['detail'])
            ->whereIn('user_entities.id', $collection->toArray())
            ->simplePaginate();
    }

    /**
     * @param User  $context
     * @param User  $user
     * @param User  $owner
     * @param array $attributes
     *
     * @return QueryBuilder|null
     */
    protected function buildMemberMentionUnions(
        User  $context,
        User  $user,
        User  $owner,
        array $attributes
    ): ?QueryBuilder
    {
        $isMemberOnly = (bool) Arr::get($attributes, 'is_member_only', false);

        $unions = match ($isMemberOnly) {
            true  => app('events')->dispatch('friend.invite.members.builder', [$context, $user, $owner, $attributes]),
            false => app('events')->dispatch('friend.mention.members.builder', [$context, $user, $owner, $attributes])
        };

        if (!is_array($unions)) {
            return null;
        }

        $subQuery = null;

        foreach ($unions as $union) {
            if (null === $union) {
                continue;
            }

            if (!is_array($union)) {
                $union = [$union];
            }

            foreach ($union as $value) {
                if (!$value instanceof QueryBuilder) {
                    continue;
                }

                if (null === $subQuery) {
                    $subQuery = $value;
                    continue;
                }

                $subQuery->unionAll($value);
            }
        }

        return $subQuery;
    }

    protected function buildQueryForMemberMention(QueryBuilder $subQuery, array $attributes): QueryBuilder
    {
        $search = Arr::get($attributes, 'q', '');

        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = DB::table('user_entities')
            ->joinSub($subQuery, 'sub_user_entities', function (JoinClause $joinClause) {
                $joinClause->on('user_entities.id', '=', 'sub_user_entities.id');
            });

        $this->buildCanBeTaggedForQueryBuilder($query, 'sub_user_entities', 'id');

        if ('' !== $search) {
            $query = $query->addScope(new SearchScope(
                $search,
                ['user_entities.name', 'user_entities.user_name'],
                'user_entities'
            ));
        }

        return $query->limit($limit)
            ->select('user_entities.id')
            ->orderBy('user_entities.name');
    }

    protected function buildQueryForInviteFriendsToOwner(User $context, User $owner, array $attributes): ?Builder
    {
        $search = Arr::get($attributes, 'q');

        $privacyType = Arr::get($attributes, 'privacy_type');

        /** @var Privacy $ownerPrivacy */
        $ownerPrivacy = Privacy::query()
            ->where('item_id', '=', $owner->entityId())
            ->where('item_type', '=', $owner->entityType())
            ->where('privacy_type', '=', $privacyType)
            ->first();

        if (!$context instanceof UserModel) {
            return null;
        }

        if (!$ownerPrivacy instanceof Privacy) {
            return null;
        }

        $table    = $this->getModel()->getTable();
        $query    = $this->getModel()->newQuery();
        $subQuery = $this->getModel()->newQuery()->select('our_member.user_id');

        $subQuery->join('core_privacy_members as our_member', function (JoinClause $join) use ($ownerPrivacy, $context, $table) {
            $join->on('our_member.user_id', '=', "$table.owner_id");
            $join->where('our_member.privacy_id', '=', $ownerPrivacy->privacy_id);
            $join->where("$table.user_id", '=', $context->entityId());
        });

        $query->join('users', function (JoinClause $join) use ($table) {
            $join->on('users.id', '=', "$table.owner_id");
        });

        $query->whereNotIn('users.id', $subQuery);

        $blockedScope = new BlockedScope();
        $blockedScope->setContextId($owner->userId())
            ->setTable('users')
            ->setPrimaryKey('id');
        $query->addScope($blockedScope);

        $query->where("$table.user_id", '=', $context->entityId());

        if ('' != $search) {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('users.user_name', $this->likeOperator(), '%' . $search . '%');
                $builder->orWhere('users.full_name', $this->likeOperator(), '%' . $search . '%');
            });
        }

        return $query;
    }

    /**
     * @param User  $context
     * @param array $attributes
     *
     * @return array
     */
    protected function getInviteMembersOnOwner(User $context, array $attributes): array
    {
        /**
         * <code>$ownerId is item in the Group/Page</code>
         */
        $ownerId     = Arr::get($attributes, 'owner_id', 0);
        $parent      = null;
        $users       = [];
        $parentId    = Arr::get($attributes, 'parent_id', 0);
        $excludedIds = Arr::get($attributes, 'excluded_ids', []);
        $search      = Arr::get($attributes, 'q', MetaFoxConstant::EMPTY_STRING);
        $limit       = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $rootTable   = UserEntityModel::newModelInstance()->getTable();
        $query       = UserEntityModel::query()->select("$rootTable.*");
        $owner       = $ownerId > 0 ? UserEntity::getById($ownerId)->detail : null;

        $query->whereNot("$rootTable.id", $context->entityId());

        if ($owner instanceof User) {
            $builderUserIds = app('events')->dispatch('friend.invite.owner.builder', [$owner], true) ?? [];
            $query->whereNotIn("$rootTable.id", $builderUserIds);
        }

        if ($parentId > 0) {
            Arr::set($attributes, 'owner_id', $parentId);
            Arr::forget($attributes, 'parent_id');
            $parent = UserEntity::getById($parentId)->detail;
        }

        Arr::set($attributes, 'is_member_only', true);

        $subQuery = $parent instanceof User ? $this->buildMemberMentionUnions($context, $context, $parent, $attributes) : null;

        if ($subQuery !== null) {
            $query->joinSub($subQuery, 'sub_user_entities', function (JoinClause $joinClause) use ($rootTable) {
                $joinClause->on("$rootTable.id", '=', 'sub_user_entities.id');
            });
        }

        if (MetaFoxConstant::EMPTY_STRING !== $search) {
            $query = $query->addScope(new SearchScope(
                $search,
                ["$rootTable.name", "$rootTable.user_name"],
                $rootTable
            ));
        }

        if (count($excludedIds) > 0) {
            $query->whereNotIn("$rootTable.id", $excludedIds);
        }

        $query->limit($limit);

        foreach ($query->cursor() as $user) {
            if (!$user instanceof UserEntityModel) {
                continue;
            }

            if (!$user->detail instanceof UserModel) {
                continue;
            }

            $users[] = $user->detail;
        }

        return $users;
    }

    protected function buildCanBeTaggedForQueryBuilder(QueryBuilder $builder, string $table, string $key): void
    {
        $builder->leftJoin('user_privacy_values as can_be_tagged', function (JoinClause $join) use ($table, $key) {
            $join->on($table . '.' . $key, '=', 'can_be_tagged.user_id');
            $join->where('can_be_tagged.name', '=', 'user:can_i_be_tagged');
            $join->where('can_be_tagged.privacy', '=', MetaFoxPrivacy::ONLY_ME);
        })
            ->whereNull('can_be_tagged.id');
    }

    public function deleteUserSuggestionIgnoreData(int $userId): void
    {
        FriendSuggestionIgnore::query()
            ->where('user_id', '=', $userId)
            ->orWhere('owner_id', '=', $userId)
            ->delete();
    }
}
