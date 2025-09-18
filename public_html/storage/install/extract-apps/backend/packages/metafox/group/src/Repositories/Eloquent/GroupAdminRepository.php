<?php

namespace MetaFox\Group\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Group\Repositories\GroupAdminRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\Browse\Scopes\Group\SortScope;
use MetaFox\Group\Support\Browse\Scopes\Group\ViewAdminScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as SortScopeSupport;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\Profile\Support\CustomField;
use MetaFox\User\Support\Browse\Scopes\User\CustomFieldScope;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;

/**
 * Class GroupRepository.
 * @method Group getModel()
 * @method Group find($id, $columns = ['*'])()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @inore
 */
class GroupAdminRepository extends AbstractRepository implements GroupAdminRepositoryInterface
{
    use HasSponsor;
    use HasApprove;
    use CollectTotalItemStatTrait;
    use HasSponsorInFeed;

    public function model(): string
    {
        return Group::class;
    }

    /**
     * @return CategoryRepositoryInterface
     */
    private function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    /**
     * @return GroupRepositoryInterface
     */
    private function groupRepository(): GroupRepositoryInterface
    {
        return resolve(GroupRepositoryInterface::class);
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function viewGroups(User $context, array $attributes): EloquentBuilder
    {
        $sortType = Arr::get($attributes, 'sort_type', SortScopeSupport::SORT_TYPE_DEFAULT);
        $sort     = Arr::get($attributes, 'sort', SortScopeSupport::SORT_DEFAULT);

        $query = $this->buildQueryViewGroups($context, $attributes);

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        return $query->with(['userEntity'])
            ->addScope($sortScope);
    }

    private function buildQueryViewGroups(User $context, array $attributes): EloquentBuilder
    {
        $privacyType  = Arr::get($attributes, 'privacy_type');
        $view         = Arr::get($attributes, 'view');
        $search       = Arr::get($attributes, 'q');
        $categoryId   = Arr::get($attributes, 'category_id', 0);
        $customFields = Arr::get($attributes, 'custom_fields');
        $searchUser   = Arr::get($attributes, 'user_name');
        $createdFrom  = Arr::get($attributes, 'created_from');
        $createdTo    = Arr::get($attributes, 'created_to');
        $table        = $this->getModel()->getTable();

        $viewScope = new ViewAdminScope();
        $viewScope->setUserContext($context)->setView($view);
        $query = $this->getModel()->newQuery();

        $searchScope = new UserSearchScope();
        $searchScope->setTable($table);
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

        if ($privacyType != null) {
            $query->where('groups.privacy_type', '=', $privacyType);
        }

        if ($customFields) {
            $customFieldScope = new CustomFieldScope();
            $customFieldScope->setCustomFields($customFields);
            $customFieldScope->setCurrentTable($this->getModel()->getTable());
            $customFieldScope->setSectionType(CustomField::SECTION_TYPE_GROUP);

            $query = $query->addScope($customFieldScope);
        }

        return $query->addScope($viewScope);
    }

    /**
     * @param User $context
     * @param int  $id
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteGroup(User $context, int $id): bool
    {
        return $this->groupRepository()->deleteGroup($context, $id);
    }

}
