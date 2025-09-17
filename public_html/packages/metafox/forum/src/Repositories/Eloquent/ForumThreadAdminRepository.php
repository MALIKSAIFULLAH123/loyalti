<?php

namespace MetaFox\Forum\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Repositories\ForumThreadAdminRepositoryInterface;
use MetaFox\Forum\Support\Browse\Scopes\ForumScope;
use MetaFox\Forum\Support\Browse\Scopes\ThreadSortScope;
use MetaFox\Forum\Support\Browse\Scopes\ThreadViewAdminScope;
use MetaFox\Forum\Support\Facades\ForumThread as ForumThreadFacade;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;

class ForumThreadAdminRepository extends AbstractRepository implements ForumThreadAdminRepositoryInterface
{
    use HasApprove;
    use HasSponsor;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;

    public function model()
    {
        return ForumThread::class;
    }

    /**
     * @param User  $context
     * @param User  $owner
     * @param array $attributes
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewThreads(User $context, array $attributes = []): Builder
    {
        $this->withUserMorphTypeActiveScope();

        $sort        = Arr::get($attributes, 'sort');
        $sortType    = Arr::get($attributes, 'sort_type');
        $view        = Arr::get($attributes, 'view');
        $search      = Arr::get($attributes, 'q');
        $forumId     = Arr::get($attributes, 'forum_id');
        $searchUser  = Arr::get($attributes, 'user_name');
        $searchOwner = Arr::get($attributes, 'owner_name');
        $createdFrom = Arr::get($attributes, 'created_from');
        $createdTo   = Arr::get($attributes, 'created_to');
        $table       = $this->getModel()->getTable();

        $searchScope = new UserSearchScope();
        $searchScope->setTable($table);

        $sortScope = new ThreadSortScope();
        $sortScope->setView($view)
            ->setSortType($sortType)
            ->setSort($sort);

        $viewScope = new ThreadViewAdminScope();

        $viewScope->setView($view);

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query->leftJoin('forum_thread_text', function (JoinClause $joinClause) {
                $joinClause->on('forum_thread_text.id', '=', 'forum_threads.id');
            });

            $query = $query->addScope(new SearchScope(
                $search,
                ['forum_threads.title', 'forum_thread_text.text_parsed']
            ));
        }

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

        if ($forumId > 0) {
            $forumScope = new ForumScope($forumId, ForumThread::ENTITY_TYPE);

            $query->addScope($forumScope);
        }

        $relations = ForumThreadFacade::getRelations();

        return $query->with($relations)
            ->addScope($sortScope)
            ->addScope($viewScope);
    }
}
