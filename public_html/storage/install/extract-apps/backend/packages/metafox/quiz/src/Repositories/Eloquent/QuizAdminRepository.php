<?php

namespace MetaFox\Quiz\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\FeaturedScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Repositories\QuizAdminRepositoryInterface;
use MetaFox\Quiz\Support\Browse\Scopes\Quiz\SortScope;
use MetaFox\Quiz\Support\Browse\Scopes\Quiz\ViewAdminScope;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;

/**
 * Class QuizAdminRepository.
 * @property Quiz $model
 * @method   Quiz getModel()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuizAdminRepository extends AbstractRepository implements QuizAdminRepositoryInterface
{
    use HasSponsor;
    use HasApprove;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;

    /**
     * @return string
     */
    public function model(): string
    {
        return Quiz::class;
    }

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    public function viewQuizzes(User $context, array $attributes): Builder
    {
        $this->withUserMorphTypeActiveScope();

        $query     = $this->buildViewQuizzesQuery($context, $attributes);
        $relations = ['quizText', 'attachments'];

        return $query->with($relations);
    }

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    private function buildViewQuizzesQuery(User $context, array $attributes): Builder
    {
        $sort        = Arr::get($attributes, 'sort');
        $sortType    = Arr::get($attributes, 'sort_type');
        $view        = Arr::get($attributes, 'view');
        $search      = Arr::get($attributes, 'q');
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

        if ($createdFrom) {
            $query->where("$table.created_at", '>=', $createdFrom);
        }

        if ($createdTo) {
            $query->where("$table.created_at", '<=', $createdTo);
        }

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['title']));
        }

        if ($view == Browse::VIEW_FEATURE) {
            $query->addScope(new FeaturedScope(true));
        }

        return $query
            ->addScope($sortScope)
            ->addScope($viewScope);
    }
}
