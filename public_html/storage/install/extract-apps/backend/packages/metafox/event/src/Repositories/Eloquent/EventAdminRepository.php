<?php

namespace MetaFox\Event\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Repositories\CategoryRepositoryInterface;
use MetaFox\Event\Repositories\EventAdminRepositoryInterface;
use MetaFox\Event\Support\Browse\Scopes\Event\SortScope;
use MetaFox\Event\Support\Browse\Scopes\Event\ViewAdminScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;

/**
 * Class EventRepository.
 * @method Event getModel()
 * @method Event find($id, $columns = ['*'])()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EventAdminRepository extends AbstractRepository implements EventAdminRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasApprove;
    use CollectTotalItemStatTrait;
    use HasSponsorInFeed;

    public function model()
    {
        return Event::class;
    }

    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    /**
     * @throws AuthorizationException
     */
    public function viewEvents(User $context, array $attributes): Builder
    {
        $this->withUserMorphTypeActiveScope();

        $relations = ['eventText', 'user', 'owner', 'userEntity', 'attachments', 'categories'];

        $query = $this->buildQueryViewEvents($context, $attributes);

        return $query
            ->with($relations);
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
    private function buildQueryViewEvents(User $context, array $attributes): Builder
    {
        $view        = Arr::get($attributes, 'view', Browse::VIEW_ALL);
        $sort        = Arr::get($attributes, 'sort', SortScope::SORT_DEFAULT);
        $sortType    = Arr::get($attributes, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
        $search      = Arr::get($attributes, 'q');
        $categoryId  = Arr::get($attributes, 'category_id');
        $searchUser  = Arr::get($attributes, 'user_name');
        $searchOwner = Arr::get($attributes, 'owner_name');
        $startTime   = Arr::get($attributes, 'start_time');
        $endTime     = Arr::get($attributes, 'end_time');
        $table       = $this->getModel()->getTable();
        $query       = $this->getModel()->newQuery()->select('events.*');

        $sortScope = new SortScope();
        $viewScope = new ViewAdminScope();

        $sortScope->setSort($sort)->setSortType($sortType);
        $viewScope->setView($view);

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['name']));
        }

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
        if ($startTime) {
            $query->where("$table.start_time", '>=', $startTime);
        }
        if ($endTime) {
            $query->where("$table.end_time", '<=', $endTime);
        }

        return $query
            ->addScope($sortScope)
            ->addScope($viewScope);
    }
}
