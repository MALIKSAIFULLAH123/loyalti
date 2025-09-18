<?php

namespace MetaFox\Page\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\PageAdminRepositoryInterface;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Page\Support\Browse\Scopes\Page\SortScope;
use MetaFox\Page\Support\Browse\Scopes\Page\ViewAdminScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as SortScopeSupport;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Profile\Support\CustomField;
use MetaFox\User\Support\Browse\Scopes\User\CustomFieldScope;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;

/**
 * Class PageRepository.
 * @method Page getModel()
 * @method Page find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PageAdminRepository extends AbstractRepository implements PageAdminRepositoryInterface
{
    use HasSponsor;
    use HasApprove;
    use CollectTotalItemStatTrait;

    public function model(): string
    {
        return Page::class;
    }

    protected function categoryRepository(): PageCategoryRepositoryInterface
    {
        return resolve(PageCategoryRepositoryInterface::class);
    }

    protected function pageRepository(): PageRepositoryInterface
    {
        return resolve(PageRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function viewPages(User $context, array $attributes): Builder
    {
        $sort         = Arr::get($attributes, 'sort', SortScopeSupport::SORT_DEFAULT);
        $sortType     = Arr::get($attributes, 'sort_type', SortScopeSupport::SORT_TYPE_DEFAULT);
        $view         = Arr::get($attributes, 'view', Browse::VIEW_ALL);
        $search       = Arr::get($attributes, 'q');
        $categoryId   = Arr::get($attributes, 'category_id');
        $customFields = Arr::get($attributes, 'custom_fields');
        $searchUser   = Arr::get($attributes, 'user_name');
        $createdFrom  = Arr::get($attributes, 'created_from');
        $createdTo    = Arr::get($attributes, 'created_to');
        $table        = $this->getModel()->getTable();

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $viewScope = new ViewAdminScope();
        $viewScope->setUserContext($context)->setView($view);

        $searchScope = new UserSearchScope();
        $searchScope->setTable($table);

        $query = $this->getModel()->newQuery()->with(['userEntity']);

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['name']));
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

        if ($categoryId > 0) {
            if (!is_array($categoryId)) {
                $categoryId = $this->categoryRepository()->getChildrenIds($categoryId);
            }

            $categoryScope = new CategoryScope();
            $categoryScope->setCategories($categoryId);
            $query->addScope($categoryScope);
        }

        if ($customFields) {
            $customFieldScope = new CustomFieldScope();
            $customFieldScope->setCustomFields($customFields);
            $customFieldScope->setCurrentTable($this->getModel()->getTable());
            $customFieldScope->setSectionType(CustomField::SECTION_TYPE_PAGE);

            $query = $query->addScope($customFieldScope);
        }

        return $query->addScope($sortScope)
            ->addScope($viewScope);
    }

    public function getPagesByPageIds(array $pageIds): Collection
    {
        return $this->getModel()
            ->newQuery()
            ->whereIn('id', $pageIds)
            ->get();
    }

    /**
     * @param  User                   $context
     * @param  int                    $id
     * @return bool
     * @throws AuthorizationException
     */
    public function deletePage(User $context, int $id): bool
    {
        return $this->pageRepository()->deletePage($context, $id);
    }
}
