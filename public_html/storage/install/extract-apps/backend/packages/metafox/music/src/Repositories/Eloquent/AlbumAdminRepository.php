<?php

namespace MetaFox\Music\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Album as Model;
use MetaFox\Music\Repositories\AlbumAdminRepositoryInterface;
use MetaFox\Music\Support\Browse\Scopes\Album\SortScope;
use MetaFox\Music\Support\Browse\Scopes\Album\ViewAdminScope;
use MetaFox\Music\Support\Browse\Scopes\Genre\GenreScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;

/**
 * Class AlbumAdminRepository.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class AlbumAdminRepository extends AbstractRepository implements AlbumAdminRepositoryInterface
{
    use HasSponsor;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;

    public function model()
    {
        return Model::class;
    }

    public function viewAlbums(User $context, array $attributes): Builder
    {
        $this->withUserMorphTypeActiveScope();
        $sort        = Arr::get($attributes, 'sort', Browse::SORT_RECENT);
        $sortType    = Arr::get($attributes, 'sort_type', Browse::SORT_TYPE_DESC);
        $when        = Arr::get($attributes, 'when', Browse::WHEN_ALL);
        $view        = Arr::get($attributes, 'view', Browse::VIEW_ALL);
        $search      = Arr::get($attributes, 'q');
        $genreId     = Arr::get($attributes, 'genre_id');
        $searchUser  = Arr::get($attributes, 'user_name');
        $searchOwner = Arr::get($attributes, 'owner_name');
        $createdFrom = Arr::get($attributes, 'created_from');
        $createdTo   = Arr::get($attributes, 'created_to');
        $table       = $this->getModel()->getTable();

        /**
         * @var SortScope $sortScope
         */
        $sortScope = resolve(SortScope::class)
            ->setSort($sort)
            ->setSortType($sortType);

        /**
         * @var WhenScope $whenScope
         */
        $whenScope = resolve(WhenScope::class)
            ->setWhen($when);

        /**
         * @var ViewAdminScope $viewScope
         */
        $viewScope = resolve(ViewAdminScope::class)
            ->setUserContext($context)
            ->setView($view);

        $query = $this->getModel()->newQuery();

        $searchScope = new UserSearchScope();
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

        if ($search != '') {
            $query->addScope(resolve(SearchScope::class, ['query' => $search, 'fields' => ['name']]));
        }

        if ($createdFrom) {
            $query->where("$table.created_at", '>=', $createdFrom);
        }

        if ($createdTo) {
            $query->where("$table.created_at", '<=', $createdTo);
        }

        if ($genreId) {
            $query->addScope(resolve(GenreScope::class, [
                'itemType' => Album::ENTITY_TYPE,
                'genreId'  => $genreId,
            ]));
        }

        return $query
            ->with(['userEntity', 'ownerEntity', 'albumText', 'attachments'])
            ->addScope($sortScope)
            ->addScope($whenScope)
            ->addScope($viewScope);
    }
}
