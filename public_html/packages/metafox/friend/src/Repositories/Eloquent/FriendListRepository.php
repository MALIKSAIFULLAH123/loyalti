<?php

namespace MetaFox\Friend\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Friend\Models\FriendList;
use MetaFox\Friend\Models\FriendListData;
use MetaFox\Friend\Policies\FriendListPolicy;
use MetaFox\Friend\Repositories\FriendListRepositoryInterface;
use MetaFox\Friend\Repositories\FriendRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class FriendListRepository.
 *
 * @property FriendList $model
 * @method   FriendList find($id, $columns = ['*'])
 * @method   FriendList getModel()
 * @ignore
 * @codeCoverageIgnore
 */
class FriendListRepository extends AbstractRepository implements FriendListRepositoryInterface
{
    use UserMorphTrait;

    public function model(): string
    {
        return FriendList::class;
    }

    public function getFriendListByUser(int $userId): Collection
    {
        return $this->model->with('users')->where(['user_id' => $userId])->get();
    }

    public function getFriendListsByIds(array $friendListIds): Collection
    {
        return $this->model->with('users')->whereIn('id', $friendListIds)->get();
    }

    public function getAssignedListIds(int $userId, int $friendUserId): array
    {
        return $this->model->query()
            ->select('list.id')
            ->from('friend_lists', 'list')
            ->where('list.user_id', '=', $userId)
            ->join('friend_list_data as data', function (JoinClause $join) {
                $join->on('data.list_id', '=', 'list.id');
            })
            ->where('data.user_id', '=', $friendUserId)
            ->get()->pluck('id')->toArray();
    }

    public function viewFriendLists(User $context, array $attributes): Builder
    {
        $sort     = $attributes['sort'] ?? SortScope::SORT_DEFAULT;
        $sortType = $attributes['sort_type'] ?? SortScope::SORT_TYPE_DEFAULT;

        $query = $this->getModel()->newQuery()
            ->with(['userEntities'])
            ->where('user_id', $context->entityId())
            ->where('user_type', $context->entityType());

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        return $query->addScope($sortScope);
    }

    public function viewFriendList(User $context, int $id): FriendList
    {
        $friendList = $this->with(['userEntities'])->find($id);

        policy_authorize(FriendListPolicy::class, 'view', $context, $friendList);

        return $friendList;
    }

    public function createFriendList(User $context, string $name): FriendList
    {
        policy_authorize(FriendListPolicy::class, 'create', $context);

        /** @var FriendList $friendList */
        $friendList = parent::create([
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'name'      => $name,
        ]);

        $friendList->refresh();

        return $friendList;
    }

    public function updateFriendList(User $context, int $id, string $name): FriendList
    {
        $friendList = $this->find($id);

        policy_authorize(FriendListPolicy::class, 'update', $context, $friendList);

        $friendList->update(['name' => $name]);
        $friendList->refresh();

        return $friendList;
    }

    public function deleteFriendList(User $context, int $id): bool
    {
        $friendList = $this->find($id);

        policy_authorize(FriendListPolicy::class, 'delete', $context, $friendList);
        $oldUsers = $friendList->users->pluck('id')->toArray();

        if (!empty($oldUsers)) {
            $friendList->users()->detach($oldUsers);
        }

        return (bool) $friendList->delete();
    }

    public function addFriendsToFriendLists(array $friendLists, $friendIds = []): void
    {
        /** @var FriendList $friendList */
        foreach ($friendLists as $friendList) {
            if (empty($friendIds)) {
                $friendList->users()->sync([]);

                continue;
            }

            $userId    = $friendList->user_id;
            $data      = [];
            $detaching = false;
            $oldUsers  = $friendList->users->pluck('id')->toArray();

            foreach ($friendIds as $k => $friendId) {
                if (!$this->getFriendRepository()->isFriend($userId, $friendId)) {
                    unset($friendIds[$k]);
                    continue;
                }

                $detaching = in_array($friendId, $oldUsers) ? true : $detaching;

                $data[$friendId] = ['user_type' => 'user'];
            }

            $friendList->users()->sync($data, $detaching);
        }
    }

    public function removeFriendsFromFriendLists(array $friendLists, array $friendIds): array
    {
        /** @var FriendList $friendList */
        foreach ($friendLists as $friendList) {
            $friendList->users()->detach($friendIds);
        }

        return $friendIds;
    }

    /**
     * @return FriendRepositoryInterface
     */
    private function getFriendRepository(): FriendRepositoryInterface
    {
        return resolve(FriendRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function updateToFriendList(int $id, array $userId): bool
    {
        $friendList = $this->find($id);

        policy_authorize(FriendListPolicy::class, 'actionOnFriendList', user(), $friendList);

        return $friendList->users()->updateExistingPivot($id, ['']);
    }

    /**
     * @inheritDoc
     */
    public function deleteUserForListData(User $user): void
    {
        $model = new FriendListData();

        $model->newQuery()->where([
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
        ])->delete();
    }
}
