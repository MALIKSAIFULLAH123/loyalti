<?php

namespace MetaFox\Forum\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Repositories\ForumPostAdminRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Forum\Support\Browse\Scopes\ForumScope;
use MetaFox\Forum\Support\Browse\Scopes\PostViewAdminScope;
use MetaFox\Forum\Support\Facades\ForumPost as ForumPostFacade;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;
use MetaFox\User\Traits\UserMorphTrait;

class ForumPostAdminRepository extends AbstractRepository implements ForumPostAdminRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasApprove;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;

    public function model()
    {
        return ForumPost::class;
    }

    /**
     * @param User  $context
     * @param array $attributes
     * @return Builder
     * @throws AuthorizationException
     */
    public function viewPosts(User $context, array $attributes): Builder
    {
        $sort        = Arr::get($attributes, 'sort');
        $sortType    = Arr::get($attributes, 'sort_type');
        $view        = Arr::get($attributes, 'view');
        $search      = Arr::get($attributes, 'q');
        $threadName  = Arr::get($attributes, 'thread_name');
        $forumId     = Arr::get($attributes, 'forum_id');
        $searchUser  = Arr::get($attributes, 'user_name');
        $searchOwner = Arr::get($attributes, 'owner_name');
        $createdFrom = Arr::get($attributes, 'created_from');
        $createdTo   = Arr::get($attributes, 'created_to');
        $table       = $this->getModel()->getTable();

        $searchScope = new UserSearchScope();
        $searchScope->setTable($table);

        if ($sort == Browse::SORT_MOST_DISCUSSED) {
            $sort = '';
        }

        $sortScope = new SortScope();
        $sortScope->setSort($sort)
            ->setSortType($sortType);

        $viewScope = new PostViewAdminScope();
        $viewScope->setUser($context)
            ->setView($view);

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query = $query->join('forum_post_text', 'forum_post_text.id', '=', 'forum_posts.id')
                ->addScope(new SearchScope($search, ['text_parsed'], 'forum_post_text'));
        }

        if ($forumId > 0) {
            $forumScope = new ForumScope($forumId, ForumPost::ENTITY_TYPE);
            $query      = $query->addScope($forumScope);
        }

        if ($threadName) {
            $query = $query->join('forum_threads', function (JoinClause $joinClause) use ($threadName) {
                $joinClause->on('forum_threads.id', '=', 'forum_posts.thread_id');
                $joinClause->where('forum_threads.title', $this->likeOperator(), '%' . $threadName . '%');
            });
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

        $relations = ForumPostFacade::getRelations();

        return $query->with($relations)
            ->addScope($sortScope)
            ->addScope($viewScope);
    }

    public function deletePost(User $context, int $id): bool
    {
        $post = $this
            ->with(['thread', 'thread.firstPost', 'thread.lastPost'])
            ->find($id);
        $post->delete();

        if (!$post->thread instanceof ForumThread) {
            return true;
        }

        resolve(ForumThreadRepositoryInterface::class)->updatePostId($post->thread);
        return true;
    }
}
