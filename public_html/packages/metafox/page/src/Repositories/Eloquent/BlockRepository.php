<?php

namespace MetaFox\Page\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\Page\Models\Block;
use MetaFox\Page\Policies\PageMemberPolicy;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\BlockRepositoryInterface;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Page\Support\Browse\Scopes\PageMember\ViewScope;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;

class BlockRepository extends AbstractRepository implements BlockRepositoryInterface
{
    public function model()
    {
        return Block::class;
    }

    /**
     * @param User  $context
     * @param int   $pageId
     * @param array $attributes
     * @return bool
     * @throws AuthorizationException
     */
    public function addPageBlock(User $context, int $pageId, array $attributes): bool
    {
        $userId = (int) Arr::get($attributes, 'user_id', 0);

        if ($userId <= 0) {
            return false;
        }

        /** @var User $user */
        $user = $this->userRepository()->find($userId);

        if (!$this->memberRepository()->isPageMember($pageId, $user->entityId())) {
            return false;
        }

        $member = $this->memberRepository()
            ->getModel()
            ->newQuery()
            ->where('page_id', $pageId)
            ->where('user_id', $userId)->first();

        policy_authorize(PageMemberPolicy::class, 'blockFromPage', $context, $member);

        $page = $this->pageRepository()->find($pageId);

        /* @var Block $block */
        Block::query()->create([
            'page_id'    => $pageId,
            'user_id'    => $user->entityId(),
            'user_type'  => $user->entityType(),
            'owner_id'   => $context->entityId(),
            'owner_type' => $context->entityType(),
        ]);

        $this->memberRepository()->deletePageMember($context, $pageId, $userId, $attributes);
        app('events')->dispatch('user.user_blocked', [$page, $user]);

        return true;
    }

    /**
     * @return UserRepositoryInterface
     */
    private function userRepository(): UserRepositoryInterface
    {
        return resolve(UserRepositoryInterface::class);
    }

    /**
     * @return PageRepositoryInterface
     */
    private function pageRepository(): PageRepositoryInterface
    {
        return resolve(PageRepositoryInterface::class);
    }

    /**
     * @return PageMemberRepositoryInterface
     */
    private function memberRepository(): PageMemberRepositoryInterface
    {
        return resolve(PageMemberRepositoryInterface::class);
    }

    /**
     * @param User  $context
     * @param int   $pageId
     * @param array $attributes
     * @return bool
     * @throws AuthorizationException
     */
    public function deletePageBlock(User $context, int $pageId, array $attributes): bool
    {
        $userId = $attributes['user_id'];
        /** @var User $user */
        $user = $this->userRepository()->find($userId);
        $page = $this->pageRepository()->find($pageId);
        policy_authorize(PagePolicy::class, 'unblockFromPage', $context, $page);

        app('events')->dispatch('user.user_unblocked', [$page, $user]);

        return $this->getModel()->newQuery()
            ->where('page_id', $pageId)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * @param User  $context
     * @param array $attributes
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewPageBlocks(User $context, array $attributes): Paginator
    {
        $pageId = Arr::get($attributes, 'page_id');
        $limit  = Arr::get($attributes, 'limit');
        $view   = Arr::get($attributes, 'view', Browse::VIEW_ALL);
        $search = Arr::get($attributes, 'q', '');

        $page = $this->pageRepository()->find($pageId);

        policy_authorize(PagePolicy::class, 'viewBlockedMember', $context, $page);

        $query     = $this->getModel()->newQuery()->where('page_id', '=', $pageId);
        $viewScope = new ViewScope();
        $viewScope->setView($view)->setPageId($page->entityId())->setUserContext($context);

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['full_name', 'user_name'], 'users'));
        }

        return $query->addScope($viewScope)->with(['userEntity', 'ownerEntity'])->simplePaginate($limit);
    }

    /**
     * @inheritDoc
     */
    public function isBlocked(int $pageId, int $userId): bool
    {
        return (bool) LoadReduce::get(
            sprintf('user::blocked(user:%s,owner:%s)', $userId, $pageId),
            fn () => $this->getModel()->newQuery()
                ->where('page_id', $pageId)
                ->where('user_id', $userId)
                ->exists(),
        );
    }
}
