<?php

namespace MetaFox\Photo\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Repositories\AlbumAdminRepositoryInterface;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Support\Browse\Scopes\Album\SortScope;
use MetaFox\Photo\Support\Browse\Scopes\Album\ViewAdminScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class AlbumRepository.
 *
 * @property Album $model
 * @method   Album getModel()
 * @method   Album find($id, $columns = ['*'])
 * @mixin UserMorphTrait;
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AlbumAdminRepository extends AbstractRepository implements AlbumAdminRepositoryInterface
{
    use HasSponsor;
    use CollectTotalItemStatTrait;

    public function model(): string
    {
        return Album::class;
    }

    protected function albumRepository(): AlbumRepositoryInterface
    {
        return resolve(AlbumRepositoryInterface::class);
    }

    public function viewAlbums(User $context, array $attributes = []): Builder
    {
        $this->withUserMorphTypeActiveScope();
        $sort        = Arr::get($attributes, 'sort', SortScope::SORT_DEFAULT);
        $sortType    = Arr::get($attributes, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
        $view        = Arr::get($attributes, 'view', ViewAdminScope::VIEW_DEFAULT);
        $search      = Arr::get($attributes, 'q');
        $searchUser  = Arr::get($attributes, 'user_name');
        $searchOwner = Arr::get($attributes, 'owner_name');
        $createdFrom = Arr::get($attributes, 'created_from');
        $createdTo   = Arr::get($attributes, 'created_to');
        $table       = $this->getModel()->getTable();

        // Scopes.
        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $viewScope = new ViewAdminScope();
        $viewScope->setUserContext($context)->setView($view);

        $query = $this->getModel()->newQuery();

        if ($search != null) {
            $query->where('photo_albums.album_type', Album::NORMAL_ALBUM);
            $query = $query->addScope(new SearchScope($search, ['name']));
        }

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

        if ($createdFrom) {
            $query->where("$table.created_at", '>=', $createdFrom);
        }

        if ($createdTo) {
            $query->where("$table.created_at", '<=', $createdTo);
        }

        return $query
            ->with(['albumText', 'coverPhoto', 'userEntity', 'ownerEntity'])
            ->addScope($sortScope)
            ->addScope($viewScope);
    }

    /**
     * @inheritDoc
     */
    public function deleteAlbum(User $context, int $id): bool
    {
        return $this->albumRepository()->deleteAlbum($context, $id);
    }
}
