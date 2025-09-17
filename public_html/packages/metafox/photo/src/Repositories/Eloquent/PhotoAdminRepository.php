<?php

namespace MetaFox\Photo\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Photo\Models\Photo;
use MetaFox\Photo\Repositories\CategoryRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoAdminRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoRepositoryInterface;
use MetaFox\Photo\Support\Browse\Scopes\Photo\SortScope;
use MetaFox\Photo\Support\Browse\Scopes\Photo\ViewAdminScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\Platform\Traits\Eloquent\Model\HasFilterTagUserTrait;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;

/**
 * Class PhotoRepository.
 * @method   Photo getModel()
 * @method   Photo find($id, $columns = ['*'])
 * @method   Photo newModelInstance()
 * @property Photo $model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PhotoAdminRepository extends AbstractRepository implements PhotoAdminRepositoryInterface
{
    use HasFeatured;
    use HasSponsor;
    use HasApprove;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;
    use HasFilterTagUserTrait;

    public function model(): string
    {
        return Photo::class;
    }

    /**
     * @return CategoryRepositoryInterface
     */
    private function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    /**
     * @return PhotoRepositoryInterface
     */
    private function photoRepository(): PhotoRepositoryInterface
    {
        return resolve(PhotoRepositoryInterface::class);
    }

    /**
     * @param User  $context
     * @param array $attributes
     * @return Builder
     */
    public function viewPhotos(User $context, array $attributes = []): Builder
    {
        $this->withUserMorphTypeActiveScope();

        $sort        = Arr::get($attributes, 'sort', SortScope::SORT_DEFAULT);
        $sortType    = Arr::get($attributes, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
        $view        = Arr::get($attributes, 'view', ViewAdminScope::VIEW_DEFAULT);
        $search      = Arr::get($attributes, 'q');
        $categoryId  = Arr::get($attributes, 'category_id');
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

        if ($categoryId != null) {
            $categoryScope = new CategoryScope();

            if (!is_array($categoryId)) {
                $categoryId = $this->categoryRepository()->getChildrenIds($categoryId);
            }

            $categoryScope->setCategories($categoryId);
            $query = $query->addScope($categoryScope);
        }

        if (null != $search) {
            $query = $query->addScope(new SearchScope($search, ['content']));
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

        $relations = ['photoInfo', 'group', 'album'];

        return $query->with($relations)
            ->addScope($sortScope)
            ->addScope($viewScope);
    }

    /**
     * @inheritDoc
     */
    public function deletePhoto(User $context, int $id): array
    {
        return $this->photoRepository()->deletePhoto($context, $id);
    }
}
