<?php

namespace MetaFox\Music\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Music\Models\Playlist as Model;
use MetaFox\Music\Repositories\PlaylistAdminRepositoryInterface;
use MetaFox\Music\Support\Browse\Scopes\Playlist\SortScope;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class PlaylistAdminRepository.
 * @method Model find($id, $columns = ['*'])
 * @ignore
 * @codeCoverageIgnore
 */
class PlaylistAdminRepository extends AbstractRepository implements PlaylistAdminRepositoryInterface
{
    use UserMorphTrait;
    use CollectTotalItemStatTrait;

    public function model()
    {
        return Model::class;
    }

    public function viewPlaylists(ContractUser $context, array $attributes): Builder
    {
        $this->withUserMorphTypeActiveScope();

        $sort        = Arr::get($attributes, 'sort', Browse::SORT_RECENT);
        $sortType    = Arr::get($attributes, 'sort_type', Browse::SORT_TYPE_DESC);
        $search      = Arr::get($attributes, 'q');
        $searchUser  = Arr::get($attributes, 'user_name');
        $createdFrom = Arr::get($attributes, 'created_from');
        $createdTo   = Arr::get($attributes, 'created_to');
        $table       = $this->getModel()->getTable();

        /**
         * @var SortScope $sortScope
         */
        $sortScope = resolve(SortScope::class)
            ->setSort($sort)->setSortType($sortType);

        $query = $this->getModel()->newQuery();

        $searchScope = new UserSearchScope();
        $searchScope->setTable($table);

        if ($searchUser) {
            $searchScope->setAliasJoinedTable('user');
            $searchScope->setSearchText($searchUser);
            $searchScope->setFieldJoined('user_id');
            $query->addScope($searchScope);
        }

        if ($search != '') {
            $query->addScope(resolve(SearchScope::class, ['query' => $search, 'fields' => ['name']]));
        }

        if ($createdFrom) {
            $query->where("$table.created_at", '>=', $createdFrom);
        }

        if ($createdTo) {
            $query->where("$table.created_at", '<=', $createdTo);
        }

        return $query
            ->with(['userEntity', 'ownerEntity', 'isFavorite'])
            ->addScope($sortScope);
    }
}
