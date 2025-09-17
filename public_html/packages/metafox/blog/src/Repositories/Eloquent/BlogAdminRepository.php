<?php

namespace MetaFox\Blog\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Blog\Models\Blog;
use MetaFox\Blog\Repositories\BlogAdminRepositoryInterface;
use MetaFox\Blog\Repositories\CategoryRepositoryInterface;
use MetaFox\Blog\Support\Browse\Scopes\Blog\ViewAdminScope;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class BlogRepository.
 * @property Blog $model
 * @method   Blog getModel()
 * @method   Blog find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @ignore
 * @codeCoverageIgnore
 */
class BlogAdminRepository extends AbstractRepository implements BlogAdminRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasApprove;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;

    public function model(): string
    {
        return Blog::class;
    }

    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    public function viewBlogs(User $context, array $attributes): Paginator
    {
        $limit = $attributes['limit'];

        $this->withUserMorphTypeActiveScope();

        $query     = $this->buildQueryViewBlogs($context, $attributes);
        $relations = $this->withRelations();

        return $query
            ->with($relations)
            ->paginate($limit, ['blogs.*']);
    }

    protected function withRelations(): array
    {
        return ['blogText', 'user', 'owner', 'userEntity', 'categories'];
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function buildQueryViewBlogs(User $context, array $attributes): Builder
    {
        $sort        = Arr::get($attributes, 'sort');
        $sortType    = Arr::get($attributes, 'sort_type');
        $view        = Arr::get($attributes, 'view');
        $search      = Arr::get($attributes, 'q');
        $categoryId  = Arr::get($attributes, 'category_id');
        $searchUser  = Arr::get($attributes, 'user_name');
        $searchOwner = Arr::get($attributes, 'owner_name');
        $createdFrom = Arr::get($attributes, 'created_from');
        $createdTo   = Arr::get($attributes, 'created_to');
        $table       = $this->getModel()->getTable();
        $sortScope   = new SortScope($sort, $sortType);

        $viewScope = new ViewAdminScope();
        $viewScope->setView($view);

        $query = $this->getModel()->newQuery();

        if ($categoryId > 0) {
            if (!is_array($categoryId)) {
                $categoryId = $this->categoryRepository()->getChildrenIds($categoryId);
            }

            $categoryScope = new CategoryScope();
            $categoryScope->setCategories($categoryId);

            $query->addScope($categoryScope);
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

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['title']));
        }

        if ($createdFrom) {
            $query->where("$table.created_at", '>=', $createdFrom);
        }

        if ($createdTo) {
            $query->where("$table.created_at", '<=', $createdTo);
        }

        return $query
            ->addScope($sortScope)
            ->addScope($viewScope);
    }
}
