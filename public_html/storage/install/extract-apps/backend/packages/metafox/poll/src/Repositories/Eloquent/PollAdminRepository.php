<?php

namespace MetaFox\Poll\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\Platform\Traits\Helpers\InputCleanerTrait;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\PollAdminRepositoryInterface;
use MetaFox\Poll\Support\Browse\Scopes\Poll\SortScope;
use MetaFox\Poll\Support\Browse\Scopes\Poll\ViewAdminScope;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class PollAdminRepository.
 * @property Poll $model
 * @method   Poll getModel()
 * @method   Poll find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PollAdminRepository extends AbstractRepository implements PollAdminRepositoryInterface
{
    use HasApprove;
    use HasFeatured;
    use HasSponsor;
    use HasSponsorInFeed;
    use InputCleanerTrait;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;

    public function model(): string
    {
        return Poll::class;
    }

    public function viewPolls(User $context, array $attributes): Builder
    {
        $this->withUserMorphTypeActiveScope();

        $query = $this->buildQueryViewPolls($context, $attributes);

        $relations = [
            'pollText',
            'userEntity',
            'user',
        ];

        return $query->with($relations);
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    private function buildQueryViewPolls(User $context, array $attributes): Builder
    {
        $sort        = $attributes['sort'];
        $sortType    = $attributes['sort_type'];
        $view        = $attributes['view'];
        $search      = $attributes['q'];
        $searchUser  = Arr::get($attributes, 'user_name');
        $searchOwner = Arr::get($attributes, 'owner_name');
        $createdFrom = Arr::get($attributes, 'created_from');
        $createdTo   = Arr::get($attributes, 'created_to');
        $table       = $this->getModel()->getTable();

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $viewScope = new ViewAdminScope();
        $viewScope->setUserContext($context)->setView($view);

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
            $query = $query->addScope(new SearchScope($search, ['question']));
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
