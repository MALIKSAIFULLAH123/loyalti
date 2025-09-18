<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Saved\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Models\SavedListData;
use MetaFox\Saved\Policies\SavedListPolicy;
use MetaFox\Saved\Repositories\SavedListMemberRepositoryInterface;
use MetaFox\Saved\Repositories\SavedListRepositoryInterface;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Support\Browse\Scopes\User\BlockedScope;

/**
 * Class SavedListRepository.
 *
 * @method SavedList getModel()
 * @method SavedList find($id, $columns = ['*'])
 * @ignore
 * @codeCoverageIgnore
 */
class SavedListRepository extends AbstractRepository implements SavedListRepositoryInterface
{
    public function model(): string
    {
        return SavedList::class;
    }

    protected function memberRepository(): SavedListMemberRepositoryInterface
    {
        return resolve(SavedListMemberRepositoryInterface::class);
    }

    /**
     * @param User $user
     */
    public function deleteForUser(User $user)
    {
        $savedListIds = $this->getModel()->newQuery()
            ->where('user_id', $user->entityId())
            ->get(['id'])
            ->pluck('id')
            ->toArray();

        if (!empty($savedListIds)) {
            $this->getModel()->newQuery()->whereIn('id', $savedListIds)->delete();
            SavedListData::query()->whereIn('list_id', $savedListIds)->delete();
        }
    }

    public function createSavedList(User $user, array $attributes): SavedList
    {
        policy_authorize(SavedListPolicy::class, 'create', $user);

        $savedList = (new SavedList(array_merge($attributes, [
            'user_id'       => $user->entityId(),
            'user_type'     => $user->entityType(),
            'item_added_at' => Carbon::now(),
        ])));

        if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $savedList->setPrivacyListAttribute($attributes['list']);
        }

        $savedList->save();

        $this->memberRepository()->createMember($user, $savedList->entityId());

        return $savedList;
    }

    public function viewSavedList(User $user, int $id): SavedList
    {
        $savedList = $this->with(['userEntity', 'savedItems', 'savedThumb', 'savedThumb.item'])->find($id);

        policy_authorize(SavedListPolicy::class, 'view', $user, $savedList);

        return $savedList;
    }

    public function updateSavedList(User $user, int $id, array $attributes): SavedList
    {
        $savedList = $this->with(['userEntity'])->find($id);
        policy_authorize(SavedListPolicy::class, 'update', $user, $savedList);

        $savedList->fill($attributes)->save();

        return $savedList->refresh();
    }

    public function viewSavedLists(User $user, array $attributes): Paginator
    {
        policy_authorize(SavedListPolicy::class, 'viewAny', $user);

        $query = $this->getModel()->newQuery()->select('saved_lists.*');

        if ($attributes['saved_id']) {
            $query->join('saved_list_data', function (JoinClause $joinClause) {
                $joinClause->on('saved_list_data.list_id', '=', 'saved_lists.id');
            });
            $query->where('saved_list_data.saved_id', '=', $attributes['saved_id']);
        }

        $query = $query->join('saved_list_members', function (JoinClause $joinClause) {
            $joinClause->on('saved_list_members.list_id', '=', 'saved_lists.id');
        });
        $query = $query->where('saved_list_members.user_id', $user->entityId());

        return $query
            ->with(['userEntity', 'savedItems', 'savedThumb', 'savedThumb.item'])
            ->orderBy('saved_lists.name')
            ->simplePaginate($attributes['limit']);
    }

    public function getTotalSavedLists(User $user, array $attributes)
    {
        $query = $this->getModel()->newQuery()->select('saved_lists.id');

        if ($attributes['saved_id']) {
            $query->join('saved_list_data', function (JoinClause $joinClause) {
                $joinClause->on('saved_list_data.list_id', '=', 'saved_lists.id');
            });
            $query->where('saved_list_data.saved_id', '=', $attributes['saved_id']);
        }

        $query = $query->join('saved_list_members', function (JoinClause $joinClause) {
            $joinClause->on('saved_list_members.list_id', '=', 'saved_lists.id');
        });
        $query = $query->where('saved_list_members.user_id', $user->entityId());

        return $query->count();
    }

    public function getSavedListByUser(User $user): Collection
    {
        $query = $this->getModel()->newQuery();
        $query = $query->select('saved_lists.*')
            ->join('saved_list_members as lm', 'lm.list_id', '=', 'saved_lists.id')
            ->where('lm.user_id', $user->entityId());

        return $query->orderByDesc('saved_lists.id')
            ->get();
    }

    public function deleteSavedList(User $user, int $id): bool
    {
        $savedList = $this->find($id);
        policy_authorize(SavedListPolicy::class, 'delete', $user, $savedList);

        return (bool) $savedList->delete();
    }

    public function addFriendToSavedList(User $context, int $listId, array $friendIds = []): void
    {
        $userEntities = UserEntity::query()
            ->with(['detail'])
            ->whereIn('id', $friendIds)
            ->get();

        $savedList = SavedList::query()->getModel()
            ->find($listId);

        policy_authorize(SavedListPolicy::class, 'addFriend', $context, $savedList);

        foreach ($userEntities as $userEntity) {
            $friend = $userEntity->detail;

            if (null === $friend) {
                continue;
            }

            $isFriend = app('events')->dispatch('friend.is_friend', [$friend->id, $context->id], true);

            if (!$isFriend) {
                continue;
            }

            $this->memberRepository()->createMember($friend, $listId);
        }
    }

    public function filterSavedListByUser(User $context, Collection $savedLists)
    {
        foreach ($savedLists as $key => $savedList) {
            $memberIds = $savedList->savedListMembers->pluck('user_id')->toArray();
            if (!in_array($context->entityId(), $memberIds)) {
                $savedLists->forget($key);
            }
        }

        return $savedLists;
    }

    /**
     * @inheritDoc
     * @param User  $user
     * @param array $attributes
     * @return Collection
     * @throws AuthorizationException
     */
    public function viewItemCollection(User $user, array $attributes): Paginator
    {
        $type  = Arr::get($attributes, 'type', Browse::VIEW_ALL);
        $id    = Arr::get($attributes, 'id');
        $limit = !empty($attributes['limit']) ? $attributes['limit'] : Pagination::DEFAULT_ITEM_PER_PAGE;

        $savedList = $this->find($id);

        $relations = [
            'savedItems',
            'savedLists',
        ];

        $query = SavedListData::query()->where('list_id', $id);

        $query->whereHas('savedItems', function (Builder $q) use ($user, $type) {
            if ($type !== Browse::VIEW_ALL) {
                $q->where('saved_items.item_type', $type);
            }

            $blockScope = new BlockedScope();
            $blockScope->setContextId($user->entityId())
                ->setTable('saved_items')
                ->setPrimaryKey('user_id');

            $q->addScope($blockScope);
        });

        policy_authorize(SavedListPolicy::class, 'view', $user, $savedList);
        $query->leftJoin('saved_items as item', 'item.id', '=', 'saved_list_data.saved_id');

        $query->with($relations)
            ->has('savedLists')
            ->orderBy('item.created_at', 'desc');

        return $query->simplePaginate($limit);
    }
}
