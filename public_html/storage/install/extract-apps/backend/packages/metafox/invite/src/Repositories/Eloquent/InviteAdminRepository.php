<?php

namespace MetaFox\Invite\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Invite\Models\Invite;
use MetaFox\Invite\Repositories\InviteAdminRepositoryInterface;
use MetaFox\Invite\Repositories\InviteRepositoryInterface;
use MetaFox\Invite\Support\Browse\Scopes\Invite\SortScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Sms\Support\Traits\PhoneRegexTrait;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class InviteRepository.
 * @method Invite find($id, $columns = ['*'])
 */
class InviteAdminRepository extends AbstractRepository implements InviteAdminRepositoryInterface
{
    use UserMorphTrait;
    use PhoneRegexTrait;

    public function model()
    {
        return Invite::class;
    }

    protected function inviteRepository(): InviteRepositoryInterface
    {
        return resolve(InviteRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function viewInvites(User $user, array $params): Paginator
    {
        $limit       = Arr::get($params, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $searchOwner = Arr::get($params, 'owner_name');
        $searchUser  = Arr::get($params, 'user_name');
        $sort        = Arr::get($params, 'sort', Browse::SORT_RECENT);
        $sortType    = Arr::get($params, 'sort_type', Browse::SORT_TYPE_DESC);
        $table       = $this->getModel()->getTable();
        $query       = $this->getModel()->newQuery()->select("$table.*");

        Arr::set($params, 'status', Arr::get($params, 'status', Invite::STATUS_COMPLETED));

        $query       = $this->inviteRepository()->buildQueryViewInvites($user, $query, $params);
        $searchScope = new SearchScope();
        $searchScope->setTable($table);

        if ($searchOwner) {
            $searchScope->setAliasJoinedTable('owner');
            $searchScope->setSearchText($searchOwner);
            $searchScope->setFieldJoined('owner_id');
            $query->addScope($searchScope);
        }

        if ($searchUser) {
            $searchScope->setAliasJoinedTable('user');
            $searchScope->setSearchText($searchUser);
            $searchScope->setFieldJoined('user_id');
            $query->addScope($searchScope);
        }

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        return $query->addScope($sortScope)->orderByDesc('id')->paginate($limit);
    }

    /**
     * @inheritDoc
     */
    public function deleteInvite(User $user, int $id): bool
    {
        $invite = $this->find($id);

        return $invite->delete();
    }
}
